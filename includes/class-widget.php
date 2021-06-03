<?php
/**
 * Navigation widget.
 * 
 * @package Archiv_Core
 */

namespace Archiv_Core;

defined( 'ABSPATH' ) || die();

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Plugin;

class Widget extends Widget_Base {

	protected function get_init_settings() {
		parent::get_init_settings();

		$settings = $this->get_data( 'settings' );

		$c = $this->get_controls( '_sample_field' );

		$control_obj = Plugin::$instance->controls_manager->get_control( $c['type'] );
		$control = array_merge_recursive( $control_obj->get_settings(), $c );

		print_r( get_class_methods( $control_obj ) );

		// $settings[ $control['name'] ] = $control_obj->get_value( $control, $settings );

		// if ( isset( $settings['_sample_field'] ) ) {
		// 	$settings['_sample_field'] = rand( 1, 10 );
		// }

		return $settings;
	}

	// protected function get_default_data() {
	// 	$data = parent::get_default_data();

	// 	// $data['_sample_field'] = '';

	// 	if ( isset( $data['settings'], $data['settings']['_sample_field'] ) ) {
	// 		$data['settings']['_sample_field'] = rand( 1, 10 );
	// 	}

	// 	return $data;
	// }

	public function get_name() {
		return 'archiv-viewing-room-nav';
	}

	public function get_title() {
		return __( 'Viewing Room Nav', 'archiv-core' );
	}

	public function get_icon() {
		return 'eicon-nav-menu';
	}

	public function get_keywords() {
		return [ 'viewing', 'room', 'nav', 'archiv', 'menu' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'_viewing_room_nav',
			[
				'label' => __( 'Nav', '@text' )
			]
		);

		$this->add_control(
			'_sample_field',
			[
				'label' => 'Sample Field',
				'type' => Controls_Manager::TEXT
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		echo '<pre>';
		print_r( $settings['_sample_field'] );
		echo '<pre>';
	}
}
