<?php
/**
 * Tags field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Tags field class.
 *
 * @class Tags
 */
class Tags extends Select {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Field meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'      => null,
				'filterable' => false,
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			$args,
			[
				'multiple'   => true,

				'attributes' => [
					'data-input'            => 'true',
					'data-max-input-length' => 64,
				],
			]
		);

		parent::__construct( $args );
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( Field::validate() && ! is_null( $this->value ) ) {
			foreach ( $this->value as $value ) {
				if ( is_string( $value ) ) {
					$error = null;

					if ( ! preg_match( '/^[\w\s]+$/u', $value ) ) {
						$error = sprintf( hivepress()->translator->get_string( 'field_contains_invalid_value' ), $this->get_label( true ) );
					} elseif ( mb_strlen( $value ) > 64 ) {
						$error = sprintf( hivepress()->translator->get_string( 'field_longer_than_n_characters' ), $this->get_label( true ), 64 );
					}

					if ( $error ) {
						$this->add_errors( $error );

						break;
					}
				}
			}

			if ( $this->max_values && count( (array) $this->value ) > $this->max_values ) {
				$this->add_errors( sprintf( hivepress()->translator->get_string( 'field_contains_too_many_values' ), $this->get_label( true ) ) );
			}
		}

		return empty( $this->errors );
	}
}
