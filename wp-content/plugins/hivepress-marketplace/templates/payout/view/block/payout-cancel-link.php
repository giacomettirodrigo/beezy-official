<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $payout->get_status() === 'pending' ) :
	?>
	<a href="#payout_cancel_modal_<?php echo esc_attr( $payout->get_id() ); ?>" title="<?php esc_attr_e( 'Cancel', 'hivepress-marketplace' ); ?>" class="hp-payout__action hp-payout__action--cancel hp-link"><i class="hp-icon fas fa-times"></i></a>
	<?php
endif;
