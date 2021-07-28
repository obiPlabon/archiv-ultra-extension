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

use Archiv_Ultra_Extension\Post_Types;
use Archiv_Ultra_Extension\Auto_Post;
use Archiv_Ultra_Extension\Widget;

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

		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
	}

	public function on_plugins_loaded() {
		$this->include_files();
		$this->init_classes();
		$this->register_hooks();
	}

	protected function register_hooks() {
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
	}

	public function register_widgets( $widgets_manager ) {
		include_once $this->plugin_dir . 'includes/class-widget.php';

		$widgets_manager->register_widget_type( new Widget() );
	}

	protected function include_files() {
		include_once $this->plugin_dir . 'includes/class-post-types.php';
		include_once $this->plugin_dir . 'includes/class-auto-post.php';
	}

	protected function init_classes() {
		$this->post_types = new Post_Types();
		$this->auto_post  = new Auto_Post();
	}
}

function archiv_ultra_extension() {
	return Archiv_Ultra_Extension::instance();
}

archiv_ultra_extension();
