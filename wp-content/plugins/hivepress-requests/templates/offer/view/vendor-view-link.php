<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( get_current_user_id() !== $vendor->get_user__id() ) :
	?>
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'vendor_view_page', [ 'vendor_id' => $vendor->get_id() ] ) ); ?>" class="hp-offer__action hp-offer__action--vendor hp-link">
		<i class="hp-icon fas fa-eye"></i>
		<span><?php echo esc_html( hivepress()->translator->get_string( 'view_vendor' ) ); ?></span>
	</a>
	<?php
endif;
