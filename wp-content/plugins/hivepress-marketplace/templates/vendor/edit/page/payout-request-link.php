<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( get_option( 'hp_payout_allow_request' ) && $vendor->get_balance() ) :
	?>
	<a href="#payout_request_modal" class="hp-payout__action hp-payout__action--request hp-link"><i class="hp-icon fas fa-arrow-circle-down"></i><span><?php esc_html_e( 'Request a Payout', 'hivepress-marketplace' ); ?></span></a>
	<?php
endif;
