<?php
/**
 * Request approve email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Request approve email class.
 *
 * @class Request_Approve
 */
class Request_Approve extends Email {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'       => esc_html__( 'Request Approved', 'hivepress-requests' ),
				'description' => esc_html__( 'This email is sent to users when a request is approved.', 'hivepress-requests' ),
				'recipient'   => hivepress()->translator->get_string( 'user' ),
				'tokens'      => [ 'user_name', 'request_title', 'request_url', 'user', 'request' ],
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
				'subject' => esc_html__( 'Request Approved', 'hivepress-requests' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! Your request "%request_title%" has been approved, click on the following link to view it: %request_url%', 'hivepress-requests' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
