<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-offer__image">
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'vendor_view_page', [ 'vendor_id' => $vendor->get_id() ] ) ); ?>">
		<?php echo get_avatar( $offer->get_bidder__id(), 150 ); ?>
	</a>
</div>
