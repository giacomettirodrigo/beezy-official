<?php
/**
 * Order refund fail email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order refund fail email class.
 *
 * @class Order_Refund_Fail
 */
class Order_Refund_Fail extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Refund Failed', 'hivepress-marketplace' ),
				'body'    => hp\sanitize_html( __( 'Refund for order %order_number% %order_url% couldn\'t be processed for the following reason: %fail_reason%', 'hivepress-marketplace' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
