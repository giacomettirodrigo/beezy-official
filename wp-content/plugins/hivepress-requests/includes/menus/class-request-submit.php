<?php
/**
 * Request submit menu.
 *
 * @package HivePress\Menus
 */

namespace HivePress\Menus;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Request submit menu class.
 *
 * @class Request_Submit
 */
class Request_Submit extends Menu {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Menu meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'chained' => true,
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Menu arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'items' => [
					'request_submit'          => [
						'route'  => 'request_submit_page',
						'_order' => 0,
					],

					'request_submit_details'  => [
						'route'  => 'request_submit_details_page',
						'_order' => 10,
					],

					'request_submit_complete' => [
						'route'  => 'request_submit_complete_page',
						'_order' => 1000,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
