<?php
/**
 * Order header block template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order header block template class.
 *
 * @class Order_Header_Block
 */
class Order_Header_Block extends Template {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'order_details_primary' => [
						'type'       => 'container',
						'_order'     => 10,

						'attributes' => [
							'class' => [ 'hp-order__details', 'hp-order__details--primary' ],
						],

						'blocks'     => [
							'order_created_date' => [
								'type'   => 'part',
								'path'   => 'order/view/page/order-created-date',
								'_order' => 10,
							],

							'order_buyer'        => [
								'type'   => 'part',
								'path'   => 'order/view/page/order-buyer',
								'_order' => 20,
							],

							'order_vendor'       => [
								'type'   => 'part',
								'path'   => 'order/view/page/order-vendor',
								'_order' => 30,
							],

							'order_status'       => [
								'type'   => 'part',
								'path'   => 'order/view/order-status',
								'_order' => 40,
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
