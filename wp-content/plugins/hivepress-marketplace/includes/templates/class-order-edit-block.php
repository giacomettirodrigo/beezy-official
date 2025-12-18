<?php
/**
 * Order edit block template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order edit block template class.
 *
 * @class Order_Edit_Block
 */
class Order_Edit_Block extends Template {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'order_container' => [
						'type'       => 'container',
						'tag'        => 'tr',
						'_order'     => 10,

						'attributes' => [
							'class' => [ 'hp-order', 'hp-order--edit-block' ],
						],

						'blocks'     => [
							'order_number'          => [
								'type'   => 'part',
								'path'   => 'order/edit/block/order-number',
								'_order' => 10,
							],

							'order_total'           => [
								'type'   => 'part',
								'path'   => 'order/edit/block/order-total',
								'_order' => 20,
							],

							'order_status'          => [
								'type'   => 'part',
								'path'   => 'order/edit/block/order-status',
								'_order' => 30,
							],

							'order_created_date'    => [
								'type'   => 'part',
								'path'   => 'order/edit/block/order-created-date',
								'_order' => 40,
							],

							'order_actions_primary' => [
								'type'       => 'container',
								'tag'        => 'td',
								'blocks'     => [],
								'_order'     => 50,

								'attributes' => [
									'class' => [ 'hp-order__actions', 'hp-order__actions--primary' ],
								],
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
