<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_rating() ) :
	?>
	<div class="hp-listing__rating rating">
		<div class="rating__circle" data-component="circle-rating" data-value="<?php echo esc_attr( $listing->get_rating() ); ?>"></div>
		<a href="<?php echo esc_url( hivepress()->router->get_url( 'listing_view_page', [ 'listing_id' => $listing->get_id() ] ) ); ?>#reviews" class="rating__details">
			<span class="rating__value"><?php echo esc_html( number_format_i18n( $listing->get_rating(), 1 ) ); ?></span>
			<span class="rating__count">(<?php echo esc_html( $listing->display_rating_count() ); ?>)</span>
		</a>
	</div>
	<?php
endif;
