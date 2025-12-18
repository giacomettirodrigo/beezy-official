<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( get_option( 'hp_order_allow_refunds' ) && $order->get_status() === 'wc-processing' && get_current_user_id() === $order->get_seller__id() ) :
	?>
	<a href="#order_refund_modal" class="hp-order__action hp-order__action--refund hp-link"><i class="hp-icon fas fa-undo"></i><span><?php esc_html_e( 'Refund', 'hivepress-marketplace' ); ?></span></a>
	<?php
endif;
