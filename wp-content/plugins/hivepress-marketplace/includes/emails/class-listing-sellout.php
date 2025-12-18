<?php
/**
 * Listing sellout email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing sellout email class.
 *
 * @class Listing_Sellout
 */
class Listing_Sellout extends Email {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'       => hivepress()->translator->get_string( 'listing_sold_out' ),
				'description' => esc_html__( 'This email is sent to users when a listing is sold out.', 'hivepress-marketplace' ),
				'recipient'   => hivepress()->translator->get_string( 'vendor' ),
				'tokens'      => [ 'user_name', 'listing_title', 'listing_url', 'user', 'listing' ],
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
				'subject' => hivepress()->translator->get_string( 'listing_sold_out' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! Your listing "%listing_title%" has been sold out, click on the following link to update it: %listing_url%', 'hivepress-marketplace' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
