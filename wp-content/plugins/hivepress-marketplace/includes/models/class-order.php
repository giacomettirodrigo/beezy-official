<?php
/**
 * Order model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order model class.
 *
 * @class Order
 */
class Order extends Post {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Model meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'alias' => 'shop_order',
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields' => [
					'status'         => [
						'type'    => 'select',
						'options' => wc_get_order_statuses(),
						'_alias'  => 'post_status',
					],

					'created_date'   => [
						'type'   => 'date',
						'format' => 'Y-m-d H:i:s',
						'_alias' => 'post_date',
					],

					'delivered_time' => [
						'type'      => 'number',
						'min_value' => 0,
						'_external' => true,
					],

					'total'          => [
						'type'      => 'currency',
						'min_value' => 0,
						'required'  => true,
						'_alias'    => '_order_total',
						'_external' => true,
					],

					'revision_limit' => [
						'type'      => 'number',
						'min_value' => 0,
						'_external' => true,
					],

					'buyer'          => [
						'type'      => 'id',
						'_model'    => 'user',
						'_alias'    => '_customer_user',
						'_external' => true,
					],

					'seller'         => [
						'type'     => 'id',
						'required' => true,
						'_model'   => 'user',
						'_alias'   => 'post_author',
					],

					'vendor'         => [
						'type'      => 'id',
						'_model'    => 'vendor',
						'_external' => true,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
