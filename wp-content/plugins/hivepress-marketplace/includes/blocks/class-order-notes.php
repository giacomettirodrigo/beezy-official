<?php
/**
 * Order notes block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order notes class.
 *
 * @class Order_Notes
 */
class Order_Notes extends Block {

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get order.
		$order = $this->get_context( 'order' );

		if ( ! $order ) {
			return $output;
		}

		$order = wc_get_order( $order->get_id() );

		// Get notes.
		$notes = $order->get_customer_order_notes();

		if ( $notes ) {
			$output .= '<div class="hp-messages hp-grid">';

			foreach ( array_reverse( $notes ) as $note ) {
				$output .= '<div class="hp-grid__item">';

				// Get note.
				$note = Models\Order_Note::query()->get_by_id( $note );

				// Render note.
				$output .= ( new Template(
					[
						'template' => 'order_note_view_block',

						'context'  => [
							'order_note' => $note,
						],
					]
				) )->render();

				$output .= '</div>';
			}

			$output .= '</div>';
		}

		return $output;
	}
}
