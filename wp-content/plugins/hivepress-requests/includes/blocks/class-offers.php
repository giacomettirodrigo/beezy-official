<?php
/**
 * Offers block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Offers block class.
 *
 * @class Offers
 */
class Offers extends Block {

	/**
	 * Columns number.
	 *
	 * @var int
	 */
	protected $columns;

	/**
	 * Container attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {

		// Set attributes.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'class' => [ 'hp-offers', 'hp-block', 'hp-grid' ],
			]
		);

		parent::boot();
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get request.
		$request = $this->get_context( 'request' );

		// Get visibility.
		$visibility = current_user_can( 'edit_others_posts' ) ? 'users' : get_option( 'hp_offer_display_restriction' );

		if ( ! $request || ( ( $request->get_status() === 'publish' || $request->get_vendor__id() ) && ( ! $visibility || is_user_logged_in() ) ) ) {

			// Get column width.
			$column_width = hp\get_column_width( $this->columns );

			// Query offers.
			$offers = $this->get_context( 'offer_query' );

			if ( ! $offers ) {
				$offers = Models\Offer::query()->filter(
					[
						'request'  => $request->get_id(),
						'approved' => true,
					]
				)->order( [ 'created_date' => 'desc' ] );

				if ( 'author' === $visibility && $request->get_user__id() !== get_current_user_id() ) {
					$offers->filter( [ 'bidder' => get_current_user_id() ] );
				}
			}

			$offers->get();

			// Render offers.
			if ( $offers->count() ) {
				$output  = '<div ' . hp\html_attributes( $this->attributes ) . '>';
				$output .= '<div class="hp-row">';

				// Get user.
				$user = $this->get_context( 'user' );

				// Get vendor.
				$vendor = null;

				foreach ( $offers as $offer ) {

					// Get request.
					if ( ! $request || $request->get_id() !== $offer->get_request__id() ) {
						$request = $offer->get_request();
					}

					// Get vendor.
					if ( ! $vendor || $vendor->get_user__id() !== $offer->get_bidder__id() ) {
						$vendor = Models\Vendor::query()->filter(
							[
								'user' => $offer->get_bidder__id(),
							]
						)->get_first();
					}

					if ( $request && $vendor ) {

						// Render offer.
						$output .= '<div class="hp-grid__item hp-col-sm-' . esc_attr( $column_width ) . ' hp-col-xs-12">';

						$output .= ( new Template(
							[
								'template' => 'offer_view_block',

								'context'  => [
									'offer'   => $offer,
									'request' => $request,
									'vendor'  => $vendor,
									'user'    => $user,
								],
							]
						) )->render();

						$output .= '</div>';
					}
				}

				$output .= '</div>';
				$output .= '</div>';
			}
		}

		return $output;
	}
}
