<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<h3 class="hp-listing__title">
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'request_view_page', [ 'request_id' => $request->get_id() ] ) ); ?>">
		<?php echo esc_html( $request->get_title() ); ?>
	</a>
</h3>
