<?php
/**
 * Elementor functionality manager.
 * 
 * @package Archiv_Ultra_Extension
 */

namespace Archiv_Ultra_Extension;

defined( 'ABSPATH' ) || die();

use WP_Widget;
use Elementor\Plugin;

class Elementor {
	
	public function __construct() {
		add_action( 'elementor/editor/after_save', [ $this, 'on_after_save' ], 10, 2 );
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue elementor assets.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_style(
			'archiv-menu-admin',
			archiv_ultra_extension()->plugin_url . 'assets/admin-style.css',
			[ 'elementor-select2' ],
			archiv_ultra_extension()->version
		);

		wp_enqueue_script(
			'archiv-menu-admin',
			archiv_ultra_extension()->plugin_url . 'assets/elementor-editor.js',
			[ 'jquery', 'jquery-ui-sortable', 'jquery-elementor-select2' ],
			archiv_ultra_extension()->version
		);

		wp_localize_script(
			'archiv-menu-admin',
			'Archiv',
			[
				'action'   => 'archiv_get_viewing_rooms',
				'endpoint' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'archiv_ajax_nonce' ),
			]
		);
	}

	/**
	 * Run on elementor editor saving.
	 * 
	 * Update viewing rooms slug and title.
	 *
	 * @param int $post_id
	 * @param array $data
	 *
	 * @return void
	 */
	public function on_after_save( $post_id, $data ) {
		if ( empty( $data ) ) {
			$document = Plugin::instance()->documents->get( $post_id );
			$data = $document ? $document->get_elements_data() : [];
		}

		$viewing_rooms = [];

		Plugin::instance()->db->iterate_data( $data, function( $element ) use( &$viewing_rooms ) {
			$widget_type = $this->get_widget_type( $element );

			if ( $widget_type === 'wp-widget-archiv-menu' && isset( $element['settings']['wp'], $element['settings']['wp']['viewing_rooms'] ) ) {
				$widget_viewing_rooms = $element['settings']['wp']['viewing_rooms'];

				if ( ! empty( $widget_viewing_rooms ) && is_array( $widget_viewing_rooms ) ) {
					$viewing_rooms = array_merge( $viewing_rooms, $widget_viewing_rooms );
				}
			}

			return $element;
		} );

		if ( ! empty( $viewing_rooms ) ) {
			$_updated = [];

			foreach ( $viewing_rooms as $viewing_room ) {
				if ( isset( $_updated[ $viewing_room['id'] ] ) && $_updated[ $viewing_room['id'] ] ) {
					continue;
				}

				$args = [
					'ID'         => $viewing_room['id'],
					'meta_input' => [
						'_archiv_menu_index' => $viewing_room['_index']
					]
				];

				$args['post_title'] = ! empty( $viewing_room['title'] ) ? sanitize_text_field( $viewing_room['title'] ) : '';
				$args['post_name'] = ! empty( $viewing_room['slug'] ) ? sanitize_title( $viewing_room['slug'] ) : sanitize_title( $args['post_title'] );

				$updated = wp_update_post( $args );

				if ( $updated ) {
					$_updated[ $viewing_room['id'] ] = true;
				}
			}
		}
	}

	/**
	 * Get the widget type from element data.
	 *
	 * @param array $element
	 *
	 * @return string Widget type.
	 */
	protected function get_widget_type( $element ) {
		if ( empty( $element['widgetType'] ) ) {
			$type = $element['elType'];
		} else {
			$type = $element['widgetType'];
		}

		if ( $type === 'global' && ! empty( $element['templateID'] ) ) {
			$type = $this->get_global_widget_type( $element['templateID'] );
		}

		return $type;
	}

	/**
	 * Get global widget type from global widget template id.
	 *
	 * @param int $template_id
	 *
	 * @return string Global widget type.
	 */
	protected function get_global_widget_type( $template_id ) {
		$template_data = Plugin::instance()->templates_manager->get_template_data( [
			'source'      => 'local',
			'template_id' => $template_id,
		] );

		if ( is_wp_error( $template_data ) ) {
			return '';
		}

		if ( empty( $template_data['content'] ) ) {
			return '';
		}

		$original_widget_type = Plugin::instance()->widgets_manager->get_widget_types( $template_data['content'][0]['widgetType'] );

		return $original_widget_type ? $template_data['content'][0]['widgetType'] : '';
	}
}

return new Elementor();
