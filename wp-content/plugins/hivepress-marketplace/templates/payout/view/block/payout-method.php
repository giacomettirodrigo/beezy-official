<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<td class="hp-payout__method">
	<span><?php echo esc_html( $payout->display_method() ); ?></span>
</td>
