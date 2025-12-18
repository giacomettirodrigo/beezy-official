<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-vendor__balance">
	<strong><?php esc_html_e( 'Balance', 'hivepress-marketplace' ); ?></strong>
	<span>
		<?php
		if ( $vendor->get_balance() ) :
			echo esc_html( $vendor->display_balance() );
		else :
			echo esc_html( hivepress()->woocommerce->format_price( 0 ) );
		endif;
		?>
	</span>
</div>
