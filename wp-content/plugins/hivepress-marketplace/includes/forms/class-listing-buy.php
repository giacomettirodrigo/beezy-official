<?php
/**
 * Listing buy form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing buy form class.
 *
 * @class Listing_Buy
 */
class Listing_Buy extends Model_Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'model' => 'listing',
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
				'button' => [
					'label' => esc_html__( 'Buy Now', 'hivepress-marketplace' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps form properties.
	 */
	protected function boot() {
		if ( $this->model->get_id() ) {

			// Set action.
			$this->action = hivepress()->router->get_url(
				'listing_buy_page',
				[
					'listing_id' => $this->model->get_id(),
				]
			);

			// Set rendering.
			$this->attributes['data-render'] = wp_json_encode(
				[
					'url'   => hivepress()->router->get_url( 'listing_buy_action', [ 'listing_id' => $this->model->get_id() ] ),
					'block' => 'listing_attributes_primary',
				]
			);
		}

		parent::boot();
	}
}
