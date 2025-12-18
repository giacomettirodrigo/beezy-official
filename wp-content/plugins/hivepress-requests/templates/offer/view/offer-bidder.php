<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<h5 class="hp-offer__bidder">
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'vendor_view_page', [ 'vendor_id' => $vendor->get_id() ] ) ); ?>">
		<?php echo esc_html( $offer->get_bidder__display_name() ); ?>
	</a>
</h5>
