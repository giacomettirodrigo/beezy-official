<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( empty( $user ) ) :
	?>
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'request_view_page', [ 'request_id' => $request->get_id() ] ) ); ?>" class="hp-offer__request hp-link">
		<i class="hp-icon fas fa-share"></i>
		<span><?php echo esc_html( $request->get_title() ); ?></span>
	</a>
	<?php
endif;
