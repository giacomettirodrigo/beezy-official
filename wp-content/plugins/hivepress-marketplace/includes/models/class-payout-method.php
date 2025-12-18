<?php
/**
 * Payout method model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Payout method model class.
 *
 * @class Payout_Method
 */
class Payout_Method extends Term {

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

					'min_amount' => [
						'type'      => 'currency',
						'min_value' => 0.01,
						'_external' => true,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
