<?php
/**
 * Vendor dashboard page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor dashboard page template class.
 *
 * @class Vendor_Dashboard_Page
 */
class Vendor_Dashboard_Page extends User_Account_Page {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => hivepress()->translator->get_string( 'vendor' ) . ' (' . esc_html__( 'Dashboard', 'hivepress-marketplace' ) . ')',
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
					'page_sidebar' => [
						'blocks' => [
							'vendor_actions_secondary' => [
								'type'       => 'container',
								'optional'   => true,
								'_label'     => hivepress()->translator->get_string( 'actions' ),
								'_order'     => 5,

								'attributes' => [
									'class' => [ 'hp-vendor__actions', 'hp-vendor__actions--secondary', 'hp-widget', 'widget' ],
								],

								'blocks'     => [
									'vendor_balance'       => [
										'type'   => 'part',
										'path'   => 'vendor/edit/page/vendor-balance',
										'_order' => 10,
									],

									'payout_request_modal' => [
										'type'        => 'modal',
										'title'       => esc_html__( 'Request a Payout', 'hivepress-marketplace' ),
										'_capability' => 'edit_posts',
										'_order'      => 5,

										'blocks'      => [
											'payout_request_form' => [
												'type'   => 'form',
												'form'   => 'payout_request',
												'_order' => 10,
											],
										],
									],

									'payout_request_link'  => [
										'type'   => 'part',
										'path'   => 'vendor/edit/page/payout-request-link',
										'_order' => 20,
									],
								],
							],
						],
					],

					'page_content' => [
						'blocks' => [
							'vendor_statistics' => [
								'type'   => 'vendor_statistics',
								'_label' => esc_html__( 'Statistics', 'hivepress-marketplace' ),
								'_order' => 10,
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
