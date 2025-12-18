<?php
/**
 * Request controller.
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
 * Request controller class.
 *
 * @class Request
 */
final class Request extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					'requests_resource'            => [
						'path' => '/requests',
						'rest' => true,
					],

					'request_resource'             => [
						'base' => 'requests_resource',
						'path' => '/(?P<request_id>\d+)',
						'rest' => true,
					],

					'request_update_action'        => [
						'base'   => 'request_resource',
						'method' => 'POST',
						'action' => [ $this, 'update_request' ],
						'rest'   => true,
					],

					'request_delete_action'        => [
						'base'   => 'request_resource',
						'method' => 'DELETE',
						'action' => [ $this, 'delete_request' ],
						'rest'   => true,
					],

					'requests_view_page'           => [
						'url'      => [ $this, 'get_requests_view_url' ],
						'match'    => [ $this, 'is_requests_view_page' ],
						'redirect' => [],
						'action'   => [ $this, 'render_requests_view_page' ],
					],

					'request_view_page'            => [
						'url'      => [ $this, 'get_request_view_url' ],
						'match'    => [ $this, 'is_request_view_page' ],
						'action'   => [ $this, 'render_request_view_page' ],

						'redirect' => [
							[
								'callback' => [ $this, 'redirect_request_view_page' ],
								'_order'   => 5,
							],
						],
					],

					'requests_edit_page'           => [
						'title'     => hivepress()->translator->get_string( 'requests' ),
						'base'      => 'user_account_page',
						'path'      => '/requests',
						'redirect'  => [ $this, 'redirect_requests_edit_page' ],
						'action'    => [ $this, 'render_requests_edit_page' ],
						'paginated' => true,
					],

					'request_submit_page'          => [
						'path'     => '/submit-request',

						'redirect' => [
							[
								'callback' => [ $this, 'redirect_request_submit_page' ],
								'_order'   => 5,
							],
						],
					],

					'request_submit_details_page'  => [
						'title'    => hivepress()->translator->get_string( 'add_details_imperative' ),
						'base'     => 'request_submit_page',
						'path'     => '/details',
						'redirect' => [ $this, 'redirect_request_submit_details_page' ],
						'action'   => [ $this, 'render_request_submit_details_page' ],
					],

					'request_submit_complete_page' => [
						'title'    => esc_html__( 'Request Submitted', 'hivepress-requests' ),
						'base'     => 'request_submit_page',
						'path'     => '/complete',
						'redirect' => [ $this, 'redirect_request_submit_complete_page' ],
						'action'   => [ $this, 'render_request_submit_complete_page' ],
					],

					'request_category_view_page'   => [
						'url' => [ $this, 'get_request_category_view_url' ],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Updates request.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function update_request( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Get request.
		$_request = Models\Request::query()->get_by_id( $request->get_param( 'request_id' ) );

		if ( ! $_request ) {
			return hp\rest_error( 404 );
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_others_posts' ) && ( get_current_user_id() !== $_request->get_user__id() || ! in_array( $_request->get_status(), [ 'auto-draft', 'private', 'publish' ], true ) || ( $_request->get_status() === 'private' && ! $_request->get_vendor__id() ) ) ) {
			return hp\rest_error( 403 );
		}

		// Validate form.
		$form = null;

		if ( $_request->get_status() === 'auto-draft' ) {
			$form = new Forms\Request_Submit( [ 'model' => $_request ] );
		} else {
			$form = new Forms\Request_Update( [ 'model' => $_request ] );
		}

		$form->set_values( $request->get_params() );

		if ( ! $form->validate() ) {
			return hp\rest_error( 400, $form->get_errors() );
		}

		// Update request.
		$_request->fill( $form->get_values() );

		if ( ! $_request->save() ) {
			return hp\rest_error( 400, $_request->_get_errors() );
		}

		return hp\rest_response(
			200,
			[
				'id' => $_request->get_id(),
			]
		);
	}

	/**
	 * Deletes request.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function delete_request( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Get request.
		$_request = Models\Request::query()->get_by_id( $request->get_param( 'request_id' ) );

		if ( ! $_request ) {
			return hp\rest_error( 404 );
		}

		// Check permissions.
		if ( ! current_user_can( 'delete_others_posts' ) && ( get_current_user_id() !== $_request->get_user__id() || ! in_array( $_request->get_status(), [ 'auto-draft', 'private', 'publish' ], true ) || ( $_request->get_status() === 'private' && ! $_request->get_vendor__id() ) ) ) {
			return hp\rest_error( 403 );
		}

		// Delete request.
		if ( ! $_request->trash() ) {
			return hp\rest_error( 400 );
		}

		return hp\rest_response( 204 );
	}

	/**
	 * Gets requests view URL.
	 *
	 * @param array $params URL parameters.
	 * @return string
	 */
	public function get_requests_view_url( $params ) {
		return get_post_type_archive_link( 'hp_request' );
	}

	/**
	 * Matches requests view URL.
	 *
	 * @return bool
	 */
	public function is_requests_view_page() {

		// Get page ID.
		$page_id = absint( get_option( 'hp_page_requests' ) );

		return ( $page_id && is_page( $page_id ) ) || is_post_type_archive( 'hp_request' ) || ( is_tax() && strpos( get_queried_object()->taxonomy, 'hp_request_' ) === 0 );
	}

	/**
	 * Renders requests view page.
	 *
	 * @return string
	 */
	public function render_requests_view_page() {
		if ( is_page() ) {

			// Query requests.
			hivepress()->request->set_context(
				'post_query',
				Models\Request::query()->filter(
					[
						'status' => 'publish',
					]
				)->order( [ 'created_date' => 'desc' ] )
				->limit( get_option( 'hp_requests_per_page' ) )
				->paginate( hivepress()->request->get_page_number() )
				->get_args()
			);
		}

		// Render template.
		return ( new Blocks\Template(
			[
				'template' => 'requests_view_page',

				'context'  => [
					'requests' => [],
				],
			]
		) )->render();
	}

	/**
	 * Gets request view URL.
	 *
	 * @param array $params URL parameters.
	 * @return string
	 */
	public function get_request_view_url( $params ) {
		return get_permalink( hp\get_array_value( $params, 'request_id' ) );
	}

	/**
	 * Matches request view URL.
	 *
	 * @return bool
	 */
	public function is_request_view_page() {
		return is_singular( 'hp_request' );
	}

	/**
	 * Redirects request view page.
	 *
	 * @return mixed
	 */
	public function redirect_request_view_page() {
		the_post();

		// Get request.
		$request = Models\Request::query()->get_by_id( get_post() );

		// Set request context.
		hivepress()->request->set_context( 'request', $request );

		if ( 'private' === $request->get_status() ) {

			// Check authentication.
			if ( ! is_user_logged_in() ) {
				return hivepress()->router->get_return_url( 'user_login_page' );
			}

			// Check permissions.
			if ( $request->get_vendor__id() ) {
				$user = $request->get_vendor__user();

				if ( $user && ! current_user_can( 'edit_others_posts' ) && ! in_array( get_current_user_id(), [ $request->get_user__id(), $user->get_id() ] ) ) {
					return home_url();
				}
			}
		}

		return false;
	}

	/**
	 * Renders request view page.
	 *
	 * @return string
	 */
	public function render_request_view_page() {
		return ( new Blocks\Template(
			[
				'template' => 'request_view_page',

				'context'  => [
					'request' => hivepress()->request->get_context( 'request' ),
					'user'    => hivepress()->request->get_context( 'request' )->get_user(),
				],
			]
		) )->render();
	}

	/**
	 * Redirects requests edit page.
	 *
	 * @return mixed
	 */
	public function redirect_requests_edit_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_return_url( 'user_login_page' );
		}

		// Check requests.
		if ( ! hivepress()->request->get_context( 'request_count' ) ) {
			return hivepress()->router->get_url( 'user_account_page' );
		}

		return false;
	}

	/**
	 * Renders requests edit page.
	 *
	 * @return string
	 */
	public function render_requests_edit_page() {

		// Query requests.
		hivepress()->request->set_context(
			'post_query',
			Models\Request::query()->filter(
				[
					'status__in' => [ 'private', 'publish' ],
					'user'       => get_current_user_id(),
				]
			)->order( [ 'created_date' => 'desc' ] )
			->limit( get_option( 'hp_requests_per_page' ) )
			->paginate( hivepress()->request->get_page_number() )
			->get_args()
		);

		// Render template.
		return ( new Blocks\Template(
			[
				'template' => 'requests_edit_page',

				'context'  => [
					'requests' => [],
				],
			]
		) )->render();
	}

	/**
	 * Redirects request submit page.
	 *
	 * @return mixed
	 */
	public function redirect_request_submit_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_return_url( 'user_login_page' );
		}

		// Get request.
		$request = Models\Request::query()->filter(
			[
				'status'  => 'auto-draft',
				'drafted' => true,
				'user'    => get_current_user_id(),
			]
		)->get_first();

		if ( ! $request ) {

			// Add request.
			$request = ( new Models\Request() )->fill(
				[
					'status'  => 'auto-draft',
					'drafted' => true,
					'user'    => get_current_user_id(),
				]
			);

			if ( ! $request->save( [ 'status', 'drafted', 'user' ] ) ) {
				return home_url();
			}
		}

		if ( get_option( 'hp_request_allow_sending' ) && isset( $_GET['vendor_id'] ) ) {

			// Get vendor ID.
			$vendor_id = absint( $_GET['vendor_id'] );

			if ( $vendor_id && $request->get_vendor__id() !== $vendor_id ) {

				// Get vendor.
				$vendor = Models\Vendor::query()->get_by_id( $vendor_id );

				if ( $vendor && $vendor->get_status() === 'publish' && $vendor->get_user__id() !== get_current_user_id() ) {
					$request->set_vendor( $vendor->get_id() )->save_vendor();
				} else {
					return home_url();
				}
			} elseif ( ! $vendor_id ) {
				$request->set_vendor( null )->save_vendor();
			}
		}

		// Set request context.
		hivepress()->request->set_context( 'request', $request );

		return true;
	}

	/**
	 * Redirects request submit details page.
	 *
	 * @return mixed
	 */
	public function redirect_request_submit_details_page() {

		// Get request.
		$request = hivepress()->request->get_context( 'request' );

		// Check request.
		if ( $request->validate() ) {
			return true;
		}

		return false;
	}

	/**
	 * Renders request submit details page.
	 *
	 * @return string
	 */
	public function render_request_submit_details_page() {
		return ( new Blocks\Template(
			[
				'template' => 'request_submit_details_page',

				'context'  => [
					'request' => hivepress()->request->get_context( 'request' ),
				],
			]
		) )->render();
	}

	/**
	 * Redirects request submit complete page.
	 *
	 * @return mixed
	 */
	public function redirect_request_submit_complete_page() {

		// Get request.
		$request = hivepress()->request->get_context( 'request' );

		// Get moderation flag.
		$moderate = get_option( 'hp_request_enable_moderation' );

		// Get status.
		$status = 'publish';

		if ( $moderate ) {
			$status = 'pending';
		} elseif ( get_option( 'hp_request_allow_sending' ) && $request->get_vendor__id() ) {
			$status = 'private';
		}

		// Update request.
		$request->fill(
			[
				'status'  => $status,
				'drafted' => null,
			]
		)->save( [ 'status', 'drafted' ] );

		// Send email.
		( new Emails\Request_Submit(
			[
				'recipient' => get_option( 'admin_email' ),

				'tokens'    => [
					'request'       => $request,
					'request_title' => $request->get_title(),
					'request_url'   => $moderate ? get_preview_post_link( $request->get_id() ) : get_permalink( $request->get_id() ),
				],
			]
		) )->send();

		if ( ! $moderate ) {
			return get_permalink( $request->get_id() );
		}

		return false;
	}

	/**
	 * Renders request submit complete page.
	 *
	 * @return string
	 */
	public function render_request_submit_complete_page() {
		return ( new Blocks\Template(
			[
				'template' => 'request_submit_complete_page',

				'context'  => [
					'request' => hivepress()->request->get_context( 'request' ),
				],
			]
		) )->render();
	}

	/**
	 * Gets request category view URL.
	 *
	 * @param array $params URL parameters.
	 * @return string
	 */
	public function get_request_category_view_url( $params ) {
		return get_term_link( hp\get_array_value( $params, 'request_category_id' ) );
	}
}
