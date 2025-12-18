<?php
/**
 * Requests view page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Requests view page template class.
 *
 * @class Requests_View_Page
 */
class Requests_View_Page extends Page_Sidebar_Left {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => hivepress()->translator->get_string( 'requests' ),
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
				'blocks' => [
					'page_header'  => [
						'blocks' => [
							'request_search_form' => [
								'type'   => 'request_search_form',
								'_order' => 10,
							],

							'request_filter_link' => [
								'type'    => 'part',
								'path'    => 'request/view/request-filter-link',
								'_parent' => 'request_filter_container',
								'_order'  => 20,
							],
						],
					],

					'page_sidebar' => [
						'attributes' => [
							'data-component' => 'sticky',
						],

						'blocks'     => [
							'request_filter_container' => [
								'type'       => 'container',
								'_label'     => hivepress()->translator->get_string( 'filter_form' ),
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'widget', 'hp-widget', 'hp-widget--listing-filter' ],
								],

								'blocks'     => [
									'request_filter_modal' => [
										'type'       => 'modal',
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-modal--mobile' ],
										],

										'blocks'     => [
											'request_filter_form' => [
												'type'   => 'form',
												'form'   => 'request_filter',
												'_order' => 10,
											],
										],
									],
								],
							],

							'page_sidebar_widgets'     => [
								'type'   => 'widgets',
								'area'   => 'hp_requests_view_sidebar',
								'_label' => hivepress()->translator->get_string( 'widgets' ),
								'_order' => 100,
							],
						],
					],

					'page_topbar'  => [
						'type'     => 'results',
						'optional' => true,
						'_label'   => hivepress()->translator->get_string( 'toolbar' ),

						'blocks'   => [
							'request_count'     => [
								'type'   => 'result_count',
								'_label' => hivepress()->translator->get_string( 'result_count' ),
								'_order' => 10,
							],

							'request_sort_form' => [
								'type'       => 'form',
								'form'       => 'request_sort',
								'_label'     => hivepress()->translator->get_string( 'sort_form' ),
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-form--pivot' ],
								],
							],
						],
					],

					'page_content' => [
						'blocks' => [
							'requests_container' => [
								'type'   => 'results',
								'_order' => 20,

								'blocks' => [
									'requests'           => [
										'type'      => 'requests',
										'_label'    => true,
										'_settings' => [ 'columns' ],
										'_order'    => 10,
									],

									'request_pagination' => [
										'type'   => 'part',
										'path'   => 'page/pagination',
										'_label' => hivepress()->translator->get_string( 'pagination' ),
										'_order' => 20,
									],
								],
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
