<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$wc_order = wc_get_order( $order->get_id() );

wc_get_template(
	'order/order-details.php',
	[
		'order_id'       => $order->get_id(),
		'show_downloads' => $wc_order->has_downloadable_item() && $wc_order->is_download_permitted(),
	]
);

if ( get_option( 'hp_order_share_details' ) && get_current_user_id() !== $order->get_buyer__id() ) {
	wc_get_template( 'order/order-details-customer.php', [ 'order' => $wc_order ] );
}
