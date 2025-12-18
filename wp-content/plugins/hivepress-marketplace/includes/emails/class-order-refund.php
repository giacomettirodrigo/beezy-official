<?php
/**
 * Order refund email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order refund email class.
 *
 * @class Order_Refund
 */
class Order_Refund extends Email {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'       => esc_html__( 'Order Refunded', 'hivepress-marketplace' ),
				'description' => esc_html__( 'This email is sent to users when an order is refunded.', 'hivepress-marketplace' ),
				'recipient'   => hivepress()->translator->get_string( 'vendor' ),
				'tokens'      => [ 'user_name', 'order_number', 'order_amount', 'order_url', 'user' ],
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Order Refunded', 'hivepress-marketplace' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! The order %order_number% of %order_amount% has been refunded, click on the following link to view it: %order_url%', 'hivepress-marketplace' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
