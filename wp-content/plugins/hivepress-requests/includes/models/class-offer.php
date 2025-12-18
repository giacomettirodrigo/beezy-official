<?php
/**
 * Offer model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Offer model class.
 *
 * @class Offer
 */
class Offer extends Comment {

	/**
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields' => [
					'text'                 => [
						'label'      => hivepress()->translator->get_string( 'details' ),
						'type'       => 'textarea',
						'max_length' => 2048,
						'required'   => true,
						'_alias'     => 'comment_content',
					],

					'created_date'         => [
						'type'   => 'date',
						'format' => 'Y-m-d H:i:s',
						'_alias' => 'comment_date',
					],

					'approved'             => [
						'type'      => 'number',
						'min_value' => 0,
						'max_value' => 1,
						'_alias'    => 'comment_approved',
					],

					'bidder'               => [
						'type'     => 'id',
						'required' => true,
						'_alias'   => 'user_id',
						'_model'   => 'user',
					],

					'bidder__display_name' => [
						'type'       => 'text',
						'max_length' => 256,
						'required'   => true,
						'_alias'     => 'comment_author',
					],

					'bidder__email'        => [
						'type'     => 'email',
						'required' => true,
						'_alias'   => 'comment_author_email',
					],

					'request'              => [
						'type'     => 'id',
						'required' => true,
						'_alias'   => 'comment_post_ID',
						'_model'   => 'request',
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Gets user ID.
	 *
	 * @return mixed
	 */
	final public function get_user__id() {
		return $this->get_bidder__id();
	}
}
