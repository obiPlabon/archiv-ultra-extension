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

		$this->version = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? time() : '1.0.2';

		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ], 20 );
	}

	public function on_plugins_loaded() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'elementor_missing_notice' ] );
			return;
		}

		$this->include_files();
		$this->register_hooks();
	}

	public function elementor_missing_notice() {
		if ( current_user_can( 'install_plugins' ) || current_user_can( 'activate_plugins' ) ) {
			$plugin_slug = 'elementor';
			$plugin_file = "{$plugin_slug}/elementor.php";
	
			if ( file_exists( trailingslashit( WP_PLUGIN_DIR ) . $plugin_file ) ) {
				$notice_title = __( 'Activate Elementor', 'archiv' );
				$notice_url   = wp_nonce_url(
					"plugins.php?action=activate&plugin={$plugin_file}&plugin_status=all&paged=1",
					"activate-plugin_{$plugin_file}"
				);
			} else {
				$notice_title = __( 'Install Elementor', 'archiv' );
				$notice_url = wp_nonce_url(
					self_admin_url( "update.php?action=install-plugin&plugin={$plugin_slug}" ),
					"install-plugin_{$plugin_slug}"
				);
			}
	
			$notice = wp_kses_data( sprintf(
				/* translators: 1: Plugin name 2: Elementor 3: Elementor installation link */
				__( '%1$s requires %2$s to be installed and activated to function properly. %3$s', 'archiv' ),
				'<strong>' . __( 'Archiv Ultra Extension', 'archiv' ) . '</strong>',
				'<strong>' . __( 'Elementor', 'archiv' ) . '</strong>',
				'<a href="' . esc_url( $notice_url ) . '">' . $notice_title . '</a>'
			) );
	
			printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $notice );
		}
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
		include_once $this->plugin_dir . 'includes/class-widget-factory.php';

		$widgets_manager->unregister_widget_type( 'wp-widget-archiv-menu' );

		$widgets_manager->register_widget_type(
			new Archiv_Ultra_Extension\Widget_Factory( [], [
				'widget_name' => '\\Archiv_Ultra_Extension\\Archiv_Menu',
			] )
		);
	}

	protected function include_files() {
		include_once $this->plugin_dir . 'includes/class-post-types.php';
		include_once $this->plugin_dir . 'includes/class-auto-post.php';
		include_once $this->plugin_dir . 'includes/class-archiv-menu.php';

		if ( is_user_logged_in() ) {
			include_once $this->plugin_dir . 'includes/class-ajax.php';
			include_once $this->plugin_dir . 'includes/class-elementor.php';
		}
	}

}

function archiv_ultra_extension() {
	return Archiv_Ultra_Extension::instance();
}

archiv_ultra_extension();
