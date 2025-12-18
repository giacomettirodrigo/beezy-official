<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $request->get_categories__id() ) :
	?>
	<div class="hp-listing__categories hp-listing__category">
		<?php
		foreach ( $request->get_categories() as $category ) :
			?>
			<a href="<?php echo esc_url( hivepress()->router->get_url( 'request_category_view_page', [ 'request_category_id' => $category->get_id() ] ) ); ?>"><?php echo esc_html( $category->get_name() ); ?></a>
		<?php endforeach; ?>
	</div>
	<?php
endif;
