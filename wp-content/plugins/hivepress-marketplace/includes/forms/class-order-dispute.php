<?php
/**
 * Order dispute form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Order dispute form class.
 *
 * @class Order_Dispute
 */
class Order_Dispute extends Model_Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'   => esc_html__( 'Dispute Order', 'hivepress-marketplace' ),
				'model'   => 'order',
				'captcha' => false,
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'description' => esc_html__( 'Please provide details of your complaint and indicate the desired outcome.', 'hivepress-marketplace' ),
				'message'     => esc_html__( 'Your complaint has been submitted.', 'hivepress-marketplace' ),
				'reset'       => true,

				'fields'      => [
					'details' => [
						'label'      => hivepress()->translator->get_string( 'details' ),
						'type'       => 'textarea',
						'max_length' => 2048,
						'required'   => true,
						'_separate'  => true,
						'_order'     => 10,
					],
				],

				'button'      => [
					'label' => esc_html__( 'Submit Complaint', 'hivepress-marketplace' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps form properties.
	 */
	protected function boot() {

		// Set action.
		if ( $this->model->get_id() ) {
			$this->action = hivepress()->router->get_url(
				'order_dispute_action',
				[
					'order_id' => $this->model->get_id(),
				]
			);
		}

		parent::boot();
	}
}
