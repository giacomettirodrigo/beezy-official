<?php
/**
 * Request submit form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Request submit form class.
 *
 * @class Request_Submit
 */
class Request_Submit extends Request_Update {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'   => esc_html__( 'Submit Request', 'hivepress-requests' ),
				'captcha' => false,
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'message'  => null,
				'redirect' => true,

				'button'   => [
					'label' => esc_html__( 'Submit Request', 'hivepress-requests' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
