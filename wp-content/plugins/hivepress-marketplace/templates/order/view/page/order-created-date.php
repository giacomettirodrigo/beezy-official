<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-order__created-date hp-meta">
	<time>
		<?php
		/* translators: %s: date. */
		printf( esc_html__( 'Placed on %s', 'hivepress-marketplace' ), esc_html( $order->display_created_date() ) );
		?>
	</time>
</div>
