<?php
/**
 * Request submit complete page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Request submit complete page template class.
 *
 * @class Request_Submit_Complete_Page
 */
class Request_Submit_Complete_Page extends Request_Submit_Page {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => hivepress()->translator->get_string( 'add_request' ) . ' (' . hivepress()->translator->get_string( 'completed' ) . ')',
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_content' => [
						'blocks' => [
							'request_complete_message' => [
								'type'   => 'part',
								'path'   => 'request/submit/request-complete-message',
								'_label' => hivepress()->translator->get_string( 'message' ),
								'_order' => 10,
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
