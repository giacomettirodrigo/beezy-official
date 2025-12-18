<?php
/**
 * Strings configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'request'                       => esc_html__( 'Request', 'hivepress-requests' ),
	'requests'                      => esc_html__( 'Requests', 'hivepress-requests' ),
	'request_category'              => esc_html__( 'Request Category', 'hivepress-requests' ),
	'request_attributes'            => esc_html__( 'Request Attributes', 'hivepress-requests' ),
	'offers'                        => esc_html__( 'Offers', 'hivepress-requests' ),
	'only_vendors_can_make_offers'  => esc_html__( 'Only vendors can make offers.', 'hivepress-requests' ),
	'notify_vendors_about_requests' => esc_html__( 'Notify vendors about new requests', 'hivepress-requests' ),
	'post_request'                  => esc_html__( 'Post a Request', 'hivepress-requests' ),
	'send_request'                  => esc_html__( 'Send Request', 'hivepress-requests' ),
	'add_request'                   => esc_html__( 'Add Request', 'hivepress-requests' ),
];
