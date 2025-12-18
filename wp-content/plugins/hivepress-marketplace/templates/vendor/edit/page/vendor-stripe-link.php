<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<a href="<?php echo esc_url( $vendor->is_stripe_setup() ? hivepress()->router->get_url( 'vendor_stripe_page' ) : hivepress()->router->get_return_url( 'vendor_stripe_page' ) ); ?>" class="hp-vendor__action hp-vendor__action--stripe hp-link">
	<i class="hp-icon fas fa-wallet"></i>
	<span>
		<?php
		/* translators: %s: payment service. */
		printf( $vendor->is_stripe_setup() ? esc_html__( 'Manage payouts on %s', 'hivepress-marketplace' ) : esc_html__( 'Set up payouts on %s', 'hivepress-marketplace' ), 'Stripe' );
		?>
	</span>
</a>
