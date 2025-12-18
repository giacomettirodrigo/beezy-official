<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<td class="hp-order__created-date">
	<time><?php echo esc_html( $order->display_created_date() ); ?></time>
</td>
