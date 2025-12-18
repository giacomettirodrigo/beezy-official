<?php
/**
 * Listing tag model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing tag model class.
 *
 * @class Listing_Tag
 */
class Listing_Tag extends Term {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Model meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'alias' => 'hp_listing_tags',
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
					'name'       => [
						'type'       => 'text',
						'max_length' => 256,
						'required'   => true,
						'_alias'     => 'name',
					],

					'item_count' => [
						'type'      => 'number',
						'min_value' => 0,
						'_alias'    => 'count',
					],

					'default'    => [
						'type'      => 'checkbox',
						'_external' => true,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
