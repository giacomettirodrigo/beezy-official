<?php
add_action( 'wp_head', 'change_request_button_for_bee' );

add_action( 'template_redirect', function() {
if ( ! is_user_logged_in() ) {
return;
}

$user = wp_get_current_user();
if ( ! $user ) {
return;
}

$bee_role = 'bee'; //
$blocked_path = '/submit-request/';

if ( in_array( $bee_role, (array) $user->roles, true ) && strpos( $_SERVER['REQUEST_URI'], $blocked_path ) !== false ) {
wp_safe_redirect( home_url('/') );
exit;
}
});