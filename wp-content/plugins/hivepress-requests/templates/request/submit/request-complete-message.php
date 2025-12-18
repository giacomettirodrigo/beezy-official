<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<p><?php printf( esc_html__( 'Thank you! Your request "%s" has been submitted and will be reviewed as soon as possible.', 'hivepress-requests' ), $request->get_title() ); ?></p>
<button type="button" class="button" data-component="link" data-url="<?php echo esc_url( hivepress()->router->get_url( 'user_account_page' ) ); ?>"><?php echo esc_html( hivepress()->translator->get_string( 'return_to_my_account' ) ); ?></button>
