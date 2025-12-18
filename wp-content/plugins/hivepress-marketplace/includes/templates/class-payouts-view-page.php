<?php
/**
 * Payouts view page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Payouts view page template class.
 *
 * @class Payouts_View_Page
 */
class Payouts_View_Page extends User_Account_Page {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => hivepress()->translator->get_string( 'payouts' ),
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
							'payouts'           => [
								'type'   => 'payouts',
								'_label' => hivepress()->translator->get_string( 'payouts' ),
								'_order' => 10,
							],

							'payout_pagination' => [
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
