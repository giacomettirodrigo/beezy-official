<?php
/**
 * Vendor statistics block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor statistics class.
 *
 * @class Vendor_Statistics
 */
class Vendor_Statistics extends Block {

	/**
	 * Chart attributes.
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
				'class'          => [ 'hp-chart' ],
				'data-component' => 'chart',
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

		// Get vendor.
		$vendor = $this->get_context( 'vendor' );

		if ( is_admin() && get_post_type() === 'hp_vendor' ) {
			$vendor = Models\Vendor::query()->get_by_id( get_post() );
		}

		if ( $vendor ) {

			// Get cached statistics.
			$statistics = hivepress()->cache->get_post_cache( $vendor->get_id(), 'statistics' );

			if ( is_null( $statistics ) ) {
				$statistics = [];

				// Set defaults.
				$start_time = strtotime( '-29 days' );
				$end_time   = time();

				while ( $start_time <= $end_time ) {
					$date = date( 'Y-m-d', $start_time );

					$statistics[ $date ] = [ $date, 0, 0 ];

					$start_time += DAY_IN_SECONDS;
				}

				// Get orders.
				$orders = wc_get_orders(
					[
						'type'         => 'shop_order',
						'status'       => [ 'processing','completed', 'refunded' ],
						'date_created' => '>' . strtotime( '-30 days' ),
						'meta_key'     => 'hp_vendor',
						'meta_value'   => $vendor->get_id(),
						'limit'        => -1,
					]
				);

				// Get statistics.
				foreach ( $orders as $order ) {
					$date = $order->get_date_created( 'edit' )->format( 'Y-m-d' );

					if ( isset( $statistics[ $date ] ) ) {
						$statistics[ $date ][1] += 1;
						$statistics[ $date ][2] += hivepress()->marketplace->get_order_total( $order );
					}
				}

				$statistics = array_values( $statistics );

				// Cache statistics.
				hivepress()->cache->set_post_cache( $vendor->get_id(), 'statistics', null, $statistics, DAY_IN_SECONDS );
			}

			// Get dates.
			$dates = array_map(
				function( $counts ) {
					return hp\get_first_array_value( $counts );
				},
				$statistics
			);

			// Set datasets.
			$datasets = hp\merge_arrays(
				array_combine(
					[ 'orders', 'revenue' ],
					array_fill(
						0,
						2,
						[
							'fill'        => false,
							'borderWidth' => 2,
							'data'        => [],
						]
					)
				),
				[
					'orders'  => [
						'label'                => hivepress()->translator->get_string( 'orders' ),
						'borderColor'          => '#ff6384',
						'pointBackgroundColor' => '#ff6384',
					],

					'revenue' => [
						'label'                => esc_html__( 'Revenue', 'hivepress-marketplace' ),
						'borderColor'          => '#4bc0c0',
						'pointBackgroundColor' => '#4bc0c0',
					],
				]
			);

			// Populate datasets.
			foreach ( $statistics as $counts ) {

				// Remove date.
				array_shift( $counts );

				// Add counts.
				$datasets['orders']['data'][]  = floatval( array_shift( $counts ) );
				$datasets['revenue']['data'][] = floatval( array_shift( $counts ) );
			}

			// Set totals.
			$totals = hp\merge_arrays(
				array_combine(
					[ 'today', 'yesterday', 'week', 'month' ],
					array_fill(
						0,
						4,
						[
							'period' => null,
							'data'   => [],
						]
					)
				),
				[
					'today'     => [
						'label' => esc_html__( 'Today', 'hivepress-marketplace' ),
						'start' => -1,
					],

					'yesterday' => [
						'label'  => esc_html__( 'Yesterday', 'hivepress-marketplace' ),
						'start'  => -2,
						'period' => 1,
					],

					'week'      => [
						/* translators: %s: days number. */
						'label' => sprintf( esc_html__( 'Last %s Days', 'hivepress-marketplace' ), 7 ),
						'start' => -7,
					],

					'month'     => [
						/* translators: %s: days number. */
						'label' => sprintf( esc_html__( 'Last %s Days', 'hivepress-marketplace' ), 30 ),
						'start' => -30,
					],
				]
			);

			// Calculate totals.
			foreach ( $totals as $period => $total ) {
				$totals[ $period ]['data'] = array_merge(
					$total['data'],
					[
						'orders'  => array_sum( array_slice( $datasets['orders']['data'], $total['start'], $total['period'] ) ),
						'revenue' => hivepress()->woocommerce->format_price( array_sum( array_slice( $datasets['revenue']['data'], $total['start'], $total['period'] ) ) ),
					]
				);
			}

			// Render totals.
			$output .= '<table class="hp-table">';

			// Render columns.
			$output .= '<thead><tr>';
			$output .= '<th></th>';

			foreach ( $datasets as $dataset ) {
				$output .= '<th>' . esc_html( $dataset['label'] ) . '</th>';
			}

			$output .= '</tr></thead>';

			// Render rows.
			$output .= '<tbody>';

			foreach ( $totals as $total ) {
				$output .= '<tr>';

				// Render label.
				$output .= '<th>' . esc_html( $total['label'] ) . '</th>';

				// Render counts.
				foreach ( $total['data'] as $count ) {
					$output .= '<td>' . esc_html( $count ) . '</td>';
				}

				$output .= '</tr>';
			}

			$output .= '</tbody>';
			$output .= '</table>';

			// Render chart.
			$output .= '<div><canvas data-labels="' . hp\esc_json( wp_json_encode( $dates ) ) . '" data-datasets="' . hp\esc_json( wp_json_encode( array_values( $datasets ) ) ) . '" ' . hp\html_attributes( $this->attributes ) . '></canvas></div>';
		}

		return $output;
	}
}
