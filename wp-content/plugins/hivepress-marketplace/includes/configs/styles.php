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
	'marketplace_backend'  => [
		'handle'  => 'hivepress-marketplace-backend',
		'src'     => hivepress()->get_url( 'marketplace' ) . '/assets/css/backend.min.css',
		'version' => hivepress()->get_version( 'marketplace' ),
		'scope'   => 'backend',
	],

	'marketplace_frontend' => [
		'handle'  => 'hivepress-marketplace-frontend',
		'src'     => hivepress()->get_url( 'marketplace' ) . '/assets/css/frontend.min.css',
		'version' => hivepress()->get_version( 'marketplace' ),
		'scope'   => [ 'frontend', 'editor' ],
	],
];
