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

/**
 * Add a Subscribe to comment feed post action.
 *
 * @since 1.0.0
 *
 * @param array $actions   The o2 post actions.
 * @param integer $post_id The ID or the post.
 * @return array           The o2 post actions.
 */
function get_o2_post_actions( $actions = array(), $post_id = 0 ) {
	$actions[26] = array(
		'action'       => 'follow',
		'href'         => get_post_comments_feed_link( $post_id ),
		'classes'      => array( 'subscribe-to-post-feed', 'subscribe-to-feed' ),
		'rel'          => false,
		'initialState' => 'default'
	);

	return $actions;
}
add_filter( 'o2_filter_post_actions', __NAMESPACE__ . '\get_o2_post_actions', 10, 2 );

/**
 * Register a Subscribe to comment feed post action state.
 *
 * @since 1.0.0
 */
function register_o2_post_action_states() {
	o2_register_post_action_states( 'follow',
		array(
			'default' => array(
				'shortText' => __( 'Subscribe', 'wptribu-plugin' ),
				'title'     => __( 'Subscribe to comment feed', 'wptribu-plugin' ),
				'classes'   => array(),
				'genericon' => 'genericon-rss'
			)
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\register_o2_post_action_states' );

/**
 * Outputs a dynamic Widget to display feeds.
 *
 * @since 1.0.0
 */
function before_o2_sidebar() {
	$secondary_feed = '';
	$queried_object = get_queried_object();

	if ( is_category() ) {
		$secondary_feed = sprintf(
			'<li class="wptribu-category-feed"><a href="%1$s"><span class="genericon genericon-rss"></span> %2$s</a></li>',
			esc_url( get_category_feed_link( $queried_object->term_id ) ),
			/* Translators: %s is the Term name */
			sprintf( esc_html__( '%s’s feed', 'wptribu-plugin' ), esc_html( $queried_object->name ) )
		);
	} elseif ( is_tag() ) {
		$secondary_feed = sprintf(
			'<li class="wptribu-tag-feed"><a href="%1$s"><span class="genericon genericon-rss"></span> %2$s</a></li>',
			esc_url( get_edit_tag_link( $queried_object->term_id ) ),
			/* Translators: %s is the Term name */
			sprintf( esc_html__( '%s’s feed', 'wptribu-plugin' ), esc_html( $queried_object->name ) )
		);
	} elseif ( is_author() ) {
		$secondary_feed = sprintf(
			'<li class="wptribu-author-feed"><a href="%1$s"><span class="genericon genericon-rss"></span> %2$s</a></li>',
			esc_url( get_author_feed_link( $queried_object->ID ) ),
			/* Translators: %s is the Author display name */
			sprintf( esc_html__( '%s’s feed', 'wptribu-plugin' ), esc_html( $queried_object->display_name ) )
		);
	}

	printf(
		'<div id="wptribu-feed" class="box gray widget widget_categories">
			<h4 class="widget-title">%1$s</h4>
			<div class="widget-content">
				<ul>
					<li class="wptribu-blog-feed"><a href="%2$s"><span class="genericon genericon-rss"></span> %3$s</a></li>
					%4$s
				</ul>
			</div>
		</div>',
		esc_html__( 'Subcribe to feed', 'wptribu-plugin' ),
		esc_url( get_feed_link() ),
		esc_html__( 'Site’s feed', 'wptribu-plugin' ),
		$secondary_feed
	);
}
add_action( 'before_o2_sidebar', __NAMESPACE__ . '\before_o2_sidebar' );
