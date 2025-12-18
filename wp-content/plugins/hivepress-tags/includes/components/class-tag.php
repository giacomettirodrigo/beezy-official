<?php
/**
 * Tag component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Tag component class.
 *
 * @class Tag
 */
final class Tag extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Delete empty tags.
		add_action( 'hivepress/v1/events/hourly', [ $this, 'delete_empty_tags' ] );

		// Add listing attributes.
		add_filter( 'hivepress/v1/models/listing/attributes', [ $this, 'add_listing_attributes' ] );

		// Add listing fields.
		add_filter( 'hivepress/v1/models/listing', [ $this, 'add_listing_fields' ] );

		if ( is_admin() ) {

			// Alter tag settings.
			add_filter( 'hivepress/v1/meta_boxes/listing_option_settings', [ $this, 'alter_tag_settings' ] );

			// Set tag defaults.
			add_action( 'hivepress/v1/models/listing_tag/create', [ $this, 'set_tag_defaults' ], 10, 2 );
		} else {

			// Set tags value.
			add_filter( 'hivepress/v1/forms/listing_filter', [ $this, 'set_tags_value' ], 200 );
			add_filter( 'hivepress/v1/forms/listing_sort', [ $this, 'set_tags_value' ], 200 );

			// Alter templates.
			add_filter( 'hivepress/v1/templates/listing_view_page', [ $this, 'alter_listing_view_page' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Deletes empty tags.
	 */
	public function delete_empty_tags() {
		$tags = Models\Listing_Tag::query()->limit( 100 )->get();

		foreach ( $tags as $tag ) {
			if ( ! $tag->is_default() && $tag->get_item_count() === 0 ) {
				$tag->delete();
			}
		}
	}

	/**
	 * Adds listing attributes.
	 *
	 * @param array $attributes Attributes.
	 * @return array
	 */
	public function add_listing_attributes( $attributes ) {
		$attributes['tags'] = [
			'editable'     => true,
			'filterable'   => true,
			'indexable'    => true,

			'edit_field'   => [
				'label'       => esc_html__( 'Tags', 'hivepress-tags' ),
				'type'        => 'tags',
				'options'     => 'terms',
				'max_values'  => 10,
				'_order'      => 190,

				'option_args' => [
					'taxonomy' => 'hp_listing_tags',
					'number'   => 100,
				],
			],

			'search_field' => [
				'label'       => esc_html__( 'Tags', 'hivepress-tags' ),
				'type'        => 'select',
				'options'     => 'terms',
				'multiple'    => true,
				'max_values'  => 10,
				'_order'      => 20,

				'option_args' => [
					'taxonomy'   => 'hp_listing_tags',
					'hide_empty' => true,
					'orderby'    => 'count',
					'order'      => 'DESC',
					'number'     => 100,
				],
			],
		];

		return $attributes;
	}

	/**
	 * Alters tag settings.
	 *
	 * @param array $meta_box Meta box arguments.
	 * @return array
	 */
	public function alter_tag_settings( $meta_box ) {
		$screen = get_current_screen();

		if ( $screen && 'edit-hp_listing_tags' === $screen->id ) {
			$meta_box['fields']['sort_order']['label'] = esc_html__( 'Priority', 'hivepress-tags' );
		}

		return $meta_box;
	}

	/**
	 * Sets tag defaults.
	 *
	 * @param int    $tag_id Tag ID.
	 * @param object $tag Tag object.
	 */
	public function set_tag_defaults( $tag_id, $tag ) {
		$tag->set_default( true )->save_default();
	}

	/**
	 * Adds listing fields.
	 *
	 * @param array $model Model arguments.
	 * @return array
	 */
	public function add_listing_fields( $model ) {
		$model['fields']['tags'] = [
			'label'       => esc_html__( 'Tags', 'hivepress-tags' ),
			'type'        => 'tags',
			'options'     => 'terms',
			'max_values'  => 10,
			'_indexable'  => true,
			'_model'      => 'listing_tag',
			'_relation'   => 'many_to_many',

			'option_args' => [
				'taxonomy' => 'hp_listing_tags',
				'number'   => 100,
			],
		];

		return $model;
	}

	/**
	 * Sets tags value.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function set_tags_value( $form ) {
		if ( is_tax( 'hp_listing_tags' ) ) {
			$form['fields']['tags']['default'] = [ get_queried_object_id() ];
		}

		return $form;
	}

	/**
	 * Alters listing view page.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_listing_view_page( $template ) {
		return hp\merge_trees(
			$template,
			[
				'blocks' => [
					'page_content' => [
						'blocks' => [
							'listing_tags' => [
								'type'   => 'part',
								'path'   => 'listing/view/page/listing-tags',
								'_order' => 70,
							],
						],
					],
				],
			]
		);
	}
}
