<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<button type="button" class="hp-order__action hp-order__action--request button" data-component="link" data-url="<?php echo esc_url( hivepress()->router->get_url( 'request_view_page', [ 'request_id' => $request->get_id() ] ) ); ?>">
	<?php esc_html_e( 'View Request', 'hivepress-requests' ); ?>
</button>
