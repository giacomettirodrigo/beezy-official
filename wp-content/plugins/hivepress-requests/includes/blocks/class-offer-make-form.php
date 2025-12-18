<?php
/**
 * Offer make form block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Offer make form block class.
 *
 * @class Offer_Make_Form
 */
class Offer_Make_Form extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'form' => 'offer_make',
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {
		if ( is_user_logged_in() ) {

			// Get request.
			$request = $this->get_context( 'request' );

			if ( $request ) {
				$this->values['request'] = $request->get_id();
			}

			// Set draft.
			if ( get_option( 'hp_offer_allow_attachment' ) ) {
				$this->context['offer'] = hivepress()->offer->get_offer_draft();

				$this->attributes['data-reset'] = 'true';
			}
		}

		parent::boot();
	}
}
