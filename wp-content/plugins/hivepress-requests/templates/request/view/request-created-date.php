<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<time class="hp-listing__created-date hp-meta" datetime="<?php echo esc_attr( $request->get_created_date() ); ?>">
	<?php printf( hivepress()->translator->get_string( 'added_on_date' ), $request->display_created_date() ); ?>
</time>
