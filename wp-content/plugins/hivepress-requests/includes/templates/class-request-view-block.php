<?php
/**
 * Request view block template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Request view block template class.
 *
 * @class Request_View_Block
 */
class Request_View_Block extends Template {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
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
					'request_container' => [
						'type'       => 'container',
						'tag'        => 'article',
						'_order'     => 10,

						'attributes' => [
							'class' => [ 'hp-listing', 'hp-listing--view-block' ],
						],

						'blocks'     => [
							'request_content' => [
								'type'       => 'container',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-listing__content' ],
								],

								'blocks'     => [
									'request_title' => [
										'type'   => 'part',
										'path'   => 'request/view/block/request-title',
										'_order' => 10,
									],

									'request_details_primary' => [
										'type'       => 'container',
										'optional'   => true,
										'_order'     => 20,

										'attributes' => [
											'class' => [ 'hp-listing__details', 'hp-listing__details--primary' ],
										],

										'blocks'     => [
											'request_category' => [
												'type'   => 'part',
												'path'   => 'request/view/request-categories',
												'_order' => 10,
											],

											'request_created_date' => [
												'type'   => 'part',
												'path'   => 'request/view/request-created-date',
												'_order' => 20,
											],
										],
									],

									'request_attributes_secondary' => [
										'type'   => 'part',
										'path'   => 'request/view/block/request-attributes-secondary',
										'_order' => 30,
									],

									'request_attributes_ternary' => [
										'type'   => 'attributes',
										'model'  => 'request',
										'alias'  => 'listing',
										'area'   => 'view_block_ternary',
										'_order' => 40,
									],
								],
							],

							'request_footer'  => [
								'type'       => 'container',
								'tag'        => 'footer',
								'optional'   => true,
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-listing__footer' ],
								],

								'blocks'     => [
									'request_attributes_primary' => [
										'type'   => 'part',
										'path'   => 'request/view/block/request-attributes-primary',
										'_order' => 10,
									],

									'request_actions_primary'    => [
										'type'       => 'container',
										'optional'   => true,
										'_order'     => 20,

										'attributes' => [
											'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary' ],
										],

										'blocks'     => [
											'offer_make_modal' => [
												'type'   => 'modal',
												'model'  => 'request',
												'title'  => esc_html__( 'Make an Offer', 'hivepress-requests' ),
												'_capability' => 'read',
												'_order' => 5,

												'blocks' => [
													'offer_make_form' => [
														'type'   => 'offer_make_form',
														'_order' => 10,
													],
												],
											],

											'offer_make_link'  => [
												'type'   => 'part',
												'path'   => 'request/view/block/offer-make-link',
												'_order' => 10,
											],
										],
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
