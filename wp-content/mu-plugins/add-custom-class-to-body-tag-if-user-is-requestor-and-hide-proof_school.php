<?php
/**
 * FINAL LAST RESORT: Hide field using JavaScript if CSS fails due to inline styles.
 * Targets the 'Proof of school enrollment' field for 'requestor' users.
 */
function hp_hide_school_proof_js() {
    // Only run this for logged-in users who are 'requestor'
    // IMPORTANT: Ensure 'requestor' is the correct role slug.
    if ( is_user_logged_in() && in_array( 'requestor', (array) wp_get_current_user()->roles ) ) {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select the field container using the unique input name (proof_school)
            const fieldContainer = document.querySelector('.hp-form__field.hp-form__field--attachment-upload:has(input[name="proof_school"])');
            
            if (fieldContainer) {
                // Set the display to none, overriding any inline style
                fieldContainer.style.display = 'none';
            }
        });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'hp_hide_school_proof_js', 999 );