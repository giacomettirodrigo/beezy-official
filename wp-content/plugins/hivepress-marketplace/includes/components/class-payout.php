<?php
/**
 * Payout component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Models;
use HivePress\Blocks;
use HivePress\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Payout component class.
 *
 * @class Payout
 */
final class Payout extends Component {

	/**
	 * Stripe API instance.
	 *
	 * @var object
	 */
	protected $stripe;

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Add attributes.
		add_filter( 'hivepress/v1/models/vendor/attributes', [ $this, 'add_vendor_attributes' ] );
		add_filter( 'hivepress/v1/models/vendor', [ $this, 'add_vendor_fields' ] );

		// Process payment.
		add_action( 'wc_gateway_stripe_process_response', [ $this, 'process_payment' ], 10, 2 );

		// Manage payouts.
		add_action( 'woocommerce_order_status_changed', [ $this, 'schedule_payout' ], 100, 4 );
		add_action( 'hivepress/v1/models/order/payout', [ $this, 'payout_order' ], 10, 4 );

		add_action( 'hivepress/v1/models/payout/create', [ $this, 'update_payout' ] );
		add_action( 'hivepress/v1/models/payout/update', [ $this, 'update_payout' ] );

		add_action( 'hivepress/v1/models/payout/update_status', [ $this, 'update_payout_status' ], 10, 3 );

		// Alter forms.
		add_filter( 'hivepress/v1/forms/payout_request', [ $this, 'alter_payout_request_form' ] );
		add_filter( 'hivepress/v1/forms/vendor_update', [ $this, 'alter_vendor_update_form' ], 10, 2 );
		add_filter( 'hivepress/v1/forms/user_update_profile', [ $this, 'alter_user_update_profile_form' ], 10, 2 );

		// Manage admin columns.
		add_filter( 'manage_hp_payout_posts_columns', [ $this, 'add_payout_admin_columns' ] );
		add_action( 'manage_hp_payout_posts_custom_column', [ $this, 'render_payout_admin_columns' ], 10, 2 );

		// Alter post types.
		add_filter( 'hivepress/v1/post_types', [ $this, 'alter_post_types' ] );

		// Alter meta boxes.
		add_filter( 'hivepress/v1/meta_boxes/payout_settings', [ $this, 'alter_payout_settings_metabox' ] );

		// Alter templates.
		add_filter( 'hivepress/v1/templates/vendor_dashboard_page/blocks', [ $this, 'alter_vendor_dashboard_page' ], 200, 2 );

		parent::__construct( $args );
	}

	/**
	 * Gets referer URL.
	 *
	 * @return string
	 * @todo remove when added to the core.
	 */
	protected function get_referer_url() {
		return wp_validate_redirect( hp\get_array_value( $_SERVER, 'HTTP_REFERER' ) );
	}

	/**
	 * Gets Stripe API instance.
	 *
	 * @return object
	 */
	public function stripe() {
		if ( is_null( $this->stripe ) && get_option( 'hp_stripe_secret_key' ) ) {
			$this->stripe = new \Stripe\StripeClient( get_option( 'hp_stripe_secret_key' ) );
		}

		return $this->stripe;
	}

	/**
	 * Adds vendor attributes.
	 *
	 * @param array $attributes Attributes.
	 * @return array
	 */
	public function add_vendor_attributes( $attributes ) {
		if ( get_option( 'hp_payout_system' ) === 'stripe' ) {
			$attributes['stripe_id'] = [
				'edit_field' => [
					/* translators: %s: payment service. */
					'label'      => sprintf( esc_html__( '%s ID', 'hivepress-marketplace' ), 'Stripe' ),
					'type'       => 'text',
					'max_length' => 256,
					'readonly'   => true,
					'required'   => true,
					'_order'     => 5,
				],
			];

			$attributes['stripe_setup'] = [
				'protected'  => true,

				'edit_field' => [
					'type'     => 'checkbox',
					'required' => true,
				],
			];
		}

		return $attributes;
	}

	/**
	 * Adds vendor fields.
	 *
	 * @todo remove when attributes are fixed.
	 * @param array $model Model arguments.
	 * @return array
	 */
	public function add_vendor_fields( $model ) {
		if ( get_option( 'hp_payout_system' ) === 'stripe' ) {
			$model['fields']['stripe_setup'] = [
				'type'      => 'checkbox',
				'required'  => true,
				'_external' => true,
			];

			$model['fields']['country'] = [
				'label'     => esc_html__( 'Country', 'hivepress-marketplace' ),
				'type'      => 'select',
				'options'   => 'countries',
				'_external' => true,
			];
		}

		return $model;
	}

	/**
	 * Processes payment.
	 *
	 * @param object $response API response.
	 * @param object $order WooCommerce order.
	 */
	public function process_payment( $response, $order ) {

		// Check settings.
		if ( get_option( 'hp_payout_system' ) !== 'stripe' ) {
			return;
		}

		// Check response.
		if ( ! $response->captured || ! $response->paid ) {
			return;
		}

		// Set charge ID.
		update_post_meta( $order->get_id(), 'hp_stripe_charge_id', $response->id );
	}

	/**
	 * Schedules payout.
	 *
	 * @param int      $order_id Order ID.
	 * @param string   $old_status Old status.
	 * @param string   $new_status New status.
	 * @param WC_Order $order Order object.
	 */
	public function schedule_payout( $order_id, $old_status, $new_status, $order ) {

		// Check settings.
		if ( get_option( 'hp_payout_system' ) !== 'stripe' ) {
			return;
		}

		// Check status.
		if ( 'completed' !== $new_status || $order->get_meta( 'hp_paid' ) ) {
			return;
		}

		// Get vendor.
		if ( ! $order->get_meta( 'hp_vendor' ) ) {
			return;
		}

		$vendor = Models\Vendor::query()->get_by_id( $order->get_meta( 'hp_vendor' ) );

		if ( ! $vendor || ! $vendor->is_stripe_setup() ) {
			return;
		}

		// Get profit.
		$profit = hivepress()->marketplace->get_order_profit( $order );

		if ( ! $profit ) {
			return;
		}

		// Schedule action.
		hivepress()->scheduler->add_action( 'hivepress/v1/models/order/payout', [ $order_id, $vendor->get_stripe_id(), $profit ] );
	}

	/**
	 * Payouts order.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $stripe_id Stripe ID.
	 * @param float  $amount Payout amount.
	 * @param int    $attempt Attempt number.
	 */
	public function payout_order( $order_id, $stripe_id, $amount, $attempt = 1 ) {

		// Check Stripe API.
		if ( ! $this->stripe() ) {
			return;
		}

		// Check status.
		if ( get_post_meta( $order_id, 'hp_paid', true ) ) {
			return;
		}

		// Check attempt.
		if ( $attempt > 5 ) {
			return;
		}

		try {

			// Set integer currencies.
			$integer_currencies = [ 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF' ];

			// Set transfer arguments.
			$transfer_args = [
				'amount'      => in_array( get_woocommerce_currency(), $integer_currencies ) ? $amount : round( $amount * 100 ),
				'currency'    => get_woocommerce_currency(),
				'destination' => $stripe_id,

				/* translators: %s: order number. */
				'description' => sprintf( esc_html__( 'Order %s', 'hivepress-marketplace' ), '#' . $order_id ),
			];

			// Get charge ID.
			$charge_id = get_post_meta( $order_id, 'hp_stripe_charge_id', true );

			if ( $charge_id ) {
				$transfer_args['source_transaction'] = $charge_id;
			}

			// Send Stripe transfer.
			$transfer = $this->stripe()->transfers->create(
				apply_filters(
					'hivepress/v1/components/stripe/create_transfer',
					$transfer_args
				)
			);

			// Set order ID.
			$this->stripe()->charges->update(
				$transfer->destination_payment,
				[
					/* translators: %s: order number. */
					'description' => sprintf( esc_html__( 'Order %s', 'hivepress-marketplace' ), '#' . $order_id ),
				],
				[
					'stripe_account' => $stripe_id,
				]
			);
		} catch ( \Exception $e ) {

			// Send email.
			( new Emails\Payout_Fail(
				[
					'recipient' => get_option( 'admin_email' ),

					'tokens'    => [
						'order_number' => '#' . $order_id,
						'fail_reason'  => $e->getMessage(),
						'order_url'    => admin_url(
							'post.php?' . http_build_query(
								[
									'action' => 'edit',
									'post'   => $order_id,
								]
							)
						),
					],
				]
			) )->send();

			// Schedule action.
			hivepress()->scheduler->add_action( 'hivepress/v1/models/order/payout', [ $order_id, $stripe_id, $amount, $attempt + 1 ], time() + DAY_IN_SECONDS );

			return;
		}

		// Save payout flag.
		update_post_meta( $order_id, 'hp_paid', 1 );

		// Delete charge ID.
		delete_post_meta( $order_id, 'hp_stripe_charge_id' );
	}

	/**
	 * Updates payout.
	 *
	 * @param int $payout_id Payout ID.
	 */
	public function update_payout( $payout_id ) {
		$updated = false;

		// Get payout.
		$payout = Models\Payout::query()->get_by_id( $payout_id );

		// Get vendor.
		$vendor = $payout->get_vendor();

		if ( $vendor ) {

			// Update balance.
			hivepress()->marketplace->update_vendor_balance( $vendor );

			// Set user.
			if ( $payout->get_user__id() !== $vendor->get_user__id() ) {
				$updated = true;

				$payout->set_user( $vendor->get_user__id() );
			}
		}

		// Set title.
		if ( ! $payout->get_title() ) {
			$updated = true;

			$payout->set_title( '#' . $payout->get_id() );
		}

		// Update payout.
		if ( $updated ) {
			$payout->save(
				[
					'title',
					'user',
				]
			);
		}
	}

	/**
	 * Updates payout status.
	 *
	 * @param int    $payout_id Payout ID.
	 * @param string $new_status New status.
	 * @param string $old_status Old status.
	 */
	public function update_payout_status( $payout_id, $new_status, $old_status ) {

		// Check status.
		if ( 'pending' !== $new_status && 'pending' !== $old_status ) {
			return;
		}

		// Get payout.
		$payout = Models\Payout::query()->get_by_id( $payout_id );

		if ( 'pending' === $new_status ) {

			// Send email.
			( new Emails\Payout_Request(
				[
					'recipient' => get_option( 'admin_email' ),

					'tokens'    => [
						'payout_amount' => $payout->display_amount(),
						'payout_method' => $payout->display_method(),
						'payout_url'    => admin_url(
							'post.php?' . http_build_query(
								[
									'action' => 'edit',
									'post'   => $payout->get_id(),
								]
							)
						),
					],
				]
			) )->send();
		} elseif ( 'publish' === $new_status ) {

			// Get user.
			$user = $payout->get_user();

			if ( $user ) {

				// Send email.
				( new Emails\Payout_Complete(
					[
						'recipient' => $user->get_email(),

						'tokens'    => [
							'user'          => $user,
							'payout'        => $payout,
							'user_name'     => $user->get_display_name(),
							'payout_amount' => $payout->display_amount(),
							'payout_method' => $payout->display_method(),
							'payouts_url'   => hivepress()->router->get_url( 'payouts_view_page' ),
						],
					]
				) )->send();
			}
		}
	}

	/**
	 * Alters payout request form.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function alter_payout_request_form( $form ) {

		// Get vendor.
		$vendor = hivepress()->request->get_context( 'vendor' );

		// Set amount.
		if ( $vendor && $vendor->get_balance() ) {
			$form['fields']['amount']['default'] = $vendor->get_balance();
		}

		return $form;
	}

	/**
	 * Alters vendor update form.
	 *
	 * @param array  $form_args Form arguments.
	 * @param object $form Form object.
	 * @return array
	 */
	public function alter_vendor_update_form( $form_args, $form ) {

		// Get vendor.
		$vendor = $form->get_model();

		if ( ! $vendor || $vendor->is_stripe_setup() ) {
			return $form_args;
		}

		// Add country field.
		$form_args['fields']['country'] = [
			'required' => true,
			'_order'   => 40,
		];

		return $form_args;
	}

	/**
	 * Alters user update profile form.
	 *
	 * @param array  $form_args Form arguments.
	 * @param object $form Form object.
	 * @return array
	 */
	public function alter_user_update_profile_form( $form_args, $form ) {

		// Check settings.
		if ( get_option( 'hp_payout_system' ) !== 'stripe' ) {
			return $form_args;
		}

		// Get vendor.
		if ( ! $form->get_model() ) {
			return $form_args;
		}

		$vendor = Models\Vendor::query()->filter(
			[
				'status' => [ 'auto-draft', 'draft', 'publish' ],
				'user'   => $form->get_model()->get_id(),
			]
		)->get_first();

		if ( ! $vendor || $vendor->is_stripe_setup() ) {
			return $form_args;
		}

		// Set redirect URL.
		$form_args['redirect'] = hivepress()->router->get_return_url( 'vendor_stripe_page' );

		if ( hp\is_rest() ) {

			// @todo change to the core helper when added.
			$form_args['redirect'] = hivepress()->router->get_url( 'vendor_stripe_page', [ 'redirect' => $this->get_referer_url() ] );
		}

		/* translators: %s: payment service. */
		$form_args['button']['label'] = sprintf( esc_html__( 'Proceed to %s', 'hivepress-marketplace' ), 'Stripe' );

		return $form_args;
	}

	/**
	 * Adds payout admin columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function add_payout_admin_columns( $columns ) {
		return array_merge(
			array_slice( $columns, 0, 2, true ),
			[
				'vendor' => hivepress()->translator->get_string( 'vendor' ),
				'amount' => esc_html__( 'Amount', 'hivepress-marketplace' ),
			],
			array_slice( $columns, 2, null, true ),
			[
				'taxonomy-hp_payout_method' => esc_html__( 'Method', 'hivepress-marketplace' ),
			]
		);
	}

	/**
	 * Renders payout admin columns.
	 *
	 * @param string $column Column name.
	 * @param int    $payout_id Payout ID.
	 */
	public function render_payout_admin_columns( $column, $payout_id ) {
		$output = '';

		if ( 'amount' === $column ) {

			// Get amount.
			$amount = round( floatval( get_post_meta( $payout_id, 'hp_amount', true ) ), 2 );

			// Render amount.
			$output = hivepress()->woocommerce->format_price( $amount );
		} elseif ( 'vendor' === $column ) {
			$output = '&mdash;';

			// Get vendor ID.
			$vendor_id = wp_get_post_parent_id( $payout_id );

			if ( $vendor_id ) {

				// Render link.
				$output = '<a href="' . esc_url( hivepress()->router->get_admin_url( 'post', $vendor_id ) ) . '">' . esc_html( get_the_title( $vendor_id ) ) . '</a>';
			}
		}

		echo wp_kses_data( $output );
	}

	/**
	 * Alters post types.
	 *
	 * @param array $post_types Post types.
	 * @return array
	 */
	public function alter_post_types( $post_types ) {

		// Hide payouts.
		if ( get_option( 'hp_payout_system' ) ) {
			$post_types['payout']['show_ui']      = false;
			$post_types['payout']['show_in_menu'] = false;
		}

		return $post_types;
	}

	/**
	 * Alters payout settings meta box.
	 *
	 * @param array $meta_box Meta box arguments.
	 * @return array
	 */
	public function alter_payout_settings_metabox( $meta_box ) {
		if ( in_array( get_post_status(), [ 'pending', 'publish' ], true ) ) {
			$meta_box['fields']['vendor']['disabled'] = true;
		}

		return $meta_box;
	}

	/**
	 * Alters vendor dashboard page.
	 *
	 * @param array  $blocks Block arguments.
	 * @param object $template Template object.
	 * @return array
	 */
	public function alter_vendor_dashboard_page( $blocks, $template ) {

		// Check settings.
		if ( ! get_option( 'hp_payout_system' ) ) {
			return $blocks;
		}

		// Get vendor.
		$vendor = $template->get_context( 'vendor' );

		if ( ! $vendor ) {
			return $blocks;
		}

		// Add blocks.
		$new_blocks = [

			// @todo make this conditional.
			'vendor_balance'       => [
				'type' => 'content',
			],

			'payout_request_link'  => [
				'type' => 'content',
			],

			'payout_request_modal' => [
				'type' => 'content',
			],
		];

		if ( get_option( 'hp_payout_system' ) === 'stripe' && $vendor->get_country() ) {
			$new_blocks['vendor_actions_secondary'] = [
				'_order' => 20,

				'blocks' => [
					'vendor_stripe_link' => [
						'type'   => 'part',
						'path'   => 'vendor/edit/page/vendor-stripe-link',
						'_order' => 10,
					],
				],
			];
		}

		return hp\merge_trees(
			[ 'blocks' => $blocks ],
			[
				'blocks' => $new_blocks,
			]
		)['blocks'];
	}
}
