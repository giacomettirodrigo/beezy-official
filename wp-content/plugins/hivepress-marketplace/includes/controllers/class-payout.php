<?php
/**
 * Payout controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Models;
use HivePress\Forms;
use HivePress\Emails;
use HivePress\Blocks;
use HivePress\Templates;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Payout controller class.
 *
 * @class Payout
 */
final class Payout extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					'payouts_resource'      => [
						'path' => '/payouts',
						'rest' => true,
					],

					'payout_resource'       => [
						'base' => 'payouts_resource',
						'path' => '/(?P<payout_id>\d+)',
						'rest' => true,
					],

					'payout_request_action' => [
						'base'   => 'payouts_resource',
						'method' => 'POST',
						'action' => [ $this, 'request_payout' ],
						'rest'   => true,
					],

					'payout_cancel_action'  => [
						'base'   => 'payout_resource',
						'method' => 'DELETE',
						'action' => [ $this, 'cancel_payout' ],
						'rest'   => true,
					],

					'payouts_view_page'     => [
						'title'     => hivepress()->translator->get_string( 'payouts' ),
						'base'      => 'vendor_account_page',
						'path'      => '/payouts',
						'redirect'  => [ $this, 'redirect_payouts_view_page' ],
						'action'    => [ $this, 'render_payouts_view_page' ],
						'paginated' => true,
					],

					'vendor_stripe_page'    => [
						'base'     => 'vendor_account_page',
						'path'     => '/stripe',
						'redirect' => [ $this, 'redirect_vendor_stripe_page' ],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Requests payout.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function request_payout( $request ) {

		// Check settings.
		if ( ! get_option( 'hp_payout_allow_request' ) || get_option( 'hp_payout_system' ) ) {
			return hp\rest_error( 403 );
		}

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Validate form.
		$form = ( new Forms\Payout_Request() )->set_values( $request->get_params() );

		if ( ! $form->validate() ) {
			return hp\rest_error( 400, $form->get_errors() );
		}

		// Get vendor.
		$vendor = Models\Vendor::query()->filter(
			[
				'status__in' => [ 'draft', 'pending', 'publish' ],
				'user'       => get_current_user_id(),
			]
		)->get_first();

		if ( ! $vendor ) {
			return hp\rest_error( 400 );
		}

		// Get payout.
		$payout = Models\Payout::query()->filter(
			[
				'status' => 'pending',
				'user'   => get_current_user_id(),
			]
		)->get_first();

		if ( $payout ) {
			return hp\rest_error( 400, esc_html__( 'You\'ve already requested a payout.', 'hivepress-marketplace' ) );
		}

		// Create payout.
		$payout = ( new Models\Payout() )->fill(
			array_merge(
				$form->get_values(),
				[
					'status' => 'draft',
					'user'   => get_current_user_id(),
					'vendor' => $vendor->get_id(),
				]
			)
		);

		// Check amount.
		$min_amount = $payout->get_method__min_amount();

		if ( ! $min_amount ) {
			$min_amount = floatval( get_option( 'hp_payout_min_amount' ) );
		}

		if ( $min_amount && $payout->get_amount() < $min_amount ) {
			/* translators: %s: amount. */
			return hp\rest_error( 400, sprintf( esc_html__( 'You can\'t pay out less than %s.', 'hivepress-marketplace' ), hivepress()->woocommerce->format_price( $min_amount ) ) );
		}

		// Check balance.
		if ( $payout->get_amount() > $vendor->get_balance() ) {
			/* translators: %s: amount. */
			return hp\rest_error( 400, sprintf( esc_html__( 'You can\'t pay out more than %s.', 'hivepress-marketplace' ), $vendor->display_balance() ) );
		}

		// Add payout.
		if ( ! $payout->save() ) {
			return hp\rest_error( 400, $payout->_get_errors() );
		}

		// Update status.
		$payout->set_status( 'pending' )->save_status();

		return hp\rest_response(
			201,
			[
				'id' => $payout->get_id(),
			]
		);
	}

	/**
	 * Cancels payout.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function cancel_payout( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Get payout.
		$payout = Models\Payout::query()->get_by_id( $request->get_param( 'payout_id' ) );

		if ( ! $payout ) {
			return hp\rest_error( 404 );
		}

		// Check permissions.
		if ( $payout->get_status() !== 'pending' || $payout->get_user__id() !== get_current_user_id() ) {
			return hp\rest_error( 403 );
		}

		// Update status.
		$payout->set_status( 'trash' )->save_status();

		// Delete payout.
		if ( ! $payout->delete() ) {
			return hp\rest_error( 400 );
		}

		return hp\rest_response( 204 );
	}

	/**
	 * Redirects payouts view page.
	 *
	 * @return mixed
	 */
	public function redirect_payouts_view_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_return_url( 'user_login_page' );
		}

		// Check payouts.
		if ( ! hivepress()->request->get_context( 'payout_count' ) ) {
			return hivepress()->router->get_url( 'user_account_page' );
		}

		return false;
	}

	/**
	 * Renders payouts view page.
	 *
	 * @return string
	 */
	public function render_payouts_view_page() {

		// Query payouts.
		hivepress()->request->set_context(
			'post_query',
			Models\Payout::query()->filter(
				[
					'status__in' => [ 'draft', 'pending', 'publish' ],
					'user'       => get_current_user_id(),
				]
			)->order( [ 'created_date' => 'desc' ] )
			->limit( 20 )
			->paginate( hivepress()->request->get_page_number() )
			->get_args()
		);

		// Render template.
		return ( new Blocks\Template(
			[
				'template' => 'payouts_view_page',

				'context'  => [
					'payouts' => [],
				],
			]
		) )->render();
	}

	/**
	 * Redirects vendor Stripe page.
	 *
	 * @return mixed
	 */
	public function redirect_vendor_stripe_page() {

		// Check settings.
		if ( get_option( 'hp_payout_system' ) !== 'stripe' ) {
			return true;
		}

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_return_url( 'user_login_page' );
		}

		// Get vendor.
		$vendor = Models\Vendor::query()->filter(
			[
				'status' => [ 'auto-draft', 'draft', 'publish' ],
				'user'   => get_current_user_id(),
			]
		)->get_first();

		if ( ! $vendor ) {
			wp_die( hivepress()->translator->get_string( 'no_vendors_found' ) );
		}

		// Check Stripe API.
		if ( ! hivepress()->payout->stripe() ) {

			/* translators: %s: payment service. */
			wp_die( sprintf( esc_html__( '%s API key is missing or invalid.', 'hivepress-marketplace' ), 'Stripe' ) );
		}

		// Get Stripe account.
		$account = null;

		if ( $vendor->get_stripe_id() ) {
			try {

				// Get existing account.
				$account = hivepress()->payout->stripe()->accounts->retrieve( $vendor->get_stripe_id() );
			} catch ( \Exception $e ) {
				wp_die( $e->getMessage() );
			}
		} else {
			try {

				// Set account arguments.
				$account_args = [
					'type'             => 'express',
					'country'          => $vendor->get_country(),
					'email'            => $vendor->get_user__email(),

					'tos_acceptance'   => [
						'service_agreement' => 'full',
					],

					'capabilities'     => [
						'transfers'     => [ 'requested' => true ],
						'card_payments' => [ 'requested' => true ],
					],

					'business_profile' => [
						'name' => $vendor->get_name(),
						'url'  => hivepress()->router->get_url( 'vendor_view_page', [ 'vendor_id' => $vendor->get_id() ] ),
					],
				];

				if ( 'US' === wc_get_base_location()['country'] ) {

					// Set recipient countries.
					$recipient_countries = [ 'AL', 'DZ', 'AO', 'AG', 'AR', 'AM', 'AU', 'AT', 'AZ', 'BS', 'BH', 'BD', 'BE', 'BJ', 'BT', 'BO', 'BA', 'BW', 'BN', 'BG', 'KH', 'CA', 'CL', 'CO', 'CR', 'CI', 'HR', 'CY', 'CZ', 'DK', 'DO', 'EC', 'EG', 'SV', 'EE', 'ET', 'FI', 'FR', 'GA', 'GM', 'DE', 'GH', 'GR', 'GT', 'GY', 'HK', 'HU', 'IS', 'IN', 'ID', 'IE', 'IL', 'IT', 'JM', 'JP', 'JO', 'KZ', 'KE', 'KW', 'LA', 'LV', 'LI', 'LT', 'LU', 'MO', 'MG', 'MY', 'MT', 'MU', 'MX', 'MD', 'MC', 'MN', 'MA', 'MZ', 'NA', 'NL', 'NZ', 'NE', 'NG', 'MK', 'NO', 'OM', 'PK', 'PA', 'PY', 'PE', 'PH', 'PL', 'PT', 'QA', 'RO', 'RW', 'SM', 'SA', 'SN', 'RS', 'SG', 'SK', 'SI', 'ZA', 'KR', 'ES', 'LK', 'LC', 'SE', 'CH', 'TW', 'TZ', 'TH', 'TT', 'TN', 'TR', 'AE', 'GB', 'UY', 'UZ', 'VN' ];

					if ( in_array( $vendor->get_country(), $recipient_countries ) ) {

						// Change agreement type.
						$account_args['tos_acceptance']['service_agreement'] = 'recipient';

						unset( $account_args['capabilities']['card_payments'] );
					}
				}

				// Create Stripe account.
				$account = hivepress()->payout->stripe()->accounts->create(
					apply_filters(
						'hivepress/v1/components/stripe/create_account',
						$account_args
					)
				);
			} catch ( \Exception $e ) {
				wp_die( $e->getMessage() );
			}

			// Save account ID.
			$vendor->set_stripe_id( $account->id )->save_stripe_id();
		}

		if ( ! $account ) {
			/* translators: %s: payment service. */
			wp_die( sprintf( esc_html__( '%s account is not accessible.', 'hivepress-marketplace' ), 'Stripe' ) );
		}

		// Get Stripe link.
		$link = null;

		if ( $account->payouts_enabled ) {

			// Save setup flag.
			if ( ! $vendor->is_stripe_setup() ) {
				$vendor->set_stripe_setup( true )->save_stripe_setup();
			}

			// Redirect user.
			if ( hivepress()->router->get_redirect_url() ) {
				return hivepress()->router->get_redirect_url();
			}

			try {

				// Create login link.
				$link = hivepress()->payout->stripe()->accounts->createLoginLink( $vendor->get_stripe_id() );
			} catch ( \Exception $e ) {
				wp_die( $e->getMessage() );
			}
		} elseif ( hp\get_array_value( $_GET, '_return' ) ) {

			// Redirect user.
			if ( hivepress()->router->get_redirect_url() ) {
				return hivepress()->router->get_redirect_url();
			}

			return home_url();
		} else {
			try {

				// Create onboarding link.
				$link = hivepress()->payout->stripe()->accountLinks->create(
					apply_filters(
						'hivepress/v1/components/stripe/create_onboarding_link',
						[
							'account'     => $vendor->get_stripe_id(),
							'refresh_url' => hivepress()->router->get_current_url(),
							'return_url'  => add_query_arg( [ '_return' => 1 ], hivepress()->router->get_current_url() ),
							'type'        => 'account_onboarding',
						]
					)
				);
			} catch ( \Exception $e ) {
				wp_die( $e->getMessage() );
			}
		}

		if ( ! $link ) {
			/* translators: %s: payment service. */
			wp_die( sprintf( esc_html__( '%s authentication failed.', 'hivepress-marketplace' ), 'Stripe' ) );
		}

		// Redirect to Stripe.
		wp_redirect( $link->url );

		exit;
	}
}
