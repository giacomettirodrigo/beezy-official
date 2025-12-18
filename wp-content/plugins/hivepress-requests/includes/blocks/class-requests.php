<?php
/**
 * Requests block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Requests block class.
 *
 * @class Requests
 */
class Requests extends Block {

	/**
	 * Columns number.
	 *
	 * @var int
	 */
	protected $columns;

	/**
	 * Requests number.
	 *
	 * @var int
	 */
	protected $number;

	/**
	 * Request category ID.
	 *
	 * @var int
	 */
	protected $category;

	/**
	 * Requests order.
	 *
	 * @var string
	 */
	protected $order;

	/**
	 * Container attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Class initializer.
	 *
	 * @param array $meta Block meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'    => hivepress()->translator->get_string( 'requests' ),

				'settings' => [
					'columns'  => [
						'label'    => hivepress()->translator->get_string( 'columns_number' ),
						'type'     => 'select',
						'default'  => 1,
						'required' => true,
						'_order'   => 10,

						'options'  => [
							1 => '1',
							2 => '2',
							3 => '3',
						],
					],

					'number'   => [
						'label'     => hivepress()->translator->get_string( 'items_number' ),
						'type'      => 'number',
						'min_value' => 1,
						'default'   => 3,
						'required'  => true,
						'_order'    => 20,
					],

					'category' => [
						'label'       => hivepress()->translator->get_string( 'category' ),
						'type'        => 'select',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_request_category' ],
						'_order'      => 30,
					],

					'order'    => [
						'label'    => hivepress()->translator->get_string( 'sort_order' ),
						'type'     => 'select',
						'required' => true,
						'_order'   => 200,

						'options'  => [
							'created_date' => hivepress()->translator->get_string( 'by_date_added' ),
							'random'       => hivepress()->translator->get_string( 'by_random' ),
						],
					],
				],
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
				'number' => get_option( 'hp_requests_per_page' ),
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {

		// Set attributes.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'class' => [ 'hp-requests', 'hp-block', 'hp-grid' ],
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
		global $wp_query;

		$output = '';

		if ( $this->number ) {

			// Get column width.
			$column_width = hp\get_column_width( $this->columns );

			// Get request query.
			$query = $wp_query;

			if ( ! isset( $this->context['requests'] ) ) {

				// Get requests.
				$requests = $this->get_context( 'request_query' );

				if ( ! $requests ) {
					$requests = Models\Request::query()->filter(
						[
							'status' => 'publish',
						]
					)->limit( $this->number )
					->set_args(
						hivepress()->attribute->get_query_args(
							'request',
							array_diff_key( $this->get_args(), get_object_vars( $this ) )
						)
					);

					// Set category.
					if ( $this->category ) {
						$requests->filter( [ 'categories__in' => $this->category ] );
					}

					// Set order.
					if ( 'random' === $this->order ) {
						$requests->order( 'random' );
					} else {
						$requests->order( [ 'created_date' => 'desc' ] );
					}
				}

				// Query requests.
				$query = new \WP_Query( $requests->get_args() );
			}

			if ( $query->have_posts() ) {
				$output .= '<div ' . hp\html_attributes( $this->attributes ) . '>';
				$output .= '<div class="hp-row">';

				// Render requests.
				while ( $query->have_posts() ) {
					$query->the_post();

					// Get request.
					$request = Models\Request::query()->get_by_id( get_post() );

					if ( $request ) {
						$output .= '<div class="hp-grid__item hp-col-sm-' . esc_attr( $column_width ) . ' hp-col-xs-12">';

						// Render request.
						$output .= ( new Template(
							[
								'template' => 'request_view_block',

								'context'  => [
									'request' => $request,
								],
							]
						) )->render();

						$output .= '</div>';
					}
				}

				$output .= '</div>';
				$output .= '</div>';
			}

			// Reset query.
			wp_reset_postdata();
		}

		return $output;
	}
}
