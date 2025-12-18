<?php
/**
 * Strings configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'files'              => esc_html__( 'Files', 'hivepress-marketplace' ),
	'get_now'            => esc_html__( 'Get Now', 'hivepress-marketplace' ),
	'contact_buyer'      => esc_html__( 'Contact Buyer', 'hivepress-marketplace' ),
	'contact_seller'     => esc_html__( 'Contact Seller', 'hivepress-marketplace' ),
	'order'              => esc_html__( 'Order', 'hivepress-marketplace' ),
	'orders'             => esc_html__( 'Orders', 'hivepress-marketplace' ),
	'view_order'         => esc_html__( 'View Order', 'hivepress-marketplace' ),
	'payouts'            => esc_html__( 'Payouts', 'hivepress-marketplace' ),
	'listing_sold_out'   => esc_html__( 'Listing Sold Out', 'hivepress-marketplace' ),
	'listing_order_form' => esc_html__( 'Listing Order Form', 'hivepress-marketplace' ),
];
