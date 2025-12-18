<?php
/**
 * Request send email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Request send email class.
 *
 * @class Request_Send
 */
class Request_Send extends Email {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'       => esc_html__( 'Request Received', 'hivepress-requests' ),
				'description' => esc_html__( 'This email is sent to users when a personal request is received.', 'hivepress-requests' ),
				'recipient'   => hivepress()->translator->get_string( 'vendor' ),
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
				'subject' => esc_html__( 'Request Received', 'hivepress-requests' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! You\'ve received a new request "%request_title%", click on the following link to view it: %request_url%', 'hivepress-requests' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
