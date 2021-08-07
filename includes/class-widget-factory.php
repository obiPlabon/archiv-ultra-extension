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
use Elementor\Group_Control_Typography;

class Widget_Factory extends Widget_WordPress {

	public function get_icon() {
		return 'eicon-navigation-horizontal';
	}

	public function get_keywords() {
		return [ 'nav', 'menu', 'archiv', 'view', 'room' ];
	}

	public function get_categories() {
		return [ 'basic' ];
	}
	
    protected function register_controls() {
		$this->add_control(
			'wp',
			[
				'label'       => __( 'Form', 'archiv' ),
				'type'        => Controls_Manager::WP_WIDGET,
				'widget'      => $this->get_name(),
				'id_base'     => $this->get_widget_instance()->id_base,
			]
		);

		if ( current_user_can( 'manage_options' ) || ! is_user_logged_in() ) {

			$this->start_controls_section(
				'_section_menu_style',
				[
					'label' => __( 'Style', 'archiv' ),
					'tab' => Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_control(
				'_archiv_menu_padding',
				[
					'label' => __( 'Padding', 'archiv' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%', 'rem' ],
					'selectors' => [
						'{{WRAPPER}} .archiv-menu .archiv-menu__item-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name'     => '_archiv_menu_typography',
					'label'    => __( 'Typography', 'archiv' ),
					'selector' => '{{WRAPPER}} .archiv-menu .archiv-menu__item-link',
				]
			);

			$this->add_control(
				'_archiv_menu_color',
				[
					'label' => __( 'Link Color', 'plugin-domain' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .archiv-menu .archiv-menu__item-link' => 'color: {{VALUE}}',
						'{{WRAPPER}} .archiv-menu .archiv-menu__item::after' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'_archiv_menu_color_hover',
				[
					'label' => __( 'Link Hover Color', 'plugin-domain' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .archiv-menu .archiv-menu__item-link:hover, {{WRAPPER}} .archiv-menu .archiv-menu__item-link:focus' => 'color: {{VALUE}}',
						'{{WRAPPER}} .archiv-menu .archiv-menu__item-link.archiv--is-active:hover, {{WRAPPER}} .archiv-menu .archiv-menu__item-link.archiv--is-active:focus' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'_archiv_menu_color_active',
				[
					'label' => __( 'Link Active Color', 'plugin-domain' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .archiv-menu .archiv-menu__item-link.archiv--is-active' => 'color: {{VALUE}}',
					],
				]
			);

			$this->end_controls_section();
		}
	}
}
