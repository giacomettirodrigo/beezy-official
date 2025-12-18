<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<td class="hp-payout__created-date">
	<time><?php echo esc_html( $payout->display_created_date() ); ?></time>
</td>
