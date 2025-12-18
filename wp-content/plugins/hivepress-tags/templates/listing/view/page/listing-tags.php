<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_tags__id() ) :
	?>
	<div class="hp-listing__tags hp-section tagcloud">
		<?php foreach ( $listing->get_tags() as $tag ) : ?>
			<a href="<?php echo esc_url( hivepress()->router->get_url( 'listing_tag_view_page', [ 'listing_tag_id' => $tag->get_id() ] ) ); ?>" class="tag-cloud-link"><?php echo esc_html( $tag->get_name() ); ?></a>
		<?php endforeach; ?>
	</div>
	<?php
endif;
