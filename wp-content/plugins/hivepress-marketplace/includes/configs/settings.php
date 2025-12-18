<?php
/**
 * Settings configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'listings'     => [
		'sections' => [
			'selling' => [
				'title'  => esc_html__( 'Selling', 'hivepress-marketplace' ),
				'_order' => 100,

				'fields' => [
					'listing_sale_categories'      => [
						'label'       => hivepress()->translator->get_string( 'categories' ),
						'description' => esc_html__( 'Select categories where selling should be available, or leave empty for all categories.', 'hivepress-marketplace' ),
						'type'        => 'select',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_listing_category' ],
						'multiple'    => true,
						'_order'      => 10,
					],

					'listing_allow_price_tiers'    => [
						'label'   => hivepress()->translator->get_string( 'price' ),
						'caption' => esc_html__( 'Allow sellers to set pricing tiers', 'hivepress-marketplace' ),
						'type'    => 'checkbox',
						'_order'  => 20,
					],

					'listing_allow_price_extras'   => [
						'caption' => esc_html__( 'Allow sellers to add price extras', 'hivepress-marketplace' ),
						'type'    => 'checkbox',
						'_order'  => 30,
					],

					'listing_require_price_extras' => [
						'caption' => esc_html__( 'Allow sellers to require price extras', 'hivepress-marketplace' ),
						'type'    => 'checkbox',
						'_parent' => 'listing_allow_price_extras',
						'_order'  => 35,
					],

					'listing_tax_price_extras'     => [
						'caption' => esc_html__( 'Include price extras in the taxable amount', 'hivepress-marketplace' ),
						'type'    => 'checkbox',
						'_parent' => 'listing_allow_price_extras',
						'_order'  => 36,
					],

					'listing_allow_quantity'       => [
						'label'   => esc_html__( 'Quantity', 'hivepress-marketplace' ),
						'caption' => esc_html__( 'Allow sellers to limit quantity', 'hivepress-marketplace' ),
						'type'    => 'checkbox',
						'_order'  => 40,
					],

					'listing_require_quantity'     => [
						'caption' => esc_html__( 'Allow buyers to select quantity', 'hivepress-marketplace' ),
						'type'    => 'checkbox',
						'_order'  => 50,
					],

					'listing_allow_discounts'      => [
						'label'   => esc_html__( 'Discounts', 'hivepress-marketplace' ),
						'caption' => esc_html__( 'Allow sellers to set quantity-based discounts', 'hivepress-marketplace' ),
						'type'    => 'checkbox',
						'_parent' => 'listing_require_quantity',
						'_order'  => 60,
					],

					'listing_allow_purchase_note'  => [
						'label'   => esc_html__( 'Purchase Note', 'hivepress-marketplace' ),
						'caption' => esc_html__( 'Allow sellers to add purchase notes', 'hivepress-marketplace' ),
						'type'    => 'checkbox',
						'_order'  => 70,
					],

					'listing_require_attachment'   => [
						'label'       => esc_html__( 'Attachments', 'hivepress-marketplace' ),
						'caption'     => esc_html__( 'Require file attachments', 'hivepress-marketplace' ),
						'description' => esc_html__( 'Check this option to require sellers to attach files for sale.', 'hivepress-marketplace' ),
						'type'        => 'checkbox',
						'_order'      => 80,
					],

					'listing_attachment_types'     => [
						'label'    => esc_html__( 'Allowed File Types', 'hivepress-marketplace' ),
						'type'     => 'select',
						'options'  => 'mime_types',
						'multiple' => true,
						'_parent'  => 'listing_require_attachment',
						'_order'   => 90,
					],
				],
			],
		],
	],

	'vendors'      => [
		'sections' => [
			'selling' => [
				'title'  => esc_html__( 'Selling', 'hivepress-marketplace' ),
				'_order' => 100,

				'fields' => [
					'vendor_commission_rate' => [
						'label'       => esc_html__( 'Commission Rate', 'hivepress-marketplace' ),
						'description' => esc_html__( 'Set the commission percentage that will be charged on every purchase.', 'hivepress-marketplace' ),
						'type'        => 'number',
						'decimals'    => 2,
						'min_value'   => 0,
						'max_value'   => 100,
						'_order'      => 10,
					],

					'vendor_commission_fee'  => [
						'label'       => esc_html__( 'Commission Fee', 'hivepress-marketplace' ),
						'description' => esc_html__( 'Set a fixed commission fee that will be charged on every purchase.', 'hivepress-marketplace' ),
						'type'        => 'currency',
						'min_value'   => 0,
						'_order'      => 20,
					],

					'vendor_include_taxes'   => [
						'label'   => esc_html__( 'Taxes', 'hivepress-marketplace' ),
						'caption' => esc_html__( 'Include taxes in the balance calculations', 'hivepress-marketplace' ),
						'type'    => 'checkbox',
						'_order'  => 30,
					],
				],
			],
		],
	],

	'users'        => [
		'sections' => [
			'buying' => [
				'title'  => esc_html__( 'Buying', 'hivepress-marketplace' ),
				'_order' => 30,

				'fields' => [
					'user_commission_rate' => [
						'label'       => esc_html__( 'Commission Rate', 'hivepress-marketplace' ),
						'description' => esc_html__( 'Set the commission percentage that will be added to every purchase.', 'hivepress-marketplace' ),
						'type'        => 'number',
						'decimals'    => 2,
						'min_value'   => 0,
						'max_value'   => 100,
						'_order'      => 10,
					],

					'user_commission_fee'  => [
						'label'       => esc_html__( 'Commission Fee', 'hivepress-marketplace' ),
						'description' => esc_html__( 'Set a fixed commission fee that will be added to every purchase.', 'hivepress-marketplace' ),
						'type'        => 'currency',
						'min_value'   => 0,
						'_order'      => 20,
					],
				],
			],
		],
	],

	'orders'       => [
		'title'    => hivepress()->translator->get_string( 'orders' ),
		'_order'   => 130,

		'sections' => [
			'restrictions' => [
				'title'  => esc_html__( 'Restrictions', 'hivepress-marketplace' ),
				'_order' => 10,

				'fields' => [
					'order_message_restriction' => [
						'label'   => hivepress()->translator->get_string( 'messages' ),
						'caption' => esc_html__( 'Restrict messages to buyers', 'hivepress-marketplace' ),
						'type'    => 'checkbox',
						'_order'  => 10,
					],

					'order_review_restriction'  => [
						'label'   => hivepress()->translator->get_string( 'reviews' ),
						'caption' => esc_html__( 'Restrict reviews to buyers', 'hivepress-marketplace' ),
						'type'    => 'checkbox',
						'_order'  => 20,
					],
				],
			],

			'processing'   => [
				'title'  => esc_html__( 'Processing', 'hivepress-marketplace' ),
				'_order' => 20,

				'fields' => [
					'order_allow_requirements' => [
						'label'       => esc_html__( 'Requirements', 'hivepress-marketplace' ),
						'caption'     => esc_html__( 'Allow sellers to add requirements', 'hivepress-marketplace' ),
						'description' => esc_html__( 'Check this option to allow sellers to add custom fields to require specific details for the order.', 'hivepress-marketplace' ),
						'type'        => 'checkbox',
						'_order'      => 10,
					],

					'order_share_details'      => [
						'label'       => hivepress()->translator->get_string( 'details' ),
						'caption'     => esc_html__( 'Share the buyer details with sellers', 'hivepress-marketplace' ),
						'description' => esc_html__( 'Check this option to share personal details, such as the buyer address, email and phone number.', 'hivepress-marketplace' ),
						'type'        => 'checkbox',
						'_order'      => 15,
					],

					'order_require_delivery'   => [
						'label'       => esc_html__( 'Delivery', 'hivepress-marketplace' ),
						'caption'     => esc_html__( 'Require manual delivery', 'hivepress-marketplace' ),
						'description' => esc_html__( 'Check this option to require sellers to mark orders as delivered.', 'hivepress-marketplace' ),
						'type'        => 'checkbox',
						'_order'      => 30,
					],

					'order_limit_revisions'    => [
						'label'       => esc_html__( 'Revisions', 'hivepress-marketplace' ),
						'caption'     => esc_html__( 'Allow sellers to limit revisions', 'hivepress-marketplace' ),
						'description' => esc_html__( 'Check this option to allow sellers to set the maximum number of revisions.', 'hivepress-marketplace' ),
						'type'        => 'checkbox',
						'_parent'     => 'order_require_delivery',
						'_order'      => 35,
					],

					'order_allow_attachment'   => [
						'label'       => esc_html__( 'Attachments', 'hivepress-marketplace' ),
						'caption'     => esc_html__( 'Allow file attachments', 'hivepress-marketplace' ),
						'description' => esc_html__( 'Check this option to allow sellers to attach files to delivered orders.', 'hivepress-marketplace' ),
						'type'        => 'checkbox',
						'_parent'     => 'order_require_delivery',
						'_order'      => 37,
					],

					'order_attachment_types'   => [
						'label'    => esc_html__( 'Allowed File Types', 'hivepress-marketplace' ),
						'type'     => 'select',
						'options'  => 'mime_types',
						'multiple' => true,
						'_parent'  => 'order_allow_attachment',
						'_order'   => 38,
					],

					'order_require_completion' => [
						'label'       => esc_html__( 'Completion', 'hivepress-marketplace' ),
						'caption'     => esc_html__( 'Require manual completion', 'hivepress-marketplace' ),
						'description' => esc_html__( 'Check this option to require buyers to mark orders as completed.', 'hivepress-marketplace' ),
						'type'        => 'checkbox',
						'default'     => true,
						'_order'      => 40,
					],

					'order_completion_period'  => [
						'label'       => esc_html__( 'Completion Period', 'hivepress-marketplace' ),
						'description' => esc_html__( 'Set the number of days after which an order is completed automatically.', 'hivepress-marketplace' ),
						'type'        => 'number',
						'min_value'   => 1,
						'_order'      => 50,
					],
				],
			],

			'refunds'      => [
				'title'  => esc_html__( 'Refunds', 'hivepress-marketplace' ),
				'_order' => 30,

				'fields' => [
					'order_allow_refunds' => [
						'label'       => esc_html__( 'Refunds', 'hivepress-marketplace' ),
						'description' => esc_html__( 'Select an option to allow sellers to refund orders. Refunds may require manual payment processing depending on the payment gateway.', 'hivepress-marketplace' ),
						'type'        => 'select',
						'_order'      => 10,

						'options'     => [
							'partial' => esc_html_x( 'Partial and Full', 'refunds', 'hivepress-marketplace' ),
							'full'    => esc_html_x( 'Full Only', 'refunds', 'hivepress-marketplace' ),
						],
					],

					'order_allow_dispute' => [
						'label'   => esc_html__( 'Disputes', 'hivepress-marketplace' ),
						'caption' => esc_html__( 'Allow disputing orders', 'hivepress-marketplace' ),
						'type'    => 'checkbox',
						'default' => true,
						'_order'  => 20,
					],
				],
			],
		],
	],

	'payouts'      => [
		'title'    => hivepress()->translator->get_string( 'payouts' ),
		'_order'   => 140,

		'sections' => [
			'requests' => [
				'title'  => esc_html__( 'Requests', 'hivepress-marketplace' ),
				'_order' => 10,

				'fields' => [
					'payout_allow_request' => [
						'label'   => esc_html__( 'Requests', 'hivepress-marketplace' ),
						'caption' => esc_html__( 'Allow requesting payouts', 'hivepress-marketplace' ),
						'type'    => 'checkbox',
						'default' => true,
						'_order'  => 10,
					],

					'payout_min_amount'    => [
						'label'     => esc_html__( 'Minimum Amount', 'hivepress-marketplace' ),
						'type'      => 'currency',
						'min_value' => 0.01,
						'_parent'   => 'payout_allow_request',
						'_order'    => 20,
					],

					'payout_system'        => [
						'label'       => esc_html__( 'Payout System', 'hivepress-marketplace' ),
						'description' => esc_html__( 'Select a system used for processing payouts. Each third-party system requires the API credentials that you can set in the Integrations section.', 'hivepress-marketplace' ),
						'placeholder' => esc_html_x( 'Manual', 'system', 'hivepress-marketplace' ),
						'type'        => 'select',
						'_order'      => 30,

						// @todo remove when fully stable.
						'statuses'    => [
							'beta'     => 'beta',
							'optional' => null,
						],

						'options'     => [
							'direct' => esc_html_x( 'Direct', 'system', 'hivepress-marketplace' ),
							'stripe' => 'Stripe Connect',
						],
					],
				],
			],
		],
	],

	'integrations' => [
		'sections' => [
			'stripe' => [
				'title'  => 'Stripe',
				'_order' => 20,

				'fields' => [
					'stripe_secret_key' => [
						'label'      => hivepress()->translator->get_string( 'secret_key' ),
						'type'       => 'text',
						'max_length' => 256,
						'_order'     => 10,
					],
				],
			],
		],
	],
];
