<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $request->get_status() === 'publish' && get_current_user_id() === $offer->get_bidder__id() ) :
	?>
	<a href="#offer_delete_modal_<?php echo esc_attr( $offer->get_id() ); ?>" class="hp-offer__action hp-offer__action--delete hp-link"><i class="hp-icon fas fa-times"></i><span><?php esc_html_e( 'Delete Offer', 'hivepress-requests' ); ?></span></a>
	<?php
endif;
