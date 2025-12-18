<?php
/**
 * Order dispute email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order dispute email class.
 *
 * @class Order_Dispute
 */
class Order_Dispute extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Order Disputed', 'hivepress-marketplace' ),
				'body'    => hp\sanitize_html( __( 'Order %order_number% %order_url% has been disputed with the following details: %dispute_details%', 'hivepress-marketplace' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
