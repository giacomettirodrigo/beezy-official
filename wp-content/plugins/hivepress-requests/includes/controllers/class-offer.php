<?php
/**
 * Offer controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Models;
use HivePress\Forms;
use HivePress\Emails;
use HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Offer controller class.
 *
 * @class Offer
 */
final class Offer extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					'offers_resource'     => [
						'path' => '/offers',
						'rest' => true,
					],

					'offer_resource'      => [
						'base' => 'offers_resource',
						'path' => '/(?P<offer_id>\d+)',
						'rest' => true,
					],

					'offer_make_action'   => [
						'base'   => 'offers_resource',
						'method' => 'POST',
						'action' => [ $this, 'make_offer' ],
						'rest'   => true,
					],

					'offer_delete_action' => [
						'base'   => 'offer_resource',
						'method' => 'DELETE',
						'action' => [ $this, 'delete_offer' ],
						'rest'   => true,
					],

					'offers_view_page'    => [
						'title'     => hivepress()->translator->get_string( 'offers' ),
						'base'      => 'user_account_page',
						'path'      => '/offers',
						'redirect'  => [ $this, 'redirect_offers_view_page' ],
						'action'    => [ $this, 'render_offers_view_page' ],
						'paginated' => true,
					],

					'offer_accept_page'   => [
						'path'     => '/accept-offer/(?P<offer_id>\d+)',
						'redirect' => [ $this, 'redirect_offer_accept_page' ],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Makes offer.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function make_offer( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Validate form.
		$form = ( new Forms\Offer_Make() )->set_values( $request->get_params() );

		if ( ! $form->validate() ) {
			return hp\rest_error( 400, $form->get_errors() );
		}

		// Get bidder.
		$bidder_id = $request->get_param( 'bidder' ) ? $request->get_param( 'bidder' ) : get_current_user_id();

		$bidder = Models\User::query()->get_by_id( $bidder_id );

		if ( ! $bidder ) {
			return hp\rest_error( 400 );
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_users' ) && get_current_user_id() !== $bidder->get_id() ) {
			return hp\rest_error( 403 );
		}

		// Get request.
		$_request = Models\Request::query()->get_by_id( $form->get_value( 'request' ) );

		if ( ! $_request || ! in_array( $_request->get_status(), [ 'private', 'publish' ], true ) || ( $_request->get_status() === 'private' && ! $_request->get_vendor__id() ) ) {
			return hp\rest_error( 400 );
		}

		if ( $_request->get_user__id() === $bidder->get_id() ) {
			return hp\rest_error( 403, esc_html__( 'You can\'t submit offers for your own requests.', 'hivepress-requests' ) );
		}

		// Add offer.
		$offer = ( new Models\Offer() )->fill(
			array_merge(
				$form->get_values(),
				[
					'bidder'               => $bidder->get_id(),
					'bidder__display_name' => $bidder->get_display_name(),
					'bidder__email'        => $bidder->get_email(),
					'approved'             => get_option( 'hp_offer_enable_moderation' ) ? 0 : 1,
				]
			)
		);

		if ( get_option( 'hp_offer_allow_attachment' ) ) {

			// Get offer draft.
			$offer_draft = hivepress()->offer->get_offer_draft();

			if ( $offer_draft && $offer_draft->get_attachment__id() ) {

				// Get attachment.
				$attachment = $offer_draft->get_attachment();

				if ( $attachment ) {

					// Set attachment.
					$offer->set_attachment( $attachment->get_id() );
				}
			}
		}

		if ( ! $offer->save() ) {
			return hp\rest_error( 400, $offer->_get_errors() );
		}

		// Set attachment.
		if ( isset( $attachment ) ) {
			$attachment->set_parent( $offer->get_id() )->save_parent();

			$offer_draft->set_attachment( null )->save_attachment();
		}

		// Send email.
		( new Emails\Offer_Submit(
			[
				'recipient' => get_option( 'admin_email' ),

				'tokens'    => [
					'user'      => $bidder,
					'request'   => $_request,
					'offer'     => $offer,
					'offer_url' => admin_url( 'edit-comments.php' ),
				],
			]
		) )->send();

		return hp\rest_response(
			201,
			[
				'id' => $offer->get_id(),
			]
		);
	}

	/**
	 * Deletes offer.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function delete_offer( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Get offer.
		$offer = Models\Offer::query()->get_by_id( $request->get_param( 'offer_id' ) );

		if ( ! $offer ) {
			return hp\rest_error( 404 );
		}

		// Check permissions.
		if ( get_current_user_id() !== $offer->get_bidder__id() || ! $offer->is_approved() || $offer->get_request__status() !== 'publish' ) {
			return hp\rest_error( 403 );
		}

		// Delete offer.
		if ( ! $offer->delete() ) {
			return hp\rest_error( 400 );
		}

		return hp\rest_response( 204 );
	}

	/**
	 * Redirects offers view page.
	 *
	 * @return mixed
	 */
	public function redirect_offers_view_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_return_url( 'user_login_page' );
		}

		// Check offers.
		if ( ! hivepress()->request->get_context( 'offer_count' ) ) {
			return hivepress()->router->get_url( 'user_account_page' );
		}

		return false;
	}

	/**
	 * Renders offers view page.
	 *
	 * @return string
	 */
	public function render_offers_view_page() {

		// Query offers.
		$query = Models\Offer::query()->filter(
			[
				'bidder'   => get_current_user_id(),
				'approved' => true,
			]
		)->order( [ 'created_date' => 'desc' ] );

		// Render template.
		return ( new Blocks\Template(
			[
				'template' => 'offers_view_page',

				'context'  => [
					'offer_query' => $query,
				],
			]
		) )->render();
	}

	/**
	 * Redirects offer accept page.
	 *
	 * @return mixed
	 */
	public function redirect_offer_accept_page() {

		// Check Marketplace status.
		if ( ! hivepress()->get_version( 'marketplace' ) ) {
			return true;
		}

		// Get offer.
		$offer = Models\Offer::query()->get_by_id( hivepress()->request->get_param( 'offer_id' ) );

		if ( ! $offer || ! $offer->is_approved() ) {
			return true;
		}

		// Get request.
		$request = $offer->get_request();

		if ( ! $request || ! in_array( $request->get_status(), [ 'private', 'publish' ], true ) || ( $request->get_status() === 'private' && ! $request->get_vendor__id() ) ) {
			return true;
		}

		if ( get_current_user_id() !== $request->get_user__id() ) {
			return true;
		}

		// Get vendor ID.
		$vendor_id = $request->get_vendor__id();

		if ( ! $vendor_id ) {
			$vendor_id = Models\Vendor::query()->filter(
				[
					'user'   => $offer->get_bidder__id(),
					'status' => 'publish',
				]
			)->get_first_id();
		}

		if ( ! $vendor_id ) {
			return true;
		}

		// Get product.
		$product = hivepress()->woocommerce->get_related_product( $request->get_id() );

		if ( ! $product || ! in_array( $product->get_status(), [ 'private', 'publish' ], true ) ) {
			return true;
		}

		// Update product.
		wp_update_post(
			[
				'ID'          => $product->get_id(),
				'post_author' => $offer->get_bidder__id(),
			]
		);

		// Add to cart.
		if ( ! hivepress()->marketplace->add_to_cart(
			$request,
			[],
			[
				'vendor' => $vendor_id,
				'price'  => $offer->get_price(),
			]
		) ) {
			wp_die( hivepress()->translator->get_string( 'something_went_wrong' ) );
		}

		return wc_get_page_permalink( 'checkout' );
	}
}
