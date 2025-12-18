<?php
/**
 * Request view page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Request view page template class.
 *
 * @class Request_View_Page
 */
class Request_View_Page extends Page_Sidebar_Right {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => hivepress()->translator->get_string( 'request' ),
				'model' => 'request',
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'attributes' => [
					'class' => [ 'hp-listing', 'hp-listing--view-page' ],
				],

				'blocks'     => [
					'page_columns' => [
						'attributes' => [
							'class' => [ 'hp-listing', 'hp-listing--view-page' ],
						],
					],

					'page_content' => [
						'blocks' => [
							'request_title'                => [
								'type'   => 'part',
								'path'   => 'request/view/page/request-title',
								'_label' => hivepress()->translator->get_string( 'title' ),
								'_order' => 10,
							],

							'request_details_primary'      => [
								'type'       => 'container',
								'optional'   => true,
								'_label'     => hivepress()->translator->get_string( 'details' ),
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-listing__details', 'hp-listing__details--primary' ],
								],

								'blocks'     => [
									'request_category'     => [
										'type'   => 'part',
										'path'   => 'request/view/request-categories',
										'_label' => hivepress()->translator->get_string( 'category' ),
										'_order' => 10,
									],

									'request_created_date' => [
										'type'   => 'part',
										'path'   => 'request/view/request-created-date',
										'_label' => hivepress()->translator->get_string( 'date' ),
										'_order' => 20,
									],
								],
							],

							'request_images'               => [
								'type'   => 'part',
								'path'   => 'request/view/page/request-images',
								'_label' => hivepress()->translator->get_string( 'images' ),
								'_order' => 30,
							],

							'request_attributes_secondary' => [
								'type'   => 'part',
								'path'   => 'request/view/page/request-attributes-secondary',
								'_label' => hivepress()->translator->get_string( 'attributes' ) . ' (' . hivepress()->translator->get_string( 'secondary_plural' ) . ')',
								'_order' => 40,
							],

							'request_attributes_ternary'   => [
								'type'      => 'attributes',
								'model'     => 'request',
								'alias'     => 'listing',
								'area'      => 'view_page_ternary',
								'_label'    => hivepress()->translator->get_string( 'attributes' ) . ' (' . hivepress()->translator->get_string( 'ternary_plural' ) . ')',
								'_settings' => [ 'columns' ],
								'_order'    => 50,
							],

							'request_description'          => [
								'type'   => 'part',
								'path'   => 'request/view/page/request-description',
								'_label' => hivepress()->translator->get_string( 'description' ),
								'_order' => 60,
							],

							'offers_container'             => [
								'type'       => 'section',
								'title'      => esc_html__( 'Offers', 'hivepress-requests' ),
								'_order'     => 100,

								'attributes' => [
									'id' => 'offers',
								],

								'blocks'     => [
									'offers' => [
										'type'   => 'offers',
										'_label' => hivepress()->translator->get_string( 'offers' ),
										'_order' => 10,
									],
								],
							],
						],
					],

					'page_sidebar' => [
						'attributes' => [
							'data-component' => 'sticky',
						],

						'blocks'     => [
							'request_attributes_primary' => [
								'type'   => 'part',
								'path'   => 'request/view/page/request-attributes-primary',
								'_label' => hivepress()->translator->get_string( 'attributes' ) . ' (' . hivepress()->translator->get_string( 'primary_plural' ) . ')',
								'_order' => 10,
							],

							'request_actions_primary'    => [
								'type'       => 'container',
								'_label'     => hivepress()->translator->get_string( 'actions' ) . ' (' . hivepress()->translator->get_string( 'primary_plural' ) . ')',
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary', 'hp-widget', 'widget' ],
								],

								'blocks'     => [
									'offer_make_modal'     => [
										'type'        => 'modal',
										'model'       => 'request',
										'title'       => esc_html__( 'Make an Offer', 'hivepress-requests' ),
										'_capability' => 'read',
										'_order'      => 5,

										'blocks'      => [
											'offer_make_form' => [
												'type'   => 'offer_make_form',
												'_order' => 10,
											],
										],
									],

									'request_delete_modal' => [
										'type'        => 'modal',
										'title'       => esc_html__( 'Delete Request', 'hivepress-requests' ),
										'_capability' => 'read',
										'_order'      => 5,

										'blocks'      => [
											'request_delete_form' => [
												'type'   => 'form',
												'form'   => 'request_delete',
												'_order' => 10,
											],
										],
									],

									'offer_make_link'      => [
										'type'   => 'part',
										'path'   => 'request/view/page/offer-make-link',
										'_order' => 10,
									],

									'request_delete_link'  => [
										'type'   => 'part',
										'path'   => 'request/view/page/request-delete-link',
										'_order' => 20,
									],
								],
							],

							'request_user'               => [
								'type'     => 'template',
								'template' => 'user_view_block',
								'_label'   => hivepress()->translator->get_string( 'user' ),
								'_order'   => 30,
							],

							'page_sidebar_widgets'       => [
								'type'   => 'widgets',
								'area'   => 'hp_request_view_sidebar',
								'_label' => hivepress()->translator->get_string( 'widgets' ),
								'_order' => 100,
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
