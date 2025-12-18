<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $order->get_vendor__id() && get_current_user_id() === $order->get_buyer__id() ) :
	?>
	<div class="hp-order__vendor hp-meta">
		<?php
		/* translators: %s: vendor name. */
		printf( esc_html__( 'Purchased from %s', 'hivepress-marketplace' ), '<a href="' . esc_url( hivepress()->router->get_url( 'vendor_view_page', [ 'vendor_id' => $order->get_vendor__id() ] ) ) . '">' . esc_html( $order->get_vendor__name() ) . '</a>' );
		?>
	</div>
	<?php
endif;
