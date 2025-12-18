<?php
/**
 * Request reject email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Request reject email class.
 *
 * @class Request_Reject
 */
class Request_Reject extends Email {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'       => esc_html__( 'Request Rejected', 'hivepress-requests' ),
				'description' => esc_html__( 'This email is sent to users when a request is rejected.', 'hivepress-requests' ),
				'recipient'   => hivepress()->translator->get_string( 'user' ),
				'tokens'      => [ 'user_name', 'request_title', 'user', 'request' ],
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
				'subject' => esc_html__( 'Request Rejected', 'hivepress-requests' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! Unfortunately, your request "%request_title%" has been rejected.', 'hivepress-requests' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
