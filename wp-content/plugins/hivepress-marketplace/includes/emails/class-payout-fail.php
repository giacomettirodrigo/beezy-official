<?php
/**
 * Payout fail email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Payout fail email class.
 *
 * @class Payout_Fail
 */
class Payout_Fail extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Payout Failed', 'hivepress-marketplace' ),
				'body'    => hp\sanitize_html( __( 'Payout for order %order_number% %order_url% couldn\'t be processed for the following reason: %fail_reason%', 'hivepress-marketplace' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
