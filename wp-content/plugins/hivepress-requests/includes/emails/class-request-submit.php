<?php
/**
 * Request submit email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Request submit email class.
 *
 * @class Request_Submit
 */
class Request_Submit extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Request Submitted', 'hivepress-requests' ),
				'body'    => hp\sanitize_html( __( 'A new request "%request_title%" has been submitted, click on the following link to view it: %request_url%', 'hivepress-requests' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
