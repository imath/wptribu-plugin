<?php
/**
 * Utilities for the wptribu.org site.
 *
 * @package   WPTribu\Plugin
 * @author    imath
 * @license   GPL-2.0+
 * @link      https://imathi.eu
 *
 * @wordpress-plugin
 * Plugin Name:       wpTribu Plugin
 * Plugin URI:        https://github.com/imath/wptribu-plugin
 * Description:       Utilities for the wptribu.org site.
 * Version:           1.0.0
 * Author:            imath
 * Author URI:        https://github.com/imath
 * Text Domain:       wptribu-plugin
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages/
 */

namespace WPTribu\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Class.
 *
 * @since 1.0.0
 */
final class WPTribu_Plugin {
	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {
		$this->inc();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 */
	public static function start() {

		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Load needed files.
	 *
	 * @since 1.0.0
	 */
	private function inc() {
		// Classes.
		spl_autoload_register( array( $this, 'autoload' ) );

		// Functions.
		$inc_path = plugin_dir_path( __FILE__ ) . 'inc/';

		require $inc_path . 'globals.php';
		require $inc_path . 'functions.php';
		require $inc_path . 'registers.php';
	}

	/**
	 * Class Autoload function
	 *
	 * @since  1.0.0
	 *
	 * @param  string $class The class name.
	 */
	public function autoload( $class ) {
		$name = str_replace( '_', '-', strtolower( $class ) );

		if ( false === strpos( $name, 'wptribu-plugin' ) ) {
			return;
		}

		$path = plugin_dir_path( __FILE__ ) . "inc/classes/class-{$name}.php";

		// Sanity check.
		if ( ! file_exists( $path ) ) {
			return;
		}

		require $path;
	}
}

/**
 * Start plugin.
 *
 * @since 1.0.0
 *
 * @return WPTribu_Plugin The main instance of the plugin.
 */
function wptribu_plugin() {
	return WPTribu_Plugin::start();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\wptribu_plugin', 9 );
