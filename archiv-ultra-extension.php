<?php
/**
 * Plugin Name: Archiv Ultra Extension
 * Description: Your extended Archiv functionality lives here. So please do not uninstall or delete it without being confirmed.
 * Author: obiPlabon
 * Version: 1.0.2
 * Author URI: https://obiplabon.im/
 * License:      GNU General Public License v2 or later
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: archiv-core
 * Domain Path: includes/languages/
 *
 * @package Archiv_Ultra_Extension
 */

defined( 'ABSPATH' ) || die();

final class Archiv_Ultra_Extension {

	public $version = '1.0.2';

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->plugin_url = plugin_dir_url( __FILE__ );
		$this->plugin_dir = plugin_dir_path( __FILE__ );

		$this->version = time();

		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
	}

	public function on_plugins_loaded() {
		$this->include_files();
		$this->register_hooks();

		// add_action( 'elementor/element/before_section_start', function( $widget, $section_id, $args ) {
		// 	file_put_contents( __DIR__ . '/data2.txt', get_class( $widget ) . "\n", FILE_APPEND );
		// }, 10, 3 );

		// add_filter( 'elementor/editor/localize_settings', function ( $config ) {
    
			// 'text-editor' is the Text Editor widget
			// 'heading' is the Heading widget
			// @see get_name method in widget class
			
			// file_put_contents( __DIR__ . '/data.txt', print_r( array_keys( $config['initial_document']['widgets'] ), 1 ), FILE_APPEND );

			// $config['widgets']['icon-box']['categories'] = [ 'common' ];
			
			// return $config;
		// }, 20 );
	}

	protected function register_hooks() {
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ], 15 );

		add_filter( 'widget_types_to_hide_from_legacy_widget_block', [ $this, 'hide_menu_widget_from_lagacy_widget_block' ] );

		add_action( 'widgets_admin_page', [ $this, 'hide_menu_widget_from_classic_widgets_page' ] );
	}

	public function hide_menu_widget_from_lagacy_widget_block( $widget_types ) {
		$widget_types[] = 'archiv-menu';
		return $widget_types;
	}
	
	public function hide_menu_widget_from_classic_widgets_page() {
		global $wp_registered_widgets;

		for ( $i = 1; $i <= 5; $i++ ) {
			if ( isset( $wp_registered_widgets['archiv-menu-' . $i ] ) ) {
				unset( $wp_registered_widgets['archiv-menu-' . $i ] );
			}
		}
	}

	public function register_widgets( $widgets_manager ) {
		// include_once $this->plugin_dir . 'includes/class-widget.php';
		include_once $this->plugin_dir . 'includes/class-xwp-widget.php';

		// global $wp_widget_factory;
		// $page_widget = $wp_widget_factory->widgets['WP_Widget_Pages'];

		// $widgets_manager->register_widget_type( new Widget() );
		$widgets_manager->unregister_widget_type( 'wp-widget-archiv-menu' );

		$widgets_manager->register_widget_type(
			new Archiv_Ultra_Extension\XWP_Widget( [], [
				'widget_name' => '\Archiv_Ultra_Extension\WP_Menu_Widget',
			] )
		);
	}

	protected function include_files() {
		include_once $this->plugin_dir . 'includes/class-post-types.php';
		include_once $this->plugin_dir . 'includes/class-auto-post.php';
		include_once $this->plugin_dir . 'includes/class-wp-menu-widget.php';

		if ( is_user_logged_in() ) {
			include_once $this->plugin_dir . 'includes/class-ajax.php';
		}
	}

}

function archiv_ultra_extension() {
	return Archiv_Ultra_Extension::instance();
}

archiv_ultra_extension();
