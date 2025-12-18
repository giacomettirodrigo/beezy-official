<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<td class="hp-payout__amount">
	<span><?php echo esc_html( $payout->display_amount() ); ?></span>
</td>
