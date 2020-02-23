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
 * Registers WordPress objects & assets.
 *
 * @since 1.0.0
 */
function registers() {
	// Stick to category meta.
	register_term_meta(
		'category',
		'_wptribu_category_sticky',
		array(
			'type'              => 'array',
			'description'       => __( 'This metadata contains the list of Post IDs sticked to the top of the category.', 'risk-ops' ),
			'single'            => true,
			'sanitize_callback' => 'wp_parse_id_list',
			'show_in_rest'      => array(
				'name'   => 'wptribu_category_sticky',
				'schema' => array(
					'type'    => 'array',
					'items'   => array(
						'type' => 'integer',
					),
					'context' => array( 'view', 'edit' ),
				),
			),
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\registers', 20 );
