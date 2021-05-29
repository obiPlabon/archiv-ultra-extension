<?php
/**
 * Plugin Name: Archiv Core Plugin
 * Description: Added extended Archiv functionality through this plugin.
 * Author: obiPlabon
 * Version: 1.0.0
 * Author URI: https://obiplabon.im/
 * License:      GNU General Public License v2 or later
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: archiv-core
 * Domain Path: includes/languages/
 *
 * @package Archiv_Core
 */

defined( 'ABSPATH' ) || die();

use Archiv_Core\Post_Types;
use Archiv_Core\Auto_Post;

final class Archiv_Core {

	public $version = '1.0.0';

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
	}

	public function include_files() {
		include_once $this->plugin_dir . 'includes/class-post-types.php';
		include_once $this->plugin_dir . 'includes/class-auto-post.php';
	}

	public function init_classes() {
		$this->post_types = new Post_Types();
		$this->auto_post  = new Auto_Post();
	}
}

function archiv_core() {
	return Archiv_Core::instance();
}

archiv_core();
