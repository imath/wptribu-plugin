<?php
/**
 * Functions about globals
 *
 * @package   WPTribu\Plugin
 * @subpackage \inc\globals
 */

namespace WPTribu\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register plugin globals
 *
 * @since 1.0.0
 */
function register_globals() {
	$wptp = wptribu_plugin();

	$wptp->version  = '1.0.0';
	$wptp->inc_path = plugin_dir_path( __FILE__ );

	$wptp->assets_url         = plugin_dir_url( dirname( __FILE__ ) ) . 'assets';
	$wptp->languages_path     = plugin_dir_path( dirname( __FILE__ ) ) . 'languages';
	$wptp->languages_basepath = trailingslashit( dirname( plugin_basename( dirname( __FILE__ ) ) ) ) . 'languages';

	$wptp->category_sticky = array();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\register_globals', 10 );
