<?php
/**
 * Offer component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Emails;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Offer component class.
 *
 * @class Offer
 */
final class Offer extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Check requests.
		add_action( 'hivepress/v1/events/hourly', [ $this, 'check_requests' ] );

		// Add attribute models.
		add_filter( 'hivepress/v1/components/attribute/models', [ $this, 'add_attribute_models' ] );

		// Add vendor fields.
		add_filter( 'hivepress/v1/models/vendor', [ $this, 'add_vendor_fields' ] );

		// Update request status.
		add_action( 'hivepress/v1/models/request/update_status', [ $this, 'update_request_status' ], 10, 4 );

		// Update offer status.
		add_action( 'hivepress/v1/models/offer/create', [ $this, 'update_offer_status' ], 10, 2 );
		add_action( 'hivepress/v1/models/offer/update_status', [ $this, 'update_offer_status' ], 10, 4 );

		// Validate offer.
		add_filter( 'hivepress/v1/models/offer/errors', [ $this, 'validate_offer' ], 10, 2 );

		// Add offer fields.
		add_filter( 'hivepress/v1/models/offer', [ $this, 'add_offer_fields' ] );
		add_filter( 'hivepress/v1/forms/offer_make', [ $this, 'add_offer_fields' ] );

		// Add request fields.
		add_filter( 'hivepress/v1/forms/request_update', [ $this, 'add_request_fields' ] );
		add_filter( 'hivepress/v1/models/request', [ $this, 'alter_request_model' ] );

		if ( hivepress()->get_version( 'marketplace' ) ) {

			// Update order status.
			add_action( 'woocommerce_order_status_changed', [ $this, 'update_order_status' ], 10, 4 );

			// Manage requests.
			add_action( 'hivepress/v1/models/request/create', [ $this, 'update_request' ], 10, 2 );
			add_action( 'hivepress/v1/models/request/update', [ $this, 'update_request' ], 10, 2 );
			add_action( 'hivepress/v1/models/request/delete', [ $this, 'delete_request' ] );

			// Add request attributes.
			add_filter( 'hivepress/v1/models/request/attributes', [ $this, 'add_request_attributes' ] );

			if ( ! is_admin() ) {

				// Alter templates.
				add_filter( 'hivepress/v1/templates/offer_view_block/blocks', [ $this, 'alter_offer_view_blocks' ], 10, 2 );
				add_filter( 'hivepress/v1/templates/order_footer_block/blocks', [ $this, 'alter_order_footer_blocks' ], 10, 2 );
			}
		}

		if ( is_admin() ) {

			// Manage admin columns.
			add_filter( 'manage_hp_request_posts_columns', [ $this, 'add_request_admin_columns' ] );
			add_action( 'manage_hp_request_posts_custom_column', [ $this, 'render_request_admin_columns' ], 10, 2 );

			// Hide draft offers.
			add_filter( 'comments_clauses', [ $this, 'hide_draft_offers' ] );

			// Alter meta boxes.
			add_filter( 'hivepress/v1/meta_boxes/request_images', [ $this, 'alter_request_images_meta_box' ] );
			add_filter( 'hivepress/v1/meta_boxes/request_settings', [ $this, 'alter_request_settings_meta_box' ] );
		} else {

			// Set request context.
			add_filter( 'hivepress/v1/components/request/context', [ $this, 'set_request_context' ] );

			// Allow request access.
			add_filter( 'user_has_cap', [ $this, 'allow_request_access' ], 10, 3 );

			// Redirect private request.
			add_action( 'template_redirect', [ $this, 'redirect_private_request' ] );

			// Alter menus.
			add_filter( 'hivepress/v1/menus/user_account', [ $this, 'alter_user_account_menu' ] );

			// Alter templates.
			add_filter( 'hivepress/v1/templates/site_header_block', [ $this, 'alter_site_header_block' ] );
			add_filter( 'hivepress/v1/templates/vendor_view_page', [ $this, 'alter_vendor_view_page' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Gets offer draft.
	 *
	 * @return object
	 */
	public function get_offer_draft() {
		$draft = hivepress()->request->get_context( 'offer_draft' );

		if ( ! $draft ) {

			// Get cached draft ID.
			$draft_id = hivepress()->cache->get_user_cache( get_current_user_id(), 'draft_id', 'models/offer' );

			if ( is_null( $draft_id ) ) {

				// Get draft ID.
				$draft_id = Models\Offer::query()->filter(
					[
						'bidder'      => get_current_user_id(),
						'request__in' => [ 0 ],
					]
				)->get_first_id();

				if ( ! $draft_id ) {

					// Add draft.
					$draft_id = (int) wp_insert_comment(
						[
							'comment_type'    => 'hp_offer',
							'user_id'         => get_current_user_id(),
							'comment_post_ID' => 0,
						]
					);
				}

				// Cache draft ID.
				if ( $draft_id ) {
					hivepress()->cache->set_user_cache( get_current_user_id(), 'draft_id', 'models/offer', $draft_id );
				}
			}

			if ( $draft_id ) {

				// Get draft.
				$draft = Models\Offer::query()->get_by_id( $draft_id );

				// Set request context.
				hivepress()->request->set_context( 'offer_draft', $draft );
			}
		}

		return $draft;
	}

	/**
	 * Gets order request.
	 *
	 * @param WC_Order $order Order object.
	 * @return mixed
	 */
	protected function get_order_request( $order ) {
		$request = null;

		// Get product ID.
		$product_id = hp\get_first_array_value( hivepress()->woocommerce->get_order_product_ids( $order ) );

		if ( $product_id ) {

			// Get request ID.
			$request_id = wp_get_post_parent_id( $product_id );

			if ( $request_id ) {

				// Get request.
				$request = Models\Request::query()->get_by_id( $request_id );
			}
		}

		return $request;
	}

	/**
	 * Checks requests.
	 */
	public function check_requests() {

		// Get expired requests.
		$expired_requests = Models\Request::query()->filter(
			[
				'status__in'        => [ 'private', 'publish' ],
				'expired_time__lte' => time(),
			]
		)->get();

		// Update expired requests.
		foreach ( $expired_requests as $request ) {
			if ( $request->get_status() === 'publish' || $request->get_vendor__id() ) {

				// Delete request.
				$request->trash();

				// Send email.
				$user = $request->get_user();

				if ( $user ) {
					( new Emails\Request_Expire(
						[
							'recipient' => $user->get_email(),

							'tokens'    => [
								'user'          => $user,
								'request'       => $request,
								'user_name'     => $user->get_display_name(),
								'request_title' => $request->get_title(),
							],
						]
					) )->send();
				}
			}
		}

		if ( get_option( 'hp_request_notify_vendors' ) ) {

			// Get vendors.
			$vendors = Models\Vendor::query()->filter(
				[
					'status' => 'publish',
				]
			)->limit( 10 )
			->set_args(
				[
					'orderby'    => 'meta_value_num',
					'order'      => 'ASC',

					'meta_query' => [
						'relation' => 'OR',

						[
							'key'     => 'hp_request_checked_time',
							'type'    => 'NUMERIC',
							'compare' => 'EXISTS',
						],

						[
							'key'     => 'hp_request_checked_time',
							'type'    => 'NUMERIC',
							'compare' => 'NOT EXISTS',
						],
					],
				]
			)->get();

			// Get first request.
			$request = Models\Request::query()->filter(
				[
					'status' => 'publish',
				]
			)->order( [ 'created_date' => 'desc' ] )
			->get_first();

			// Get requests URL.
			$requests_url = hivepress()->router->get_url( 'requests_view_page' );

			foreach ( $vendors as $vendor ) {
				if ( $request ) {

					// Get found time.
					$found_time = strtotime( $request->get_created_date() );

					if ( $found_time > $vendor->get_request_found_time() ) {

						// Set found time.
						$vendor->set_request_found_time( $found_time );

						// Send email.
						$user = $vendor->get_user();

						if ( $user ) {
							( new Emails\Request_Find(
								[
									'recipient' => $user->get_email(),
									'subject'   => esc_html__( 'New Requests', 'hivepress-requests' ),

									'tokens'    => [
										'user'         => $user,
										'user_name'    => $user->get_display_name(),
										'requests_url' => $requests_url,
									],
								]
							) )->send();
						}
					}
				}

				// Update vendor.
				$vendor->fill(
					[
						'request_checked_time' => time(),
					]
				)->save(
					[
						'request_checked_time',
						'request_found_time',
					]
				);
			}
		}

		// Delete offer drafts.
		if ( get_option( 'hp_offer_allow_attachment' ) ) {
			Models\Offer::query()->filter(
				[
					'request__in' => [ 0 ],
				]
			)->delete();
		}
	}

	/**
	 * Adds attribute models.
	 *
	 * @param array $models Model names.
	 * @return array
	 */
	public function add_attribute_models( $models ) {
		$models[] = 'request';

		return $models;
	}

	/**
	 * Adds vendor fields.
	 *
	 * @param array $model Model arguments.
	 * @return array
	 */
	public function add_vendor_fields( $model ) {
		$model['fields']['request_checked_time'] = [
			'type'      => 'number',
			'min_value' => 0,
			'_external' => true,
		];

		$model['fields']['request_found_time'] = [
			'type'      => 'number',
			'min_value' => 0,
			'_external' => true,
		];

		return $model;
	}

	/**
	 * Updates request status.
	 *
	 * @param int    $request_id Request ID.
	 * @param string $new_status New status.
	 * @param string $old_status Old status.
	 * @param object $request Request object.
	 */
	public function update_request_status( $request_id, $new_status, $old_status, $request ) {
		if ( get_option( 'hp_request_enable_moderation' ) && 'pending' === $old_status ) {

			// Get user.
			$user = $request->get_user();

			if ( $user ) {
				if ( 'publish' === $new_status ) {

					// Update request status.
					if ( get_option( 'hp_request_allow_sending' ) && $request->get_vendor__id() ) {
						$request->set_status( 'private' )->save_status();
					}

					// Send approval email.
					( new Emails\Request_Approve(
						[
							'recipient' => $user->get_email(),

							'tokens'    => [
								'user'          => $user,
								'request'       => $request,
								'user_name'     => $user->get_display_name(),
								'request_title' => $request->get_title(),
								'request_url'   => get_permalink( $request->get_id() ),
							],
						]
					) )->send();
				} elseif ( 'trash' === $new_status ) {

					// Send rejection email.
					( new Emails\Request_Reject(
						[
							'recipient' => $user->get_email(),

							'tokens'    => [
								'user'          => $user,
								'request'       => $request,
								'user_name'     => $user->get_display_name(),
								'request_title' => $request->get_title(),
							],
						]
					) )->send();
				}
			}
		}

		if ( in_array( $new_status, [ 'publish', 'private', 'pending' ], true ) ) {

			// Get expiration period.
			$expiration_period = absint( get_option( 'hp_request_expiration_period' ) );

			if ( $expiration_period && ! $request->get_expired_time() ) {

				// Set expiration time.
				$request->set_expired_time( time() + $expiration_period * DAY_IN_SECONDS )->save_expired_time();
			}
		}

		if ( get_option( 'hp_request_allow_sending' ) && 'private' === $new_status && $request->get_vendor__id() ) {

			// Get user.
			$user = $request->get_vendor__user();

			// Send request email.
			if ( $user ) {
				( new Emails\Request_Send(
					[
						'recipient' => $user->get_email(),
						/* translators: %s: user name. */
						'subject'   => sprintf( hp\sanitize_html( __( 'New request from %s', 'hivepress-requests' ) ), $request->get_user__display_name() ),

						'tokens'    => [
							'user'          => $user,
							'request'       => $request,
							'user_name'     => $user->get_display_name(),
							'request_title' => $request->get_title(),
							'request_url'   => get_permalink( $request->get_id() ),
						],
					]
				) )->send();
			}
		}
	}

	/**
	 * Updates offer status.
	 *
	 * @param int    $offer_id Offer ID.
	 * @param string $new_status New status.
	 * @param string $old_status Old status.
	 * @param object $offer Offer object.
	 */
	public function update_offer_status( $offer_id, $new_status, $old_status = null, $offer = null ) {

		// Get offer.
		if ( is_null( $offer ) ) {
			$offer = $new_status;
		}

		if ( ! $offer->get_request__id() ) {
			return;
		}

		// Get moderation flag.
		$moderate = get_option( 'hp_offer_enable_moderation' );

		if ( ( $moderate && 'approve' === $new_status ) || ( ! $moderate && is_object( $new_status ) ) ) {

			// Get request.
			$request = $offer->get_request();

			if ( $request && ( $request->get_status() === 'publish' || ( $request->get_status() === 'private' && $request->get_vendor__id() ) ) ) {

				// Send email.
				$user = $request->get_user();

				if ( $user ) {
					( new Emails\Offer_Make(
						[
							'recipient' => $user->get_email(),

							'tokens'    => [
								'user'      => $user,
								'request'   => $request,
								'offer'     => $offer,
								'user_name' => $user->get_display_name(),
								'offer_url' => get_permalink( $request->get_id() ) . '#offers',
							],
						]
					) )->send();
				}
			}
		}
	}

	/**
	 * Validates offer.
	 *
	 * @param array  $errors Error messages.
	 * @param object $offer Offer object.
	 * @return array
	 */
	public function validate_offer( $errors, $offer ) {
		if ( ! $offer->get_id() && ! $errors ) {

			// Get vendor ID.
			$vendor_id = Models\Vendor::query()->filter(
				[
					'user' => $offer->get_bidder__id(),
				]
			)->get_first_id();

			// Add error.
			if ( ! $vendor_id ) {
				$errors[] = hivepress()->translator->get_string( 'only_vendors_can_make_offers' );
			}

			if ( ! get_option( 'hp_offer_allow_multiple' ) ) {

				// Get offer ID.
				$offer_id = Models\Offer::query()->filter(
					[
						'bidder'  => $offer->get_bidder__id(),
						'request' => $offer->get_request__id(),
					]
				)->get_first_id();

				// Add error.
				if ( $offer_id ) {
					$errors[] = esc_html__( 'You\'ve already submitted an offer.', 'hivepress-requests' );
				}
			}
		}

		return $errors;
	}

	/**
	 * Updates order status.
	 *
	 * @param int      $order_id Order ID.
	 * @param string   $old_status Old status.
	 * @param string   $new_status New status.
	 * @param WC_Order $order Order object.
	 */
	public function update_order_status( $order_id, $old_status, $new_status, $order ) {

		// Check vendor.
		if ( ! $order->get_meta( 'hp_vendor' ) ) {
			return;
		}

		if ( in_array( $new_status, [ 'processing', 'completed', 'refunded' ], true ) ) {

			// Get request.
			$request = $this->get_order_request( $order );

			if ( $request ) {

				// Update request.
				if ( 'processing' === $new_status ) {
					$request->fill(
						[
							'status' => 'private',
							'vendor' => null,
						]
					)->save( [ 'status', 'vendor' ] );
				} else {
					$request->trash();
				}
			}
		}
	}

	/**
	 * Updates request.
	 *
	 * @param int    $request_id Request ID.
	 * @param object $request Request object.
	 */
	public function update_request( $request_id, $request ) {

		// Remove action.
		remove_action( 'hivepress/v1/models/request/update', [ $this, 'update_request' ] );

		// Get title.
		$title = get_option( 'hp_request_title_format' );

		if ( $title ) {
			$title = hp\replace_tokens( [ 'request' => $request ], $title );

			// Update title.
			if ( $request->get_title() !== $title ) {
				$request->set_title( $title )->save_title();
			}
		}

		// Get product.
		$product = hivepress()->woocommerce->get_related_product( $request->get_id() );

		if ( ! $product ) {
			if ( $request->get_status() === 'publish' || ( $request->get_status() === 'private' && $request->get_vendor__id() ) ) {

				// Add product.
				$product = new \WC_Product();

				// Set properties.
				$product->set_props(
					[
						'parent_id'          => $request->get_id(),
						'catalog_visibility' => 'hidden',
						'virtual'            => true,
					]
				);
			} else {
				return;
			}
		}

		// Set properties.
		$product->set_props(
			[
				'name'          => $request->get_title(),
				'slug'          => $request->get_slug(),
				'status'        => $request->get_status() === 'private' ? 'publish' : $request->get_status(),
				'date_created'  => $request->get_created_date(),
				'date_modified' => $request->get_modified_date(),
				'price'         => $request->get_budget(),
				'regular_price' => $request->get_budget(),
			]
		);

		// Update product.
		$product->save();
	}

	/**
	 * Deletes request.
	 *
	 * @param int $request_id Request ID.
	 */
	public function delete_request( $request_id ) {

		// Get product.
		$product = hivepress()->woocommerce->get_related_product( $request_id );

		// Delete product.
		if ( $product ) {
			$product->delete( true );
		}
	}

	/**
	 * Adds request attributes.
	 *
	 * @param array $attributes Attributes.
	 * @return array
	 */
	public function add_request_attributes( $attributes ) {

		// Add budget attribute.
		$attributes['budget'] = [
			'editable'      => true,
			'filterable'    => true,
			'sortable'      => true,

			'display_areas' => [
				'view_block_primary',
				'view_page_primary',
			],

			'edit_field'    => [
				'label'     => esc_html__( 'Budget', 'hivepress-requests' ),
				'type'      => 'currency',
				'min_value' => 0,
				'required'  => true,
				'_order'    => 25,
			],

			'search_field'  => [
				'label'  => esc_html__( 'Budget', 'hivepress-requests' ),
				'type'   => 'number_range',
				'_order' => 100,
			],
		];

		return $attributes;
	}

	/**
	 * Adds offer fields.
	 *
	 * @param array $model Model arguments.
	 * @return array
	 */
	public function add_offer_fields( $model ) {
		if ( hivepress()->get_version( 'marketplace' ) && get_option( 'hp_offer_allow_bidding' ) ) {

			// Add price field.
			$model['fields']['price'] = [
				'label'     => hivepress()->translator->get_string( 'price' ),
				'type'      => 'currency',
				'min_value' => 0,
				'required'  => true,
				'_external' => true,
				'_order'    => 5,
			];
		}

		if ( get_option( 'hp_offer_allow_attachment' ) ) {

			// Get file formats.
			$formats = hivepress()->request->get_context( 'offer_attachment_types' );

			if ( ! is_array( $formats ) ) {
				$formats = array_filter( explode( '|', implode( '|', (array) get_option( 'hp_offer_attachment_types' ) ) ) );

				hivepress()->request->set_context( 'offer_attachment_types', $formats );
			}

			// Add attachment field.
			$model['fields']['attachment'] = [
				'label'     => hivepress()->translator->get_string( 'attachment' ),
				'type'      => 'attachment_upload',
				'formats'   => $formats,
				'protected' => true,
				'_model'    => 'attachment',
				'_external' => true,
				'_order'    => 20,
			];
		}

		return $model;
	}

	/**
	 * Adds request fields.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function add_request_fields( $form ) {

		// Remove title field.
		if ( get_option( 'hp_request_title_format' ) ) {
			unset( $form['fields']['title'] );
		}

		return $form;
	}

	/**
	 * Alters request model.
	 *
	 * @param array $model Model arguments.
	 * @return array
	 */
	public function alter_request_model( $model ) {
		if ( get_option( 'hp_request_title_format' ) ) {
			$model['fields']['title']['required'] = false;
		}

		return $model;
	}

	/**
	 * Adds request admin columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function add_request_admin_columns( $columns ) {
		return array_merge(
			array_slice( $columns, 0, 2, true ),
			[
				'user' => hivepress()->translator->get_string( 'user' ),
			],
			array_slice( $columns, 2, null, true )
		);
	}

	/**
	 * Renders request admin columns.
	 *
	 * @param string $column Column name.
	 * @param int    $request_id Request ID.
	 */
	public function render_request_admin_columns( $column, $request_id ) {
		$output = '';

		if ( 'user' === $column ) {

			// Get user ID.
			$user_id = get_post_field( 'post_author', $request_id );

			if ( $user_id ) {

				// Get user name.
				$name = get_the_author_meta( 'user_login', $user_id );

				// Get user URL.
				$url = admin_url(
					'user-edit.php?' . http_build_query(
						[
							'user_id' => $user_id,
						]
					)
				);

				// Render user link.
				$output = '<a href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a>';
			}
		}

		echo wp_kses_data( $output );
	}

	/**
	 * Hides draft offers.
	 *
	 * @param array $clauses Clauses.
	 * @return array
	 */
	public function hide_draft_offers( $clauses ) {
		$clauses['where'] .= " AND (comment_type!='hp_offer' OR comment_post_ID!=0)";

		return $clauses;
	}

	/**
	 * Alters request images meta box.
	 *
	 * @param array $meta_box Meta box arguments.
	 * @return array
	 */
	public function alter_request_images_meta_box( $meta_box ) {

		// Get request.
		$request = Models\Request::query()->get_by_id( get_post() );

		if ( $request ) {

			// Set image IDs.
			$meta_box['fields']['images']['default'] = $request->get_images__id();
		}

		return $meta_box;
	}

	/**
	 * Alters request settings meta box.
	 *
	 * @param array $meta_box Meta box arguments.
	 * @return array
	 */
	public function alter_request_settings_meta_box( $meta_box ) {

		// Get request.
		$request = Models\Request::query()->get_by_id( get_post() );

		if ( $request && ! $request->get_vendor__id() ) {

			// Remove field.
			unset( $meta_box['fields']['vendor'] );
		}

		return $meta_box;
	}

	/**
	 * Sets request context.
	 *
	 * @param array $context Request context.
	 * @return array
	 */
	public function set_request_context( $context ) {

		// Get cached request count.
		$request_count = hivepress()->cache->get_user_cache( get_current_user_id(), 'request_count', 'models/request' );

		if ( is_null( $request_count ) ) {

			// Get request count.
			$request_count = Models\Request::query()->filter(
				[
					'status__in' => [ 'private', 'publish' ],
					'user'       => get_current_user_id(),
				]
			)->get_count();

			// Cache request count.
			hivepress()->cache->set_user_cache( get_current_user_id(), 'request_count', 'models/request', $request_count );
		}

		// Set request context.
		$context['request_count'] = $request_count;

		if ( current_user_can( 'edit_posts' ) ) {

			// Get cached offer count.
			$offer_count = hivepress()->cache->get_user_cache( get_current_user_id(), 'offer_count', 'models/offer' );

			if ( is_null( $offer_count ) ) {

				// Get offer count.
				$offer_count = Models\Offer::query()->filter(
					[
						'approved' => 1,
						'bidder'   => get_current_user_id(),
					]
				)->get_count();

				// Cache offer count.
				hivepress()->cache->set_user_cache( get_current_user_id(), 'offer_count', 'models/offer', $offer_count );
			}

			// Set request context.
			$context['offer_count'] = $offer_count;
		}

		return $context;
	}

	/**
	 * Allows request access.
	 *
	 * @param array $caps All capabilities.
	 * @param array $cap Required capability.
	 * @param array $args Access arguments.
	 * @return array
	 */
	public function allow_request_access( $caps, $cap, $args ) {

		// Get capabilities.
		$required_cap  = hp\get_first_array_value( $cap );
		$requested_cap = hp\get_first_array_value( $args );

		if ( 'read_post' === $requested_cap && 'read_private_posts' === $required_cap ) {

			// Get request ID.
			$request_id = hp\get_last_array_value( $args );

			if ( $request_id && get_post_type( $request_id ) === 'hp_request' ) {

				// Add capability.
				$caps[ $required_cap ] = true;
			}
		}

		return $caps;
	}

	/**
	 * Redirects private request.
	 */
	public function redirect_private_request() {
		if ( ! is_user_logged_in() && is_404() && 'hp_request' === get_query_var( 'post_type' ) ) {
			wp_safe_redirect( hivepress()->router->get_return_url( 'user_login_page' ) );

			exit;
		}
	}

	/**
	 * Alters user account menu.
	 *
	 * @param array $menu Menu arguments.
	 * @return array
	 */
	public function alter_user_account_menu( $menu ) {
		if ( hivepress()->request->get_context( 'request_count' ) ) {
			$menu['items']['requests_edit'] = [
				'route'  => 'requests_edit_page',
				'_order' => 10,
			];
		}

		if ( hivepress()->request->get_context( 'offer_count' ) ) {
			$menu['items']['offers_view'] = [
				'route'  => 'offers_view_page',
				'_order' => 15,
			];
		}

		return $menu;
	}

	/**
	 * Alters site header block.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_site_header_block( $template ) {
		return hp\merge_trees(
			$template,
			[
				'blocks' => [
					'site_header_menu' => [
						'blocks' => [
							'request_submit_link' => [
								'type'   => 'part',
								'path'   => 'request/submit/request-submit-link',
								'_order' => 15,
							],
						],
					],
				],
			]
		);
	}

	/**
	 * Alters offer view blocks.
	 *
	 * @param array  $blocks Template blocks.
	 * @param object $template Template object.
	 * @return array
	 */
	public function alter_offer_view_blocks( $blocks, $template ) {
		$new_blocks = [];

		// Get request.
		$request = $template->get_context( 'request' );

		// Add actions.
		if ( $request && get_current_user_id() === $request->get_user__id() ) {
			$new_blocks['offer_actions_primary'] = [
				'blocks' => [
					'vendor_view_link'  => [
						'type' => 'content',
					],

					'offer_accept_form' => [
						'type'   => 'form',
						'form'   => 'offer_accept',
						'_order' => 10,
					],
				],
			];
		}

		// Add attributes.
		if ( get_option( 'hp_offer_allow_bidding' ) ) {
			$new_blocks['offer_attributes_primary'] = [
				'blocks' => [
					'offer_price' => [
						'type'   => 'part',
						'path'   => 'offer/view/offer-price',
						'_order' => 10,
					],
				],
			];
		}

		return hp\merge_trees(
			[ 'blocks' => $blocks ],
			[ 'blocks' => $new_blocks ]
		)['blocks'];
	}

	/**
	 * Alters order footer blocks.
	 *
	 * @param array  $blocks Template blocks.
	 * @param object $template Template object.
	 * @return array
	 */
	public function alter_order_footer_blocks( $blocks, $template ) {

		// Get order.
		$order = wc_get_order( $template->get_context( 'order' )->get_id() );

		// Get request.
		$request = $this->get_order_request( $order );

		if ( $request && $request->get_status() === 'private' ) {

			// Set template context.
			$template->set_context( 'request', $request );

			// Add template blocks.
			$blocks = hp\merge_trees(
				[ 'blocks' => $blocks ],
				[
					'blocks' => [
						'order_actions_primary' => [
							'blocks' => [
								'request_view_link' => [
									'type'   => 'part',
									'path'   => 'order/view/page/request-view-link',
									'_order' => 15,
								],
							],
						],
					],
				]
			)['blocks'];
		}

		return $blocks;
	}

	/**
	 * Alters vendor view page.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_vendor_view_page( $template ) {
		if ( get_option( 'hp_request_allow_sending' ) ) {
			$template = hp\merge_trees(
				$template,
				[
					'blocks' => [
						'vendor_actions_primary' => [
							'blocks' => [
								'request_send_link' => [
									'type'   => 'part',
									'path'   => 'vendor/view/page/request-send-link',
									'_order' => 20,
								],
							],
						],
					],
				]
			);
		}

		return $template;
	}
}
