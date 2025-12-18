<?php
/**
 * Listing tag controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing tag controller class.
 *
 * @class Listing_Tag
 */
final class Listing_Tag extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					'listing_tag_view_page' => [
						'url'    => [ $this, 'get_listing_tag_view_url' ],
						'match'  => [ $this, 'is_listing_tag_view_page' ],
						'action' => [ $this, 'render_listing_tag_view_page' ],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Gets listing tag view URL.
	 *
	 * @param array $params URL parameters.
	 * @return string
	 */
	public function get_listing_tag_view_url( $params ) {
		return get_term_link( hp\get_array_value( $params, 'listing_tag_id' ) );
	}

	/**
	 * Matches listing tag view URL.
	 *
	 * @return bool
	 */
	public function is_listing_tag_view_page() {
		return is_tax( 'hp_listing_tags' );
	}

	/**
	 * Renders listing tag view page.
	 *
	 * @return string
	 */
	public function render_listing_tag_view_page() {
		return ( new Blocks\Template(
			[
				'template' => 'listings_view_page',

				'context'  => [
					'listings' => [],
				],
			]
		) )->render();
	}
}
