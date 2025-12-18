<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<time class="hp-offer__created-date hp-meta" datetime="<?php echo esc_attr( $offer->get_created_date() ); ?>">
	<?php echo esc_html( $offer->display_created_date() ); ?>
</time>
