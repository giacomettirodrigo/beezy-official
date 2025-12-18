<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<a href="#<?php if ( is_user_logged_in() ) : ?>offer_make_modal_<?php echo esc_attr( $request->get_id() ); else : ?>user_login_modal<?php endif; ?>" title="<?php esc_attr_e( 'Make an Offer', 'hivepress-requests' ); ?>" class="hp-listing__action hp-listing__action--offer">
	<i class="hp-icon fas fa-comment"></i>
</a>
