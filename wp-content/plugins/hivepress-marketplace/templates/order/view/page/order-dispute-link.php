<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( get_option( 'hp_order_allow_dispute' ) && $order->get_status() === 'wc-processing' && get_current_user_id() === $order->get_buyer__id() ) :
	?>
	<a href="#order_dispute_modal" class="hp-order__action hp-order__action--dispute hp-link"><i class="hp-icon fas fa-gavel"></i><span><?php esc_html_e( 'Dispute', 'hivepress-marketplace' ); ?></span></a>
	<?php
endif;
