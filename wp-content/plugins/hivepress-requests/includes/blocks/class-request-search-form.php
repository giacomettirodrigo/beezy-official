<?php
/**
 * Request search form block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Request search form block class.
 *
 * @class Request_Search_Form
 */
class Request_Search_Form extends Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Block meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => esc_html__( 'Request Search Form', 'hivepress-requests' ),
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'form'       => 'request_search',

				'attributes' => [
					'class' => [ 'hp-form--listing-search', 'hp-form--wide', 'hp-form--primary', 'hp-block' ],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
