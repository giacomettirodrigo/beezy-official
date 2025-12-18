<?php
/**
 * Order footer block template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order footer block template class.
 *
 * @class Order_Footer_Block
 */
class Order_Footer_Block extends Template {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'order_actions_primary' => [
						'type'       => 'container',
						'_order'     => 10,

						'attributes' => [
							'class' => [ 'hp-order__actions', 'hp-order__actions--primary' ],
						],

						'blocks'     => [
							'order_deliver_modal'  => [
								'type'        => 'modal',
								'title'       => esc_html__( 'Deliver Order', 'hivepress-marketplace' ),
								'_capability' => 'edit_posts',
								'_order'      => 5,

								'blocks'      => [
									'order_deliver_form' => [
										'type' => 'form',
										'form' => 'order_deliver',
									],
								],
							],

							'order_reject_modal'   => [
								'type'        => 'modal',
								'title'       => esc_html__( 'Reject Delivery', 'hivepress-marketplace' ),
								'_capability' => 'read',
								'_order'      => 5,

								'blocks'      => [
									'order_reject_form' => [
										'type' => 'form',
										'form' => 'order_reject',
									],
								],
							],

							'order_complete_modal' => [
								'type'        => 'modal',
								'title'       => esc_html__( 'Complete Order', 'hivepress-marketplace' ),
								'_capability' => 'read',
								'_order'      => 5,

								'blocks'      => [
									'order_complete_form' => [
										'type' => 'form',
										'form' => 'order_complete',
									],
								],
							],

							'order_dispute_modal'  => [
								'type'        => 'modal',
								'title'       => esc_html__( 'Dispute Order', 'hivepress-marketplace' ),
								'_capability' => 'read',
								'_order'      => 5,

								'blocks'      => [
									'order_dispute_form' => [
										'type' => 'form',
										'form' => 'order_dispute',
									],
								],
							],

							'order_refund_modal'   => [
								'type'        => 'modal',
								'title'       => esc_html__( 'Refund Order', 'hivepress-marketplace' ),
								'_capability' => 'edit_posts',
								'_order'      => 5,

								'blocks'      => [
									'order_refund_form' => [
										'type' => 'form',
										'form' => 'order_refund',
									],
								],
							],

							'order_deliver_link'   => [
								'type'   => 'part',
								'path'   => 'order/edit/page/order-deliver-link',
								'_order' => 20,
							],

							'order_complete_link'  => [
								'type'   => 'part',
								'path'   => 'order/view/page/order-complete-link',
								'_order' => 30,
							],

							'order_reject_link'    => [
								'type'   => 'part',
								'path'   => 'order/view/page/order-reject-link',
								'_order' => 40,
							],

							'order_dispute_link'   => [
								'type'   => 'part',
								'path'   => 'order/view/page/order-dispute-link',
								'_order' => 50,
							],

							'order_refund_link'    => [
								'type'   => 'part',
								'path'   => 'order/edit/page/order-refund-link',
								'_order' => 60,
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
