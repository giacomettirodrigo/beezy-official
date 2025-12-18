<?php
/**
 * Order note view block template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order note view block template class.
 *
 * @class Order_Note_View_Block
 */
class Order_Note_View_Block extends Template {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'order_note_container' => [
						'type'       => 'container',
						'_order'     => 10,

						'attributes' => [
							'class' => [ 'hp-message', 'hp-message--view-block' ],
						],

						'blocks'     => [
							'order_note_header'  => [
								'type'       => 'container',
								'tag'        => 'header',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-message__header' ],
								],

								'blocks'     => [
									'order_note_details' => [
										'type'       => 'container',
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-message__details' ],
										],

										'blocks'     => [
											'order_note_author' => [
												'type'   => 'part',
												'path'   => 'order-note/view/order-note-author',
												'_order' => 10,
											],

											'order_note_added_date' => [
												'type'   => 'part',
												'path'   => 'order-note/view/order-note-added-date',
												'_order' => 20,
											],
										],
									],
								],
							],

							'order_note_content' => [
								'type'       => 'container',
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-message__content' ],
								],

								'blocks'     => [
									'order_note_text' => [
										'type'   => 'part',
										'path'   => 'order-note/view/order-note-text',
										'_order' => 10,
									],

									'order_note_attachment' => [
										'type'   => 'part',
										'path'   => 'order-note/view/order-note-attachment',
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
