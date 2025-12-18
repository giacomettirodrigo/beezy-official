<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<time class="hp-message__sent-date hp-message__date hp-meta" datetime="<?php echo esc_attr( $order_note->get_added_date() ); ?>"><?php echo esc_html( $order_note->display_added_date() ); ?></time>
