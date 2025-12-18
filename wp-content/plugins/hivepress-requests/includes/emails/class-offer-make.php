<?php
/**
 * Offer make email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Offer make email class.
 *
 * @class Offer_Make
 */
class Offer_Make extends Email {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'       => esc_html__( 'Offer Received', 'hivepress-requests' ),
				'description' => esc_html__( 'This email is sent to users when a new offer is received.', 'hivepress-requests' ),
				'recipient'   => hivepress()->translator->get_string( 'user' ),
				'tokens'      => [ 'user_name', 'offer_url', 'user', 'request', 'offer' ],
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
				'subject' => esc_html__( 'Offer Received', 'hivepress-requests' ),
				'body'    => hp\sanitize_html( __( "Hi, %user_name%! You've received a new offer, click on the following link to view it: %offer_url%", 'hivepress-requests' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
