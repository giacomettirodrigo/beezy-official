<?php
add_action( 'user_register', function( $user_id ) {
    error_log("Bee→Vendor: user_register hook fired for $user_id");
    bee_create_vendor_if_missing( $user_id );
}, 20 );

add_action( 'wp_login', function( $user_login, $user ) {
    error_log("Bee→Vendor: wp_login hook fired for {$user->ID}");
    bee_create_vendor_if_missing( $user->ID );
}, 20, 2 );

/**
 * Plugin Name: Custom Verification Check
 * Description: Prevents unverified 'requestor' users from accessing the submission page.
 */

function check_and_replace_content_seamlessly() {
    // 1. Hook into template_redirect. This runs just before the template is loaded.
    // NOTE: If the original code was not hooked, it would never run.
    
    // 2. Initial Checks: Logged in and 'requestor' role
    if ( is_user_logged_in() && in_array( 'requestor', (array) wp_get_current_user()->roles ) ) {
        
        $user = wp_get_current_user();
        
        // Ensure you are using the correct meta key (assuming 'hp_doc_verified' is correct now)
        $is_verified = get_user_meta( $user->ID, 'hp_doc_verified', true );
        
        // Define the target path for comparison
        $target_path = '/submit-request/details/';
        
        // Get the current request URI path, cleaning up trailing slashes for reliable comparison
        $current_path = parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
        
        $normalized_current_path = untrailingslashit( strtolower( $current_path ) );
        $normalized_target_path  = untrailingslashit( strtolower( $target_path ) );

        // 3. Conditional Content Override
        
        // We proceed if: (Normalized paths match) AND (User is NOT verified)
        if ( $normalized_current_path === $normalized_target_path && empty( $is_verified ) ) {

            // Set a custom page title
            add_filter( 'pre_get_document_title', function( $title ) {
                return 'Please verify your identity before proceeding';
            }, 9999 );

            // Start output buffering
            ob_start();

            // Load WordPress template parts. This is where the fatal error previously occurred.
            // By running on template_redirect, it should be late enough for HivePress to initialize.
            get_header();

            ?>
            <div class="hp-content" style="text-align: center; max-width: 600px; margin: 50px auto;">
                <div class="hp-content__inner">
                    <div class="hp-content__title">
                        <h1 class="hp-content__title-text">
                            You need to verify your identity before proceeding
                        </h1>
                    </div>
                    <div class="hp-content__message">
                        <p>Please visit your <a href="<?php echo esc_url( home_url( '/account/settings/' ) ); ?>"><b>account settings page</b></a> to upload your proof of identity.</p>
                    </div>
                </div>
            </div>
            <?php

            get_footer();

            // Clear the buffer, echo the output, and halt execution
            $final_output = ob_get_clean();
            echo $final_output;

            // CRITICAL: Halts the main template from loading.
            exit; 
        }
    }
}