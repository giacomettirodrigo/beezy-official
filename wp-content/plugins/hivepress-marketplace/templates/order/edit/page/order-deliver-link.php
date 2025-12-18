<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( get_option( 'hp_order_require_delivery' ) && $order->get_status() === 'wc-processing' && ! $order->get_delivered_time() && get_current_user_id() === $order->get_seller__id() ) :
	?>
	<a href="#order_deliver_modal" class="hp-order__action hp-order__action--deliver hp-link"><i class="hp-icon fas fa-share"></i><span><?php esc_html_e( 'Deliver', 'hivepress-marketplace' ); ?></span></a>
	<?php
endif;
