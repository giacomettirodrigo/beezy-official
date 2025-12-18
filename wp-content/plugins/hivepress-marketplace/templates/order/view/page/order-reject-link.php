<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( get_option( 'hp_order_require_delivery' ) && $order->get_status() === 'wc-processing' && $order->get_delivered_time() && get_current_user_id() === $order->get_buyer__id() ) :
	?>
	<a href="#order_reject_modal" class="hp-order__action hp-order__action--reject hp-link"><i class="hp-icon fas fa-reply"></i><span><?php esc_html_e( 'Reject', 'hivepress-marketplace' ); ?></span></a>
	<?php
endif;
