<?php

/**
 * Plugin Name: Custom T&C and Verification
 * Description: Enforces T&C acceptance on first login and tracks verification status.
 */

// Define the current version meta key for easy updating (e.g., 'terms_accepted_v2')
define( 'TERMS_META_KEY', 'terms_accepted_v1' );

function enforce_terms_acceptance_modal() {
    if ( ! is_user_logged_in() || is_admin() ) {
        return;
    }

    $user = wp_get_current_user();
    $accepted_terms = get_user_meta( $user->ID, TERMS_META_KEY, true );
    
    // Define the current page slug to prevent the modal from looping on the T&C page itself
    $current_slug = get_query_var( 'pagename' );
    $terms_slug   = 'terms-and-conditions';
    
    // Check if user has NOT accepted terms AND is NOT on the T&C page
    if ( empty( $accepted_terms ) && $current_slug !== $terms_slug ) {
        
        // Define the redirect URLs based on user roles
        $redirect_url_bee = home_url( '/requests/' );
        $redirect_url_requestor = home_url( '/submit-request/details/' );
        
        // Determine the final landing page on acceptance
        $final_landing_page = in_array( 'bee', (array) $user->roles ) ? $redirect_url_bee : $redirect_url_requestor;

        wp_enqueue_script('jquery');
        
        add_action( 'wp_footer', function() use ($user, $final_landing_page) {
            
            // Get the full HTML content of the T&C page
            $terms_page = get_page_by_path( 'terms-and-conditions' );
            $terms_content = $terms_page ? apply_filters( 'the_content', $terms_page->post_content ) : '<p>Error: Terms and Conditions page not found.</p>';
            
            // Output the Modal HTML structure and the controlling script
            ?>
            <style>
                /* Basic Modal CSS */
                #terms-modal {
                    display: none; position: fixed; z-index: 99999; left: 0; top: 0; 
                    width: 100%; height: 100%; overflow: hidden; background-color: rgb(0,0,0); 
                    background-color: rgba(0,0,0,0.8);
                }
                #terms-modal-content {
                    background-color: #fefefe; margin: 5% auto; padding: 20px; border: 1px solid #888; 
                    width: 90%; max-width: 700px; height: 90%; display: flex; flex-direction: column;
                }
                #terms-header { text-align: center; border-bottom: 1px solid #eee; padding-bottom: 10px; }
                #terms-body { flex-grow: 1; overflow-y: scroll; padding: 15px; border: 1px solid #eee; margin-top: 10px; }
                #terms-footer { 
                    display: flex; justify-content: space-between; padding-top: 10px; border-top: 1px solid #eee; 
                    align-items: center; min-height: 60px; 
                }
                #agree-btn { padding: 10px 20px; cursor: pointer; opacity: 0.6; pointer-events: none; }
                #agree-btn.enabled { opacity: 1; pointer-events: auto; }
                #decline-btn { padding: 10px 20px; cursor: pointer; background-color: #f44336; color: white; border: none; }
            </style>

            <div id="terms-modal">
                <div id="terms-modal-content">
                    <div id="terms-header"><h2>Mandatory Terms and Conditions</h2></div>
                    <div id="terms-body"><?php echo $terms_content; ?></div>
                    <div id="terms-footer">
                        <button id="decline-btn" class="button button--secondary" data-user-id="<?php echo esc_attr($user->ID); ?>">
                            Decline and Logout
                        </button>
                        <span id="scroll-prompt" style="font-size: 0.9em; margin-left: 20px; margin-right: 20px;">
                            Please scroll to the bottom to agree.
                        </span>
                        <button id="agree-btn" class="button button--primary" data-user-id="<?php echo esc_attr($user->ID); ?>">
                            Agree and continue
                        </button>
                    </div>
                </div>
            </div>

            <script>
                jQuery(document).ready(function($) {
                    const $modal = $('#terms-modal');
                    const $body = $('#terms-body');
                    const $agreeBtn = $('#agree-btn');
                    const $declineBtn = $('#decline-btn');
                    const $prompt = $('#scroll-prompt');
                    const userID = $agreeBtn.data('user-id');
                    const redirectURL = '<?php echo esc_url($final_landing_page); ?>';
                    const homeURL = '<?php echo esc_url( home_url('/') ); ?>';
                    
                    // 1. Display the modal immediately and prevent background scrolling
                    $modal.fadeIn(300);
                    $('body').css('overflow', 'hidden');

                    // 2. Scroll detection logic
                    $body.on('scroll', function() {
                        // Check if the user has scrolled near the very bottom (within 20 pixels for safety)
                        if (this.scrollHeight - this.scrollTop <= this.clientHeight + 20) {
                            $agreeBtn.addClass('enabled');
                            $agreeBtn.prop('disabled', false);
                            $prompt.text('Thank you for reading.');
                        }
                    });
                    
                    // Trigger scroll check on load
                    $body.trigger('scroll'); 

                    // 3. Handle ACCEPTANCE (AJAX Submission)
                    $agreeBtn.on('click', function() {
                        if (!$agreeBtn.hasClass('enabled')) { return; }
                        
                        $agreeBtn.prop('disabled', true).text('Saving...');
                        $declineBtn.prop('disabled', true); // Disable decline while saving

                        $.ajax({
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            type: 'POST',
                            data: {
                                action: 'user_terms_action',
                                user_id: userID,
                                type: 'accept', // Send 'accept' type
                                nonce: '<?php echo wp_create_nonce('terms_nonce'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    $modal.fadeOut(300, function() {
                                        $('body').css('overflow', 'auto');
                                        window.location.replace(redirectURL);
                                    });
                                } else {
                                    alert('Error accepting terms: ' + (response.data || 'Unknown error.'));
                                    $agreeBtn.prop('disabled', false).text('Agree and continue');
                                    $declineBtn.prop('disabled', false);
                                }
                            },
                            error: function() {
                                alert('A server error occurred. Please try again.');
                                $agreeBtn.prop('disabled', false).text('Agree and continue');
                                $declineBtn.prop('disabled', false);
                            }
                        });
                    });
                    
                    // 4. Handle DECLINE (AJAX Submission)
                    $declineBtn.on('click', function() {
                        if (!confirm("Are you sure you want to decline the terms? You will be logged out.")) { return; }

                        $declineBtn.prop('disabled', true).text('Logging out...');
                        $agreeBtn.prop('disabled', true); // Disable agree while logging out

                        $.ajax({
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            type: 'POST',
                            data: {
                                action: 'user_terms_action',
                                user_id: userID,
                                type: 'decline', // Send 'decline' type
                                nonce: '<?php echo wp_create_nonce('terms_nonce'); ?>'
                            },
                            success: function(response) {
                                // Always redirect to home on success, regardless of response body
                                window.location.replace(homeURL);
                            },
                            error: function() {
                                // Even on client-side error, try to force redirect/logout
                                window.location.replace(homeURL);
                            }
                        });
                    });
                });
            </script>
            <?php
        }, 99 );
    }
}
add_action( 'wp_head', 'enforce_terms_acceptance_modal' );

// ------------------------------------------------------------------
// AJAX Handler for saving the acceptance/decline status
// ------------------------------------------------------------------

function user_terms_action_ajax_handler() {
    check_ajax_referer( 'terms_nonce', 'nonce' );

    if ( ! is_user_logged_in() || ! isset( $_POST['user_id'], $_POST['type'] ) ) {
        wp_send_json_error( 'Invalid request.' );
        wp_die();
    }

    $user_id = (int) $_POST['user_id'];
    $action_type = sanitize_text_field( $_POST['type'] );
    
    // Only allow actions from the current user
    if ( get_current_user_id() !== $user_id ) {
        wp_send_json_error( 'Permission denied.' );
        wp_die();
    }

    if ( $action_type === 'accept' ) {
        // Set a persistent user meta field with a timestamp
        update_user_meta( $user_id, TERMS_META_KEY, time() );
        wp_send_json_success( ['message' => 'Terms accepted.'] );
        
    } elseif ( $action_type === 'decline' ) {
        // Log user out and send successful log out status
        wp_logout();
        wp_send_json_success( ['message' => 'User logged out.'] );
        
    } else {
        wp_send_json_error( 'Invalid action type.' );
    }

    wp_die();
}

add_action( 'wp_ajax_user_terms_action', 'user_terms_action_ajax_handler' );

// ------------------------------------------------------------------
// Make User Meta Visible in Admin Profile
// ------------------------------------------------------------------

function show_terms_meta_in_admin( $user ) {
    $meta_value = get_user_meta( $user->ID, TERMS_META_KEY, true );
    
    // Convert timestamp to readable date or display status
    if ( $meta_value ) {
        $display_value = date( 'Y-m-d H:i:s', $meta_value ) . ' (v' . substr(TERMS_META_KEY, -1) . ')';
    } else {
        $display_value = 'Not Accepted';
    }
    
    ?>
    <h2>T&C Acceptance Status</h2>
    <table class="form-table">
        <tr>
            <th><label for="<?php echo TERMS_META_KEY; ?>">Terms Accepted</label></th>
            <td>
                <span style="font-weight: bold;"><?php echo esc_html( $display_value ); ?></span>
                <p class="description">
                    This records the date and time the user accepted the mandatory Terms and Conditions version (<?php echo TERMS_META_KEY; ?>).
                </p>
            </td>
        </tr>
    </table>
    <?php
}
