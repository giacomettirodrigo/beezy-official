<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( get_current_user_id() === $request->get_user__id() && ( $request->get_status() === 'publish' || $request->get_vendor__id() ) ) :
	?>
	<a href="#request_delete_modal" class="hp-listing__action hp-listing__action--delete hp-link">
		<i class="hp-icon fas fa-times"></i>
		<span><?php esc_html_e( 'Delete Request', 'hivepress-requests' ); ?></span>
	</a>
	<?php
endif;
