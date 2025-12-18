<?php
/**
 * Payout model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Payout model class.
 *
 * @class Payout
 */
class Payout extends Post {

	/**
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields' => [
					'title'        => [
						'type'       => 'text',
						'max_length' => 256,
						'_alias'     => 'post_title',
					],

					'details'      => [
						'label'      => hivepress()->translator->get_string( 'details' ),
						'type'       => 'textarea',
						'max_length' => 10240,
						'_alias'     => 'post_content',
					],

					'status'       => [
						'type'    => 'select',
						'_alias'  => 'post_status',

						'options' => [
							'publish'    => esc_html_x( 'Completed', 'payout', 'hivepress-marketplace' ),
							'future'     => '',
							'draft'      => esc_html_x( 'Failed', 'payout', 'hivepress-marketplace' ),
							'pending'    => esc_html_x( 'Pending', 'payout', 'hivepress-marketplace' ),
							'private'    => '',
							'trash'      => '',
							'auto-draft' => '',
							'inherit'    => '',
						],
					],

					'created_date' => [
						'type'   => 'date',
						'format' => 'Y-m-d H:i:s',
						'_alias' => 'post_date',
					],

					'amount'       => [
						'label'     => esc_html__( 'Amount', 'hivepress-marketplace' ),
						'type'      => 'currency',
						'min_value' => 0.01,
						'required'  => true,
						'_external' => true,
					],

					'method'       => [
						'label'       => esc_html__( 'Method', 'hivepress-marketplace' ),
						'type'        => 'select',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_payout_method' ],
						'required'    => true,
						'_model'      => 'payout_method',
						'_relation'   => 'many_to_many',
					],

					'user'         => [
						'type'     => 'id',
						'required' => true,
						'_model'   => 'user',
						'_alias'   => 'post_author',
					],

					'vendor'       => [
						'type'     => 'id',
						'required' => true,
						'_model'   => 'vendor',
						'_alias'   => 'post_parent',
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
