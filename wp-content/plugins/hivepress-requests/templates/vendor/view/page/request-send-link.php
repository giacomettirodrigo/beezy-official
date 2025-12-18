<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( get_current_user_id() !== $vendor->get_user__id() ) :
	?>
	<button type="button" class="hp-vendor__action hp-vendor__action--request button button--large" data-component="link" data-url="<?php echo esc_url( hivepress()->router->get_url( 'request_submit_page', [ 'vendor_id' => $vendor->get_id() ] ) ); ?>">
		<?php echo esc_html( hivepress()->translator->get_string( 'send_request' ) ); ?>
	</button>
	<?php
endif;
