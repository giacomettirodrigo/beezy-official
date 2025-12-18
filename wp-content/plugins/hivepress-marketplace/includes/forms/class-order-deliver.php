<?php
/**
 * Order deliver form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order deliver form class.
 *
 * @class Order_Deliver
 */
class Order_Deliver extends Model_Form {

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
				'description' => esc_html__( 'Are you sure you want to mark this order as delivered?', 'hivepress-marketplace' ),
				'redirect'    => true,

				'fields'      => [
					'note' => [
						'label'      => esc_html__( 'Note', 'hivepress-marketplace' ),
						'type'       => 'textarea',
						'max_length' => 2056,
						'_separate'  => true,
						'_order'     => 10,
					],
				],

				'button'      => [
					'label' => esc_html__( 'Deliver Order', 'hivepress-marketplace' ),
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
				'order_deliver_action',
				[
					'order_id' => $this->model->get_id(),
				]
			);
		}

		parent::boot();
	}
}
