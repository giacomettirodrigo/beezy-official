<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<td class="hp-order__number">
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'order_edit_page', [ 'order_id' => $order->get_id() ] ) ); ?>" class="hp-link">
		<i class="hp-icon fas fa-edit"></i>
		<span>#<?php echo esc_html( $order->get_id() ); ?></span>
	</a>
</td>
