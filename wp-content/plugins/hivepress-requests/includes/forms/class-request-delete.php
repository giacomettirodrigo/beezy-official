<?php
/**
 * Request delete form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Request delete form class.
 *
 * @class Request_Delete
 */
class Request_Delete extends Model_Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'model' => 'request',
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'description' => esc_html__( 'Are you sure you want to permanently delete this request?', 'hivepress-requests' ),
				'method'      => 'DELETE',
				'redirect'    => hivepress()->router->get_url( 'requests_edit_page' ),

				'button'      => [
					'label' => esc_html__( 'Delete Request', 'hivepress-requests' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps form properties.
	 */
	protected function boot() {

		// Set action.
		if ( $this->model->get_id() ) {
			$this->action = hivepress()->router->get_url(
				'request_delete_action',
				[
					'request_id' => $this->model->get_id(),
				]
			);
		}

		parent::boot();
	}
}
