<?php
/**
 * Theme component.
 *
 * @package HiveTheme\Components
 */

namespace HiveTheme\Components;

use HiveTheme\Helpers as ht;
use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Theme component class.
 *
 * @class Theme
 */
final class Theme extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Set hero background.
		add_action( 'wp_enqueue_scripts', [ $this, 'set_hero_background' ] );

		// Render hero content.
		add_filter( 'hivetheme/v1/areas/site_hero', [ $this, 'render_hero_content' ] );

		// Check HivePress status.
		if ( ! ht\is_plugin_active( 'hivepress' ) ) {
			return;
		}

		if ( ! is_admin() ) {

			// Alter strings.
			add_filter( 'hivepress/v1/strings', [ $this, 'alter_strings' ] );

			// Alter templates.
			add_filter( 'hivepress/v1/templates/listing_categories_view_page', [ $this, 'alter_listing_categories_view_page' ], 100 );

			add_filter( 'hivepress/v1/templates/listing_category_view_block', [ $this, 'alter_listing_category_view_block' ], 100 );

			add_filter( 'hivepress/v1/templates/listing_view_page', [ $this, 'alter_listing_view_page' ], 100 );
			add_filter( 'hivepress/v1/templates/listing_view_block/blocks', [ $this, 'alter_listing_view_block' ], 100, 2 );

			add_filter( 'hivepress/v1/templates/vendor_view_page', [ $this, 'alter_vendor_view_page' ], 100 );
			add_filter( 'hivepress/v1/templates/vendor_view_block', [ $this, 'alter_vendor_view_block' ], 100 );
		}

		// Alter blocks.
		add_filter( 'hivepress/v1/blocks/listings/meta', [ $this, 'alter_slider_block_meta' ] );
		add_filter( 'hivepress/v1/blocks/vendors/meta', [ $this, 'alter_slider_block_meta' ] );
		add_filter( 'hivepress/v1/blocks/reviews/meta', [ $this, 'alter_slider_block_meta' ] );
		add_filter( 'hivepress/v1/blocks/listings', [ $this, 'alter_slider_block_args' ], 10, 2 );
		add_filter( 'hivepress/v1/blocks/vendors', [ $this, 'alter_slider_block_args' ], 10, 2 );
		add_filter( 'hivepress/v1/blocks/reviews', [ $this, 'alter_slider_block_args' ], 10, 2 );

		parent::__construct( $args );
	}

	/**
	 * Sets hero background.
	 */
	public function set_hero_background() {
		$style = '';

		// Set site background.
		if ( get_theme_mod( 'background_noise', true ) ) {
			$background_url = hivetheme()->get_url( 'parent' ) . '/assets/images/backgrounds/noise.png';

			$style .= '.site-header,.content-section{background-image:url(' . esc_url( $background_url ) . ')}';
		}

		// Set hero background.
		if ( is_page() && has_post_thumbnail() ) {
			$image_url = get_the_post_thumbnail_url( null, 'full' );

			$style .= '.header-hero--large{background-image:url(' . esc_url( $image_url ) . ')}';
		}

		if ( $style ) {
			wp_add_inline_style( 'hivetheme-core-frontend', $style );
		}
	}

	/**
	 * Renders hero content.
	 *
	 * @param string $output Hero content.
	 * @return string
	 */
	public function render_hero_content( $output ) {
		$classes = [];

		// Render header.
		if ( is_page() ) {

			// Get content.
			$content = '';

			$parts = get_extended( get_post_field( 'post_content' ) );

			if ( $parts['extended'] ) {
				$content = apply_filters( 'the_content', $parts['main'] );

				$classes[] = 'header-hero--large';
			} else {
				$classes[] = 'header-hero--title';
			}

			// Check title.
			$title = get_the_ID() !== absint( get_option( 'page_on_front' ) );

			if ( ht\is_plugin_active( 'hivepress' ) ) {

				// @todo change condition when common category pages are added.
				$title = $title && ! hivepress()->request->get_context( 'post_query' ) && hivepress()->router->get_current_route_name() !== 'listings_view_page';
			}

			// Render part.
			if ( $content ) {
				$output .= $content;
			} elseif ( $title ) {
				$output .= hivetheme()->template->render_part( 'templates/page/page-title' );
			}
		} elseif ( is_singular( 'post' ) ) {

			// Add classes.
			$classes = array_merge(
				$classes,
				[
					'post',
					'post--single',
					'header-hero--medium',
				]
			);

			// Render part.
			$output .= hivetheme()->template->render_part( 'templates/post/single/post-header' );
		} elseif ( ht\is_plugin_active( 'hivepress' ) && is_tax( 'hp_listing_category' ) ) {

			// Add classes.
			$classes = array_merge(
				$classes,
				[
					'hp-listing-category',
					'hp-listing-category--view-page',
					'header-hero--medium',
				]
			);

			// Render part.
			$output .= hivetheme()->template->render_part(
				'hivepress/listing-category/view/page/listing-category-header',
				[
					'listing_category' => \HivePress\Models\Listing_Category::query()->get_by_id( get_queried_object() ),
				]
			);
		}

		// Filter output.
		// @deprecated Since version 1.1.0.
		$output = apply_filters( 'hivetheme/v1/areas/page_header', $output );

		// Add wrapper.
		if ( $output ) {
			$output = hivetheme()->template->render_part(
				'templates/page/page-header',
				[
					'class'   => implode( ' ', $classes ),
					'content' => $output,
				]
			);
		}

		return $output;
	}

	/**
	 * Alters strings.
	 *
	 * @param array $strings Strings.
	 * @return array
	 */
	public function alter_strings( $strings ) {
		$strings['reply_to_listing'] = hivepress()->translator->get_string( 'send_message' );

		return $strings;
	}

	/**
	 * Alters listing categories view page.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_listing_categories_view_page( $template ) {
		return hivepress()->template->merge_blocks(
			$template,
			[
				'listing_categories' => [
					'columns' => 4,
				],
			]
		);
	}

	/**
	 * Alters listing category view block.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_listing_category_view_block( $template ) {
		return hivepress()->template->merge_blocks(
			$template,
			[
				'listing_category_count' => [
					'path' => 'listing-category/view/block/listing-category-item-count',
				],
			]
		);
	}

	/**
	 * Alters listing view page.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_listing_view_page( $template ) {
		hivepress()->template->fetch_block( $template, 'listing_created_date' );

		$template = hivepress()->template->merge_blocks(
			$template,
			[
				'page_content' => [
					'blocks' => [
						'listing_category' => array_merge(
							hivepress()->template->fetch_block( $template, 'listing_category' ),
							[
								'_order' => 5,
							]
						),
					],
				],

				'page_sidebar' => [
					'blocks' => [
						'listing_topbar' => [
							'type'       => 'container',
							'_order'     => 10,

							'attributes' => [
								'class' => [ 'hp-listing__topbar', 'hp-widget', 'widget' ],
							],

							'blocks'     => [
								'listing_attributes_primary' => hivepress()->template->fetch_block( $template, 'listing_attributes_primary' ),
							],
						],
					],
				],
			]
		);

		if ( hivepress()->get_version( 'reviews' ) ) {
			$template = hivepress()->template->merge_blocks(
				$template,
				[
					'listing_topbar' => [
						'blocks' => [
							'listing_rating' => array_merge(
								hivepress()->template->fetch_block( $template, 'listing_rating' ),
								[
									'_order' => 20,
								]
							),
						],
					],
				]
			);
		}

		return $template;
	}

	/**
	 * Alters listing view block.
	 *
	 * @param array  $blocks Template blocks.
	 * @param object $template Template object.
	 * @return array
	 */
	public function alter_listing_view_block( $blocks, $template ) {
		hivepress()->template->fetch_blocks( $blocks, [ 'listing_created_date', 'listing_category' ] );

		if ( get_option( 'hp_vendor_enable_display' ) ) {

			// Get listing.
			$listing = $template->get_context( 'listing' );

			if ( $listing ) {

				// Get vendor.
				$vendor = $listing->get_vendor();

				if ( $vendor && $vendor->get_status() === 'publish' ) {

					// Set context.
					$template->set_context( 'vendor', $vendor );

					// Add blocks.
					$blocks = hivepress()->template->merge_blocks(
						$blocks,
						[
							'listing_content' => [
								'blocks' => [
									'listing_topbar' => [
										'type'       => 'container',
										'_order'     => 1,

										'attributes' => [
											'class' => [ 'hp-listing__topbar' ],
										],

										'blocks'     => [
											'listing_vendor' => [
												'type'   => 'container',
												'_order' => 10,

												'attributes' => [
													'class' => [ 'hp-vendor', 'hp-vendor--embed-block' ],
												],

												'blocks' => [
													'vendor_image' => [
														'type' => 'part',
														'path' => 'vendor/view/block/vendor-image',
														'_order' => 10,
													],

													'vendor_details' => [
														'type' => 'container',
														'_order' => 20,

														'attributes' => [
															'class' => [ 'hp-vendor__details' ],
														],

														'blocks' => [
															'vendor_name' => [
																'type' => 'container',
																'tag'  => 'h6',
																'_order' => 10,

																'attributes' => [
																	'class' => [ 'hp-vendor__name' ],
																],

																'blocks' => [
																	'vendor_name_text' => [
																		'type' => 'part',
																		'path' => 'vendor/view/block/vendor-name',
																		'_order' => 10,
																	],
																],
															],

															'vendor_verified_badge' => [
																'type' => 'part',
																'path' => 'vendor/view/vendor-verified-badge',
																'_order' => 20,
															],
														],
													],
												],
											],
										],
									],
								],
							],
						]
					);

					if ( hivepress()->get_version( 'reviews' ) ) {
						$blocks = hivepress()->template->merge_blocks(
							$blocks,
							[
								'listing_topbar' => [
									'blocks' => [
										'listing_rating' => array_merge(
											hivepress()->template->fetch_block( $blocks, 'listing_rating' ),
											[
												'_order' => 20,
											]
										),
									],
								],
							]
						);
					}
				}
			}
		}

		return $blocks;
	}

	/**
	 * Alters vendor view page.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_vendor_view_page( $template ) {
		$template = hivepress()->template->merge_blocks(
			$template,
			[
				'vendor_summary' => [
					'blocks' => [
						'vendor_verified_badge' => array_merge(
							hivepress()->template->fetch_block( $template, 'vendor_verified_badge' ),
							[
								'_order' => 1,
							]
						),
					],
				],
			]
		);

		if ( hivepress()->get_version( 'reviews' ) ) {
			$template = hivepress()->template->merge_blocks(
				$template,
				[
					'vendor_rating' => [
						'_order' => 1,
					],
				]
			);
		}

		return $template;
	}

	/**
	 * Alters vendor view block.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_vendor_view_block( $template ) {
		$template = hivepress()->template->merge_blocks(
			$template,
			[
				'vendor_header' => [
					'blocks' => [
						'vendor_verified_badge' => array_merge(
							hivepress()->template->fetch_block( $template, 'vendor_verified_badge' ),
							[
								'_order' => 1,
							]
						),
					],
				],
			]
		);

		if ( hivepress()->get_version( 'reviews' ) ) {
			$template = hivepress()->template->merge_blocks(
				$template,
				[
					'vendor_rating' => [
						'_order' => 1,
					],
				]
			);
		}

		return $template;
	}

	/**
	 * Alters slider block meta.
	 *
	 * @param array $meta Block meta.
	 * @return array
	 */
	public function alter_slider_block_meta( $meta ) {
		$meta['settings']['slider'] = [
			'label'  => esc_html__( 'Display in a slider', 'taskhive' ),
			'type'   => 'checkbox',
			'_order' => 100,
		];

		return $meta;
	}

	/**
	 * Alters slider block arguments.
	 *
	 * @param array  $args Block arguments.
	 * @param object $block Block object.
	 * @return array
	 */
	public function alter_slider_block_args( $args, $block ) {
		if ( hp\get_array_value( $args, 'slider' ) ) {
			$attributes = [
				'data-component' => 'slider',
				'class'          => [ 'content-slider' ],
			];

			if ( $block::get_meta( 'name' ) !== 'reviews' ) {
				$attributes['data-type'] = 'carousel';

				$attributes['class'][] = 'content-slider--carousel';
				$attributes['class'][] = 'alignfull';
			} else {
				$attributes['class'][] = 'content-slider--gallery';
			}

			$args['attributes'] = hp\merge_arrays( hp\get_array_value( $args, 'attributes', [] ), $attributes );
		}

		return $args;
	}
}
