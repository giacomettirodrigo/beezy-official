<?php
add_action( 'wp_head', 'hp_hide_submit_button' );

function hp_hide_submit_button() {
    echo '<style>
        .hp-menu--site-header .hp-menu__item--listing-submit {
            display: none !important;
        }
    </style>';
}
