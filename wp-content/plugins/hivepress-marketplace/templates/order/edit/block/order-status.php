<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<td class="hp-order__status hp-status hp-status--<?php echo esc_attr( $order->get_status() ); ?>">
	<span><?php echo esc_html( $order->display_status() ); ?></span>
	<?php if ( get_option( 'hp_order_require_delivery' ) && $order->get_status() === 'wc-processing' && $order->get_delivered_time() ) : ?>
		<i class="hp-order__delivered-badge hp-icon fas fa-check-circle" title="<?php echo esc_attr_x( 'Delivered', 'order', 'hivepress-marketplace' ); ?>"></i>
	<?php endif; ?>
</td>
