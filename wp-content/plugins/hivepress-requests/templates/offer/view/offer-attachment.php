<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $offer->get_attachment__id() ) :
	?>
	<a href="<?php echo esc_url( $offer->get_attachment__url() ); ?>" target="_blank" class="hp-offer__attachment hp-link">
		<i class="hp-icon fas fa-file-download"></i>
		<span><?php echo esc_html( $offer->get_attachment__name() ); ?></span>
	</a>
	<?php
endif;
