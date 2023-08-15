<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class McwFullPageElementorNavSectionControl extends \Elementor\Group_Control_Base {
	// Fields (derived variable)
	protected static $fields;

	public function __construct( $fields ) {
		self::$fields = $fields;
	}

	public static function get_type() {
		return McwFullPageElementorGlobals::Tag() . '-group-section-nav';
	}

	protected function init_fields() {
		return self::$fields;
	}

	protected function get_default_options() {
		return array(
			'popover' => array(
				'starter_title' => esc_html__( 'Section Navigation', 'mcw-fullpage-elementor' ),
				'starter_name' => McwFullPageElementorGlobals::Tag() . '-group-section-nav-type',
				'starter_value' => 'yes',
			),
		);
	}
}
