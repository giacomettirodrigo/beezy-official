<?php
/**
 * Order reject form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order reject form class.
 *
 * @class Order_Reject
 */
class Order_Reject extends Model_Form {

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
				'description' => esc_html__( 'Are you sure you want to reject the order delivery?', 'hivepress-marketplace' ),
				'redirect'    => true,

				'fields'      => [
					'note' => [
						'label'      => esc_html__( 'Note', 'hivepress-marketplace' ),
						'type'       => 'textarea',
						'max_length' => 2056,
						'required'   => true,
						'_separate'  => true,
						'_order'     => 10,
					],
				],

				'button'      => [
					'label' => esc_html__( 'Reject Delivery', 'hivepress-marketplace' ),
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
				'order_reject_action',
				[
					'order_id' => $this->model->get_id(),
				]
			);
		}

		parent::boot();
	}
}
