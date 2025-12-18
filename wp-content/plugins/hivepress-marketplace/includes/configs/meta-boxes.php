<?php
/**
 * Meta boxes configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'payout_settings'        => [
		'title'  => hivepress()->translator->get_string( 'settings' ),
		'screen' => 'payout',

		'fields' => [
			'vendor' => [
				'label'       => hivepress()->translator->get_string( 'vendor' ),
				'type'        => 'select',
				'options'     => 'posts',
				'option_args' => [ 'post_type' => 'hp_vendor' ],
				'source'      => hivepress()->router->get_url( 'vendors_resource' ),
				'required'    => true,
				'_alias'      => 'post_parent',
				'_order'      => 10,
			],

			'amount' => [
				'label'     => esc_html__( 'Amount', 'hivepress-marketplace' ),
				'type'      => 'currency',
				'min_value' => 0.01,
				'required'  => true,
				'_order'    => 20,
			],
		],
	],

	'payout_details'         => [
		'title'  => hivepress()->translator->get_string( 'details' ),
		'screen' => 'payout',

		'fields' => [
			'details' => [
				'type'       => 'textarea',
				'max_length' => 10240,
				'_alias'     => 'post_content',
				'_order'     => 10,
			],
		],
	],

	'payout_method_settings' => [
		'screen' => 'payout_method',

		'fields' => [
			'min_amount' => [
				'label'     => esc_html__( 'Minimum Amount', 'hivepress-marketplace' ),
				'type'      => 'currency',
				'min_value' => 0.01,
				'_order'    => 10,
			],
		],
	],

	'vendor_settings'        => [
		'fields' => [
			'commission_rate' => [
				'label'       => esc_html__( 'Commission Rate', 'hivepress-marketplace' ),
				'description' => esc_html__( 'Set the commission percentage that will be charged on every purchase.', 'hivepress-marketplace' ),
				'type'        => 'number',
				'decimals'    => 2,
				'min_value'   => 0,
				'max_value'   => 100,
				'_order'      => 100,
			],

			'commission_fee'  => [
				'label'       => esc_html__( 'Commission Fee', 'hivepress-marketplace' ),
				'description' => esc_html__( 'Set a fixed commission fee that will be charged on every purchase.', 'hivepress-marketplace' ),
				'type'        => 'currency',
				'min_value'   => 0,
				'_order'      => 110,
			],
		],
	],

	'vendor_statistics'      => [
		'title'  => esc_html__( 'Statistics', 'hivepress-marketplace' ),
		'screen' => 'vendor',

		'blocks' => [
			'vendor_statistics' => [
				'type'   => 'vendor_statistics',
				'_order' => 10,
			],
		],
	],
];
