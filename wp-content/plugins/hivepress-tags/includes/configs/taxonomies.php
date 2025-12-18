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
	'listing_tags' => [
		'post_type'   => [ 'listing' ],
		'rewrite'     => [ 'slug' => 'listing-tag' ],
		'meta_box_cb' => false,

		'labels'      => [
			'name'          => esc_html__( 'Tags', 'hivepress-tags' ),
			'singular_name' => esc_html__( 'Tag', 'hivepress-tags' ),
			'add_new_item'  => esc_html__( 'Add Tag', 'hivepress-tags' ),
			'edit_item'     => esc_html__( 'Edit Tag', 'hivepress-tags' ),
			'update_item'   => esc_html__( 'Update Tag', 'hivepress-tags' ),
			'view_item'     => esc_html__( 'View Tag', 'hivepress-tags' ),
			'search_items'  => esc_html__( 'Search Tags', 'hivepress-tags' ),
			'not_found'     => esc_html__( 'No tags found.', 'hivepress-tags' ),
		],
	],
];
