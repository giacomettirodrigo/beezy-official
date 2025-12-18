<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $order->get_buyer__id() && get_current_user_id() !== $order->get_buyer__id() ) :
	$username = $order->get_buyer__display_name();

	if ( get_option( 'hp_user_enable_display' ) ) :
		$username = '<a href="' . esc_url( hivepress()->router->get_url( 'user_view_page', [ 'username' => $order->get_buyer__username() ] ) ) . '">' . $username . '</a>';
	endif;
	?>
	<div class="hp-order__buyer hp-meta">
		<?php
		/* translators: %s: user name. */
		printf( esc_html__( 'Purchased by %s', 'hivepress-marketplace' ), $username );
		?>
	</div>
	<?php
endif;
