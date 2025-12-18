<?php
/**
 * Order edit page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order edit page template class.
 *
 * @class Order_Edit_Page
 */
class Order_Edit_Page extends User_Account_Page {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => hivepress()->translator->get_string( 'order' ),
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_content' => [
						'attributes' => [
							'class' => [ 'hp-order', 'hp-order--page', 'hp-order--edit-page', 'woocommerce' ],
						],

						'blocks'     => [
							'order_header'  => [
								'type'     => 'template',
								'template' => 'order_header_block',
								'_label'   => esc_html__( 'Header', 'hivepress-marketplace' ),
								'_order'   => 10,
							],

							'order_notes'   => [
								'type'   => 'order_notes',
								'_label' => esc_html__( 'Notes', 'hivepress-marketplace' ),
								'_order' => 15,
							],

							'order_content' => [
								'type'   => 'part',
								'path'   => 'order/edit/page/order-content',
								'_label' => hivepress()->translator->get_string( 'details' ),
								'_order' => 20,
							],

							'order_footer'  => [
								'type'     => 'template',
								'template' => 'order_footer_block',
								'_label'   => esc_html__( 'Footer', 'hivepress-marketplace' ),
								'_order'   => 30,
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
