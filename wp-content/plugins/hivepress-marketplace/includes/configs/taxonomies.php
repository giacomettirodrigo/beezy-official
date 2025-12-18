<?php
/**
 * Taxonomies configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'payout_method' => [
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'hierarchical'      => true,
		'post_type'         => [ 'payout' ],

		'labels'            => [
			'name'          => esc_html__( 'Methods', 'hivepress-marketplace' ),
			'singular_name' => esc_html__( 'Method', 'hivepress-marketplace' ),
			'add_new_item'  => esc_html__( 'Add Method', 'hivepress-marketplace' ),
			'edit_item'     => esc_html__( 'Edit Method', 'hivepress-marketplace' ),
			'update_item'   => esc_html__( 'Update Method', 'hivepress-marketplace' ),
			'view_item'     => esc_html__( 'View Method', 'hivepress-marketplace' ),
			'parent_item'   => esc_html__( 'Parent Method', 'hivepress-marketplace' ),
			'search_items'  => esc_html__( 'Search Methods', 'hivepress-marketplace' ),
			'not_found'     => esc_html__( 'No methods found.', 'hivepress-marketplace' ),
		],
	],
];
