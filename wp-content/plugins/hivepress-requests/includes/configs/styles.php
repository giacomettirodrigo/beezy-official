<?php
/**
 * Styles configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'requests_frontend' => [
		'handle'  => 'hivepress-requests-frontend',
		'src'     => hivepress()->get_url( 'requests' ) . '/assets/css/frontend.min.css',
		'version' => hivepress()->get_version( 'requests' ),
		'scope'   => [ 'frontend', 'editor' ],
	],
];
