<?php
/**
 * Order note model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order note model class.
 *
 * @class Order_Note
 */
class Order_Note extends Comment {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Model meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'alias' => 'order_note',
			],
			$meta
		);

		parent::init( $meta );
	}

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
						'type'       => 'textarea',
						'max_length' => 2056,
						'required'   => true,
						'_alias'     => 'comment_content',
					],

					'added_date'           => [
						'type'   => 'date',
						'format' => 'Y-m-d H:i:s',
						'time'   => true,
						'_alias' => 'comment_date',
					],

					'author'               => [
						'type'     => 'id',
						'required' => true,
						'_alias'   => 'user_id',
						'_model'   => 'user',
					],

					'author__display_name' => [
						'type'       => 'text',
						'max_length' => 256,
						'required'   => true,
						'_alias'     => 'comment_author',
					],

					'author__email'        => [
						'type'     => 'email',
						'required' => true,
						'_alias'   => 'comment_author_email',
					],

					'order'                => [
						'type'     => 'id',
						'required' => true,
						'_alias'   => 'comment_post_ID',
						'_model'   => 'order',
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
	 * @todo Deprecate when attachments are not checked by user.
	 * @return mixed
	 */
	final public function get_user__id() {
		return $this->get_author__id();
	}
}
