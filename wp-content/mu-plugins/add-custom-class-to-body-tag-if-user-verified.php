<?php
/**
 * Add a custom class to the body tag if the user is verified.
 */
function hp_add_verified_body_class( $classes ) {
    if ( is_user_logged_in() ) {
        $user_id = get_current_user_id();
        $is_verified = get_user_meta( $user_id, 'hp_doc_verified', true );

        // If the user has the attribute set to '1' (or a truthy value), add the class.
        if ( $is_verified == 1 ) {
            $classes[] = 'user-doc-verified';
        }
    }
    return $classes;
}
add_filter( 'body_class', 'hp_add_verified_body_class' );