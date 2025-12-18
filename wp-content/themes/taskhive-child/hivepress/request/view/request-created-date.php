<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$request = isset( $args['request'] ) ? $args['request'] : $request;
if ( ! is_object( $request ) ) {
    return;
}

$request_id = $request->get_id();
$request_detail_url = get_permalink( $request_id );

// Define target URLs based on user status
if ( is_user_logged_in() ) {
    // If logged in, redirect to the request detail page
    $target_url = $request_detail_url;
    $link_class = 'hp-block-link hp-logged-in';
} else {
    // If NOT logged in, redirect to the login URL
    $target_url = 'https://beezybees.com/account/login/';
    $link_class = 'hp-block-link hp-logged-out';
}

// Ensure the block is ONLY clickable on the list pages (not the detail page)
$is_clickable_list_view = ( is_front_page() || is_post_type_archive( 'hp_request' ) ) && ! is_singular( 'hp_request' ); 


// --- 1. Data Retrieval (Date, Time, City, Category) ---
// (Retrieval logic is unchanged)
$task_datetime_raw = get_post_meta( $request_id, 'hp_task_date', true );
$formatted_date_time = '';
if ( $task_datetime_raw ) {
    $timestamp = strtotime( $task_datetime_raw );
    $formatted_date = date( 'd/m/Y', $timestamp );
    $formatted_time = date( 'H:i', $timestamp );
    $formatted_date_time = $formatted_date . ' | ' . $formatted_time;
}

$city_value = '';
$terms = get_the_terms( $request_id, 'hp_request_task_city' ); 
if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
    $city_value = reset( $terms )->name; 
}

$category_name = '';
$cat_terms = get_the_terms( $request_id, 'hp_request_category' ); 
if ( ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ) {
    $category_name = reset( $cat_terms )->name;
}

// --- 2. Output SEPARATE ELEMENTS ---

// CONDITIONALLY OUTPUT THE CLICKABLE BLOCK LINK
if ( $is_clickable_list_view ) :
?>
<a href="<?php echo esc_url( $target_url ); ?>" class="<?php echo esc_attr( $link_class ); ?>"></a>
<?php endif; ?>


<?php if ( $formatted_date_time ) : ?>
<time class="hp-listing__task-date hp-meta hp-term" datetime="<?php echo esc_attr( $task_datetime_raw ); ?>">
	<?php 
	printf( esc_html__( 'Task Date: %s', 'taskhive' ), $formatted_date_time ); 
	?>
</time>

<?php 
// OUTPUT 2: CITY
if ( ! empty( $city_value ) ) : 
?>
<div class="hp-listing__task-city hp-meta hp-term">
    <?php echo esc_html( $city_value ); ?>
</div>
<?php endif; ?>

<?php
// OUTPUT 3: CATEGORY (Non-Clickable)
if ( ! empty( $category_name ) ) : 
?>
<span class="hp-custom-category-static hp-term">
    <?php echo esc_html( $category_name ); ?>
</span>
<?php endif; ?>

<?php else: 
// Fallback to original created date
if ( $is_clickable_list_view ) :
?>
<a href="<?php echo esc_url( $target_url ); ?>" class="<?php echo esc_attr( $link_class ); ?>"></a>
<?php endif; ?>

<time class="hp-listing__created-date hp-meta hp-term" datetime="<?php echo esc_attr( $request->get_created_date() ); ?>">
	<?php printf( hivepress()->translator->get_string( 'added_on_date' ), $request->display_created_date() ); ?>
</time>
<?php endif; ?>