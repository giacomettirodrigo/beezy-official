<?php
/**
 * Order component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order component class.
 *
 * @class Order
 */
final class Order extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {
		if ( get_option( 'hp_order_allow_attachment' ) ) {

			// Add order fields.
			add_filter( 'hivepress/v1/models/order', [ $this, 'add_attachment_field' ] );
			add_filter( 'hivepress/v1/models/order_note', [ $this, 'add_attachment_field' ] );
			add_filter( 'hivepress/v1/forms/order_deliver', [ $this, 'add_attachment_field' ] );
		}

		// Alter templates.
		add_filter( 'hivepress/v1/templates/order_note_view_block/blocks', [ $this, 'alter_order_note_view_blocks' ], 10, 2 );

		parent::__construct( $args );
	}

	/**
	 * Adds attachment field.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function add_attachment_field( $form ) {

		// Get file formats.
		$formats = hivepress()->request->get_context( 'order_attachment_types' );

		if ( ! is_array( $formats ) ) {
			$formats = array_filter( explode( '|', implode( '|', (array) get_option( 'hp_order_attachment_types' ) ) ) );

			hivepress()->request->set_context( 'order_attachment_types', $formats );
		}

		// Add attachment field.
		$form['fields']['attachment'] = [
			'label'     => esc_html__( 'Attachment', 'hivepress-marketplace' ),
			'type'      => 'attachment_upload',
			'formats'   => $formats,
			'protected' => true,
			'_model'    => 'attachment',
			'_external' => true,
			'_order'    => 20,
		];

		return $form;
	}

	/**
	 * Alters order note view blocks.
	 *
	 * @param array  $blocks Block arguments.
	 * @param object $template Template object.
	 * @return array
	 */
	public function alter_order_note_view_blocks( $blocks, $template ) {

		// Get note.
		$note = $template->get_context( 'order_note' );

		if ( $note && get_current_user_id() === $note->get_author__id() ) {

			// Add attributes.
			$blocks = hivepress()->template->merge_blocks(
				$blocks,
				[
					'order_note_container' => [
						'attributes' => [
							'class' => [ 'hp-message--sent' ],
						],
					],
				]
			);
		}

		return $blocks;
	}
}
