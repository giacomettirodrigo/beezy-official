<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $vendor->get_rating() ) :
	?>
	<div class="hp-vendor__rating rating">
		<div class="rating__circle" data-component="circle-rating" data-value="<?php echo esc_attr( $vendor->get_rating() ); ?>"></div>
		<div class="rating__details">
			<span class="rating__value"><?php echo esc_html( number_format_i18n( $vendor->get_rating(), 1 ) ); ?></span>
			<span class="rating__count">(<?php echo esc_html( $vendor->display_rating_count() ); ?>)</span>
		</div>
	</div>
	<?php
endif;
