<?php
/**
 * Payout request email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Payout request email class.
 *
 * @class Payout_Request
 */
class Payout_Request extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Payout Requested', 'hivepress-marketplace' ),
				'body'    => hp\sanitize_html( __( 'A new payout of %payout_amount% via %payout_method% has been requested, click on the following link to view it: %payout_url%', 'hivepress-marketplace' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
