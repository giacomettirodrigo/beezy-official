<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $request->get_status() === 'publish' || $request->get_vendor__id() ) :
	?>
	<button type="button" class="hp-listing__action hp-listing__action--offer hp-button hp-button--wide button button--large button--primary alt" data-component="link" data-url="#<?php if ( is_user_logged_in() ) : ?>offer_make_modal_<?php echo esc_attr( $request->get_id() ); else : ?>user_login_modal<?php endif; ?>">
		<?php esc_html_e( 'Make an Offer', 'hivepress-requests' ); ?>
	</button>
	<?php
endif;
