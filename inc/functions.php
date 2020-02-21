<?php
/**
 * General functions
 *
 * @package   WPTribu\Plugin
 * @subpackage \inc\functions
 */

namespace WPTribu\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load text domains.
 *
 * @since 1.0.0
 */
function load_textdomains() {
	$locale  = get_locale();
	$wptp    = wptribu_plugin();
	$domains = array();

	if ( class_exists( 'o2' ) ) {
		$domains['o2'] = 'o2-' . $locale . '.mo';
	}

	if ( class_exists( 'WPorg_Handbook_Init' ) ) {
		$domains['wporg'] = 'handbook-' . $locale . '.mo';
	}

	if ( ! $domains ) {
		return;
	}

	foreach ( $domains as $text_domain => $mofile ) {
		load_textdomain( $text_domain, trailingslashit( $wptp->languages_path ) . $mofile );
	}
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\load_textdomains', 20 );

/**
 * Customize the Handbook Label.
 *
 * @since 1.0.0
 *
 * @param string $label     The label for the Handbook post type.
 * @param string $post_type The post type name.
 * @return string           The label for the Handbook post type.
 */
function handbook_label( $label = '', $post_type = '' ) {
	if ( 'handbook' === $post_type ) {
		$label = __( 'Documentation', 'wptribu-plugin' );
	}

	return $label;
}
add_filter( 'handbook_label', __NAMESPACE__ . '\handbook_label', 10, 2 );
