<?php
function change_request_button_for_bee() {
    // 1. Check if the user is logged in AND has the 'bee' capability/role
    if ( is_user_logged_in() && current_user_can( 'bee' ) ) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Selector for the "Post a Request" link
                const $button = $('.hp-menu--site-header .hp-menu__item--request-submit');
                
                if ($button.length) {
                    // 2. Change the URL (href attribute)
                    $button.attr('href', '<?php echo esc_url( home_url( '/requests/' ) ); ?>');
                    
                    // 3. Change the button text (find the span and update its content)
                    $button.find('span').text('Current Requests');

                    // Optional: If you want to change the icon as well
                    // $button.find('.hp-icon').removeClass('fas fa-file-alt').addClass('fas fa-list');
                }
            });
        </script>
        <?php
    }
}