<?php
/**
 * Scripts configuration.
 *
 * @package HiveTheme\Configs
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'circle_progress' => [
		'handle'  => 'circle-progress',
		'src'     => hivetheme()->get_url( 'parent' ) . '/assets/js/circle-progress.min.js',
		'version' => hivetheme()->get_version( 'parent' ),
	],

	'parent_frontend' => [
		'handle'  => 'hivetheme-parent-frontend',
		'src'     => hivetheme()->get_url( 'parent' ) . '/assets/js/frontend.min.js',
		'version' => hivetheme()->get_version( 'parent' ),
		'deps'    => [ 'hivetheme-core-frontend', 'circle-progress' ],
	],
];
