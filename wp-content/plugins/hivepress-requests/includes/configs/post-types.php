<?php
/**
 * Post types configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'request' => [
		'public'              => true,
		'has_archive'         => true,
		'exclude_from_search' => true,
		'delete_with_user'    => true,
		'supports'            => [ 'title', 'editor' ],
		'menu_icon'           => 'dashicons-format-chat',
		'rewrite'             => [ 'slug' => 'request' ],

		'labels'              => [
			'name'               => hivepress()->translator->get_string( 'requests' ),
			'singular_name'      => hivepress()->translator->get_string( 'request' ),
			'add_new_item'       => hivepress()->translator->get_string( 'add_request' ),
			'add_new'            => esc_html_x( 'Add New', 'request', 'hivepress-requests' ),
			'edit_item'          => esc_html__( 'Edit Request', 'hivepress-requests' ),
			'new_item'           => hivepress()->translator->get_string( 'add_request' ),
			'all_items'          => hivepress()->translator->get_string( 'requests' ),
			'search_items'       => esc_html__( 'Search Requests', 'hivepress-requests' ),
			'not_found'          => esc_html__( 'No requests found.', 'hivepress-requests' ),
			'not_found_in_trash' => esc_html__( 'No requests found.', 'hivepress-requests' ),
		],
	],
];
