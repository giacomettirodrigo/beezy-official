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
	'requests' => [
		'title'    => hivepress()->translator->get_string( 'requests' ),
		'_order'   => 120,

		'sections' => [
			'display'    => [
				'title'  => hivepress()->translator->get_string( 'display_noun' ),
				'_order' => 10,

				'fields' => [
					'page_requests'        => [
						'label'       => esc_html__( 'Requests Page', 'hivepress-requests' ),
						'description' => esc_html__( 'Choose a page that displays all requests.', 'hivepress-requests' ),
						'type'        => 'select',
						'options'     => 'posts',
						'option_args' => [ 'post_type' => 'page' ],
						'_order'      => 10,
					],

					'requests_per_page'    => [
						'label'     => esc_html__( 'Requests per Page', 'hivepress-requests' ),
						'type'      => 'number',
						'default'   => 10,
						'min_value' => 1,
						'required'  => true,
						'_order'    => 20,
					],

					'request_title_format' => [
						'label'       => hivepress()->translator->get_string( 'title' ),
						'description' => hivepress()->translator->get_string( 'set_title_format_based_on_attributes' ) . ' ' . sprintf( hivepress()->translator->get_string( 'these_tokens_are_available' ), '%request%' ),
						'type'        => 'text',
						'max_length'  => 256,
						'_order'      => 30,
					],
				],
			],

			'submission' => [
				'title'  => hivepress()->translator->get_string( 'submission' ),
				'_order' => 20,

				'fields' => [
					'request_allow_sending'     => [
						'label'   => hivepress()->translator->get_string( 'submission' ),
						'caption' => esc_html__( 'Allow sending personal requests', 'hivepress-requests' ),
						'type'    => 'checkbox',
						'_order'  => 10,
					],

					'request_enable_moderation' => [
						'label'   => hivepress()->translator->get_string( 'moderation' ),
						'caption' => esc_html__( 'Manually approve new requests', 'hivepress-requests' ),
						'type'    => 'checkbox',
						'default' => true,
						'_order'  => 20,
					],

					'request_notify_vendors'    => [
						'label'   => hivepress()->translator->get_string( 'emails' ),
						'caption' => hivepress()->translator->get_string( 'notify_vendors_about_requests' ),
						'type'    => 'checkbox',
						'_order'  => 30,
					],
				],
			],

			'expiration' => [
				'title'  => hivepress()->translator->get_string( 'expiration' ),
				'_order' => 30,

				'fields' => [
					'request_expiration_period' => [
						'label'       => hivepress()->translator->get_string( 'expiration_period' ),
						'description' => esc_html__( 'Set the number of days after which a request expires.', 'hivepress-requests' ),
						'type'        => 'number',
						'min_value'   => 1,
						'_order'      => 10,
					],
				],
			],
		],
	],

	'offers'   => [
		'title'    => esc_html__( 'Offers', 'hivepress-requests' ),
		'_order'   => 125,

		'sections' => [
			'display'    => [
				'title'  => hivepress()->translator->get_string( 'display_noun' ),
				'_order' => 10,

				'fields' => [
					'offer_display_restriction' => [
						'label'       => hivepress()->translator->get_string( 'display_noun' ),
						'description' => esc_html__( 'Select which users can view the submitted offers.', 'hivepress-requests' ),
						'type'        => 'select',
						'placeholder' => hivepress()->translator->get_string( 'all_users' ),
						'_order'      => 10,

						'options'     => [
							'users'  => hivepress()->translator->get_string( 'registered_users' ),
							'author' => esc_html__( 'Request Author', 'hivepress-requests' ),
						],
					],
				],
			],

			'submission' => [
				'title'  => hivepress()->translator->get_string( 'submission' ),
				'_order' => 20,

				'fields' => [
					'offer_allow_multiple'    => [
						'label'   => hivepress()->translator->get_string( 'submission' ),
						'caption' => esc_html__( 'Allow submitting multiple offers', 'hivepress-requests' ),
						'type'    => 'checkbox',
						'_order'  => 10,
					],

					'offer_enable_moderation' => [
						'label'   => hivepress()->translator->get_string( 'moderation' ),
						'caption' => esc_html__( 'Manually approve new offers', 'hivepress-requests' ),
						'type'    => 'checkbox',
						'default' => true,
						'_order'  => 20,
					],

					'offer_allow_bidding'     => [
						'label'   => esc_html__( 'Bidding', 'hivepress-requests' ),
						'caption' => esc_html__( 'Allow bidding on requests', 'hivepress-requests' ),
						'type'    => 'checkbox',
						'default' => true,
						'_order'  => 30,
					],

					'offer_allow_attachment'  => [
						'label'   => hivepress()->translator->get_string( 'attachments' ),
						'caption' => hivepress()->translator->get_string( 'allow_file_attachments' ),
						'type'    => 'checkbox',
						'_order'  => 40,
					],

					'offer_attachment_types'  => [
						'label'    => hivepress()->translator->get_string( 'allowed_file_types' ),
						'type'     => 'select',
						'options'  => 'mime_types',
						'multiple' => true,
						'_parent'  => 'offer_allow_attachment',
						'_order'   => 50,
					],
				],
			],
		],
	],
];
