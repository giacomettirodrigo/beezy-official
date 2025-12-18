<?php
/**
 * Order reject email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order reject email class.
 *
 * @class Order_Reject
 */
class Order_Reject extends Email {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'       => esc_html__( 'Order Rejected', 'hivepress-marketplace' ),
				'description' => esc_html__( 'This email is sent to users when the order delivery is rejected.', 'hivepress-marketplace' ),
				'recipient'   => hivepress()->translator->get_string( 'vendor' ),
				'tokens'      => [ 'user_name', 'order_number', 'order_url', 'order_note', 'user' ],
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
				'subject' => esc_html__( 'Delivery Rejected', 'hivepress-marketplace' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! The delivery of the order %order_number% has been rejected, click on the following link to view it: %order_url%', 'hivepress-marketplace' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
