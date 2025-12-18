<?php
/**
 * Plugin Name: HivePress Marketplace
 * Description: Allow users to sell listings.
 * Version: 1.3.15
 * Author: HivePress
 * Author URI: https://hivepress.io/
 * Text Domain: hivepress-marketplace
 * Domain Path: /languages/
 *
 * @package HivePress
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Register extension directory.
add_filter(
	'hivepress/v1/extensions',
	function( $extensions ) {
		if ( class_exists( 'woocommerce' ) ) {
			$extensions[] = __DIR__;
		}

		return $extensions;
	}
);

// Add WooCommerce notice.
add_filter(
	'hivepress/v1/admin_notices',
	function( $notices ) {
		if ( ! class_exists( 'woocommerce' ) ) {
			$notices['woocommerce_required'] = [
				'type' => 'error',
				/* translators: 1: plugin name, 2: extension name. */
				'text' => sprintf( esc_html__( 'The %1$s plugin must be installed and activated for %2$s to work.', 'hivepress-marketplace' ), '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>', 'HivePress Marketplace' ),
			];
		}

		return $notices;
	}
);
