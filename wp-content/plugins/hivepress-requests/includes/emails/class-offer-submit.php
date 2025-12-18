<?php
/**
 * Offer submit email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Offer submit email class.
 *
 * @class Offer_Submit
 */
class Offer_Submit extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Offer Submitted', 'hivepress-requests' ),
				'body'    => hp\sanitize_html( __( 'A new offer has been submitted, click on the following link to view it: %offer_url%', 'hivepress-requests' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
