<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $request->get_description() ) :
	?>
	<div class="hp-listing__description">
		<?php echo $request->display_description(); ?>
	</div>
	<?php
endif;
