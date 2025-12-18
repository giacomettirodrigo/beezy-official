<?php
/**
 * Orders edit page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Orders edit page template class.
 *
 * @class Orders_Edit_Page
 */
class Orders_Edit_Page extends User_Account_Page {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => hivepress()->translator->get_string( 'orders' ),
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
						'blocks' => [
							'orders'           => [
								'type'   => 'orders',
								'_label' => hivepress()->translator->get_string( 'orders' ),
								'_order' => 10,
							],

							'order_pagination' => [
								'type'   => 'part',
								'path'   => 'page/pagination',
								'_label' => hivepress()->translator->get_string( 'pagination' ),
								'_order' => 20,
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
