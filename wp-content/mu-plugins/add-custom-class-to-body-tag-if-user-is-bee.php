<?php
/**
 * Add the user role as a class to the <body> tag.
 */
function add_user_role_to_body_class( $classes ) {
    if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
        if ( !empty( $current_user->roles ) ) {
            // Assumes the primary role is the first one in the array.
            $classes[] = 'user-role-' . $current_user->roles[0];
        }
    }
    return $classes;
}
add_filter( 'body_class', 'add_user_role_to_body_class' );