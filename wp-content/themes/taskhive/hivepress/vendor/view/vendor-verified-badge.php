<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $vendor->is_verified() ) :
	?>
	<div class="hp-vendor__pro-badge">
		<i class="hp-icon fas fa-shield-alt"></i>
		<span><?php esc_html_e( 'Pro', 'taskhive' ); ?></span>
	</div>
	<?php
endif;
