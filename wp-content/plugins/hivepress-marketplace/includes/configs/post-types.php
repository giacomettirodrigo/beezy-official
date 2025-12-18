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
	'payout' => [
		'public'    => false,
		'show_ui'   => true,
		'supports'  => [ 'title' ],
		'menu_icon' => 'dashicons-migrate',

		'labels'    => [
			'name'               => hivepress()->translator->get_string( 'payouts' ),
			'singular_name'      => esc_html__( 'Payout', 'hivepress-marketplace' ),
			'add_new_item'       => esc_html__( 'Add Payout', 'hivepress-marketplace' ),
			'add_new'            => esc_html_x( 'Add New', 'payout', 'hivepress-marketplace' ),
			'edit_item'          => esc_html__( 'Edit Payout', 'hivepress-marketplace' ),
			'new_item'           => esc_html__( 'Add Payout', 'hivepress-marketplace' ),
			'all_items'          => hivepress()->translator->get_string( 'payouts' ),
			'search_items'       => esc_html__( 'Search Payouts', 'hivepress-marketplace' ),
			'not_found'          => esc_html__( 'No payouts found.', 'hivepress-marketplace' ),
			'not_found_in_trash' => esc_html__( 'No payouts found.', 'hivepress-marketplace' ),
		],
	],
];
