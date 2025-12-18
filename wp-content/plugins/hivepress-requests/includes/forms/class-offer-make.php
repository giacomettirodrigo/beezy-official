<?php
/**
 * Offer make form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Offer make form class.
 *
 * @class Offer_Make
 */
class Offer_Make extends Model_Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'   => esc_html__( 'Submit Offer', 'hivepress-requests' ),
				'captcha' => false,
				'model'   => 'offer',
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
				'message' => esc_html__( 'Your offer has been submitted.', 'hivepress-requests' ),
				'action'  => hivepress()->router->get_url( 'offer_make_action' ),

				'fields'  => [
					'text'    => [
						'_order' => 10,
					],

					'request' => [
						'display_type' => 'hidden',
					],
				],

				'button'  => [
					'label' => esc_html__( 'Submit Offer', 'hivepress-requests' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
