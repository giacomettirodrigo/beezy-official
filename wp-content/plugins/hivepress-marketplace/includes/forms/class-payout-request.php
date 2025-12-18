<?php
/**
 * Payout request form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Payout request form class.
 *
 * @class Payout_Request
 */
class Payout_Request extends Model_Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'model' => 'payout',
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'message' => esc_html__( 'Your payout request has been submitted.', 'hivepress-marketplace' ),
				'action'  => hivepress()->router->get_url( 'payout_request_action' ),

				'fields'  => [
					'amount'  => [
						'_order' => 10,
					],

					'method'  => [
						'_order' => 20,
					],

					'details' => [
						'required' => true,
						'_order'   => 30,
					],
				],

				'button'  => [
					'label' => esc_html__( 'Submit Request', 'hivepress-marketplace' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
