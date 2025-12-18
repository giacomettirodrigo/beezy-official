<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<td class="hp-payout__status hp-status hp-status--<?php echo esc_attr( $payout->get_status() ); ?>">
	<span><?php echo esc_html( $payout->display_status() ); ?></span>
</td>
