<?php
/**
 * Payout cancel form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Payout cancel form class.
 *
 * @class Payout_Cancel
 */
class Payout_Cancel extends Model_Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'model' => 'payout',
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
				'description' => esc_html__( 'Are you sure you want to cancel this payout request?', 'hivepress-marketplace' ),
				'method'      => 'DELETE',
				'redirect'    => true,

				'button'      => [
					'label' => esc_html__( 'Cancel Request', 'hivepress-marketplace' ),
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
				'payout_cancel_action',
				[
					'payout_id' => $this->model->get_id(),
				]
			);
		}

		parent::boot();
	}
}
