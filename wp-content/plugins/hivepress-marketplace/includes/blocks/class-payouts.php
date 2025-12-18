<?php
/**
 * Payouts block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Payouts block class.
 *
 * @class Payouts
 */
class Payouts extends Block {

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		if ( have_posts() ) {
			$output .= '<table class="hp-payouts hp-table hp-block">';

			while ( have_posts() ) {
				the_post();

				// Get payout.
				$payout = Models\Payout::query()->get_by_id( get_post() );

				if ( $payout ) {

					// Render payout.
					$output .= ( new Template(
						[
							'template' => 'payout_view_block',

							'context'  => [
								'payout' => $payout,
							],
						]
					) )->render();
				}
			}

			$output .= '</table>';

			// Reset query.
			wp_reset_postdata();
		}

		return $output;
	}
}
