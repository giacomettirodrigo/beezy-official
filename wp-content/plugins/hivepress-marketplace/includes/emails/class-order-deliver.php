<?php
/**
 * Order deliver email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order deliver email class.
 *
 * @class Order_Deliver
 */
class Order_Deliver extends Email {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'       => esc_html__( 'Order Delivered', 'hivepress-marketplace' ),
				'description' => esc_html__( 'This email is sent to users when an order is delivered.', 'hivepress-marketplace' ),
				'recipient'   => hivepress()->translator->get_string( 'user' ),
				'tokens'      => [ 'user_name', 'order_number', 'order_url', 'user' ],
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
				'subject' => esc_html__( 'Order Delivered', 'hivepress-marketplace' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! The order %order_number% has been delivered, click on the following link to view it: %order_url%', 'hivepress-marketplace' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
