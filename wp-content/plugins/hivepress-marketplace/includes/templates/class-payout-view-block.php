<?php
/**
 * Payout view block template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Payout view block template class.
 *
 * @class Payout_View_Block
 */
class Payout_View_Block extends Template {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'payout_container' => [
						'type'       => 'container',
						'tag'        => 'tr',
						'_order'     => 10,

						'attributes' => [
							'class' => [ 'hp-payout', 'hp-payout--view-block' ],
						],

						'blocks'     => [
							'payout_number'          => [
								'type'   => 'part',
								'path'   => 'payout/view/block/payout-number',
								'_order' => 10,
							],

							'payout_amount'          => [
								'type'   => 'part',
								'path'   => 'payout/view/block/payout-amount',
								'_order' => 20,
							],

							'payout_method'          => [
								'type'   => 'part',
								'path'   => 'payout/view/block/payout-method',
								'_order' => 30,
							],

							'payout_status'          => [
								'type'   => 'part',
								'path'   => 'payout/view/block/payout-status',
								'_order' => 40,
							],

							'payout_created_date'    => [
								'type'   => 'part',
								'path'   => 'payout/view/block/payout-created-date',
								'_order' => 50,
							],

							'payout_actions_primary' => [
								'type'       => 'container',
								'tag'        => 'td',
								'_order'     => 60,

								'attributes' => [
									'class' => [ 'hp-payout__actions', 'hp-payout__actions--primary' ],
								],

								'blocks'     => [
									'payout_cancel_modal' => [
										'type'        => 'modal',
										'model'       => 'payout',
										'title'       => esc_html__( 'Cancel Payout', 'hivepress-marketplace' ),
										'_capability' => 'edit_posts',
										'_order'      => 5,

										'blocks'      => [
											'payout_cancel_form' => [
												'type'   => 'form',
												'form'   => 'payout_cancel',
												'_order' => 10,
											],
										],
									],

									'payout_cancel_link'  => [
										'type'   => 'part',
										'path'   => 'payout/view/block/payout-cancel-link',
										'_order' => 10,
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
