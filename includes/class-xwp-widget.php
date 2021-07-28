<?php
/**
 * Navigation widget.
 * 
 * @package Archiv_Core
 */

namespace Archiv_Core;

defined( 'ABSPATH' ) || die();

use Elementor\Widget_WordPress;
use Elementor\Controls_Manager;
// use Elementor\Plugin;

class XWP_Widget extends Widget_WordPress {
    protected function register_controls() {
		$this->add_control(
			'wp',
			[
				'label' => __( 'Form', 'elementor' ),
				'type' => Controls_Manager::WP_WIDGET,
				'widget' => $this->get_name(),
				'id_base' => $this->get_widget_instance()->id_base,
			]
		);

		$this->start_controls_section(
			'content_section2',
			[
				'label' => __( 'Content2', 'plugin-name' ),
                'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title',
			[
				'label' => __( 'Title', 'plugin-name' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter your title', 'plugin-name' ),
			]
		);

		$this->end_controls_section();
	}
}
