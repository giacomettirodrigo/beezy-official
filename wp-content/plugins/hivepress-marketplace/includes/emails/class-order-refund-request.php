<?php
/**
 * Order refund request email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order refund request email class.
 *
 * @class Order_Refund_Request
 */
class Order_Refund_Request extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Refund Requested', 'hivepress-marketplace' ),
				'body'    => hp\sanitize_html( __( 'A refund for order %order_number% has been requested, click on the following link to view it: %order_url%', 'hivepress-marketplace' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
