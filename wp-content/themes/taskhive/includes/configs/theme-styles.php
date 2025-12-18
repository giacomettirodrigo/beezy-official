<?php
/**
 * Theme styles configuration.
 *
 * @package HiveTheme\Configs
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	[
		'selector'   => '
			.button,
			button,
			button[type=submit],
			input[type=submit],
			.wp-block-button__link,
			.header-navbar__burger > ul > li > a,
			.post__image .post__date,
			.post--archive .post__author a,
			.pagination > span,
			.pagination .nav-links > a,
			.pagination .nav-links > span,
			.pagination > a,
			.woocommerce nav.woocommerce-pagination ul li a,
			.woocommerce nav.woocommerce-pagination ul li span
		',

		'properties' => [
			[
				'name'      => 'font-family',
				'theme_mod' => 'heading_font',
			],

			[
				'name'      => 'font-weight',
				'theme_mod' => 'heading_font_weight',
			],
		],
	],

	[
		'selector'   => '
			.content-slider--carousel .slick-arrow:hover,
			.content-slider--gallery .slick-arrow:hover,
			.post__author a:hover,
			.hp-listing__image-slider .slick-arrow:hover,
			.hp-listing__images-carousel .slick-arrow:hover
		',

		'properties' => [
			[
				'name'      => 'color',
				'theme_mod' => 'primary_color',
			],
		],
	],

	[
		'selector'   => '
			.content-title::before,
			.content-slider--gallery .slick-dots li div:hover,
			.content-slider--gallery .slick-dots li.slick-active div,
			.site-footer .widget .widget__title::before,
			.post-navbar__end:hover > div::before,
			.post-navbar__start:hover > div::before,
			.hp-page__title::before,
			.hp-section__title::before,
			.hp-listing--view-block .hp-listing__featured-badge,
			.hp-listing-category--view-block:hover::before,
			.hp-vendor--view-block:hover::before
		',

		'properties' => [
			[
				'name'      => 'background-color',
				'theme_mod' => 'primary_color',
			],
		],
	],

	[
		'selector'   => '
			.tagcloud a:hover,
			.wp-block-tag-cloud a:hover,
			.hp-listing__images-carousel .slick-current img,
			.hp-listing--view-block.hp-listing--featured
		',

		'properties' => [
			[
				'name'      => 'border-color',
				'theme_mod' => 'primary_color',
			],
		],
	],

	[
		'selector'   => '
			.hp-listing-package--view-block .hp-listing-package__price,
			.hp-membership-plan--view-block .hp-membership-plan__price
		',

		'properties' => [
			[
				'name'      => 'color',
				'theme_mod' => 'secondary_color',
			],
		],
	],

	[
		'selector'   => '
			.post__categories a,
			.hp-listing--view-block .hp-listing__categories a,
			.hp-listing--view-page .hp-listing__categories a,
			.hp-listing-category--view-page .hp-listing-category__item-count,
			.hp-listing-tags a
		',

		'properties' => [
			[
				'name'      => 'background-color',
				'theme_mod' => 'secondary_color',
			],
		],
	],

	[
		'selector'   => '
			.site-header,
			.content-section
		',

		'properties' => [
			[
				'name'      => 'background-color',
				'theme_mod' => 'secondary_background',
			],
		],
	],
];
