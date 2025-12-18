<?php
/**
 * Enqueue parent theme styles.
 */
function taskhive_child_enqueue_styles() {
    // Enqueue the parent theme's main stylesheet
    wp_enqueue_style( 'taskhive-style', get_template_directory_uri() . '/style.css' );
    
    // Enqueue the child theme's stylesheet (it depends on the parent theme's style)
    wp_enqueue_style( 'taskhive-child-style', get_stylesheet_uri(), array( 'taskhive-style' ), wp_get_theme()->get('Version') );
}
add_action( 'wp_enqueue_scripts', 'taskhive_child_enqueue_styles' );

