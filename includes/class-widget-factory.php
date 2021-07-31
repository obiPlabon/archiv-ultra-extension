<?php
/**
 * Elementor archiv widget factory class.
 * 
 * @package Archiv_Ultra_Extension
 */

namespace Archiv_Ultra_Extension;

defined( 'ABSPATH' ) || die();

use Elementor\Widget_WordPress;
use Elementor\Controls_Manager;

class Widget_Factory extends Widget_WordPress {
	
    protected function register_controls() {
		$this->add_control(
			'wp',
			[
				'label'       => __( 'Form', 'elementor' ),
				'type'        => Controls_Manager::WP_WIDGET,
				'widget'      => $this->get_name(),
				'id_base'     => $this->get_widget_instance()->id_base,
			]
		);

		$this->start_controls_section(
			'_section_nav_style',
			[
				'label' => __( 'Nav Style', 'plugin-name' ),
                'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->end_controls_section();
	}
}
