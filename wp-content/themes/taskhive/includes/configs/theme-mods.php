<?php
/**
 * Theme mods configuration.
 *
 * @package HiveTheme\Configs
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'colors' => [
		'fields' => [
			'primary_color'        => [
				'default' => '#ffc107',
			],

			'secondary_color'      => [
				'default' => '#3d82f0',
			],

			'secondary_background' => [
				'label'   => esc_html__( 'Background Color', 'taskhive' ),
				'type'    => 'color',
				'default' => '#f7f9fd',
			],

			'background_noise'     => [
				'label'   => esc_html__( 'Enable background noise effect', 'taskhive' ),
				'type'    => 'checkbox',
				'default' => true,
			],
		],
	],

	'fonts'  => [
		'fields' => [
			'heading_font'        => [
				'default' => 'Quicksand',
			],

			'heading_font_weight' => [
				'default' => '500',
			],

			'body_font'           => [
				'default' => 'Nunito Sans',
			],

			'body_font_weight'    => [
				'default' => '400,600',
			],
		],
	],
];
