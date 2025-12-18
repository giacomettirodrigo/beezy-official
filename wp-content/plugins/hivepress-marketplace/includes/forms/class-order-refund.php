<?php
/**
 * Order refund form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order refund form class.
 *
 * @class Order_Refund
 */
class Order_Refund extends Model_Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'model' => 'order',
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
				'redirect' => true,

				'fields'   => [
					'amount' => [
						'label'     => esc_html__( 'Amount', 'hivepress-marketplace' ),
						'type'      => 'currency',
						'min_value' => 1,
						'required'  => true,
						'_separate' => true,
						'_order'    => 10,
					],

					'reason' => [
						'label'      => esc_html__( 'Reason', 'hivepress-marketplace' ),
						'type'       => 'textarea',
						'max_length' => 2056,
						'required'   => true,
						'_separate'  => true,
						'_order'     => 20,
					],
				],

				'button'   => [
					'label' => esc_html__( 'Submit Request', 'hivepress-marketplace' ),
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
				'order_refund_action',
				[
					'order_id' => $this->model->get_id(),
				]
			);
		}

		parent::boot();
	}
}
