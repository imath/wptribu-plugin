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
 * Checks if a post is sticked to the top of a category.
 *
 * @since 1.0.0
 *
 * @param integer $post_id     The Post ID.
 * @param integer $category_id The category ID.
 * @return boolean             True if the post is sticked to the top of a category.
 *                             False otherwise.
 */
function is_category_sticky( $post_id = 0, $category_id = 0 ) {
	$wptp   = wptribu_plugin();
	$retval = false;

	if ( ! $category_id ) {
		$queried_object = get_queried_object();

		if ( isset( $queried_object->taxonomy ) && 'category' === $queried_object->taxonomy ) {
			$category_id = $queried_object->term_id;
		}
	}

	$category_id = (int) $category_id;
	$post_id     = (int) $post_id;

	if ( $category_id && $post_id ) {
		if ( isset( $wptp->category_sticky[ $category_id ] ) ) {
			$retval = in_array( $post_id, $wptp->category_sticky[ $category_id ], true );
		} else {
			$stickies = (array) get_term_meta( $category_id, '_wptribu_category_sticky', true );
			$retval   = in_array( $post_id, $stickies, true );

			$wptp->category_sticky[ $category_id ] = wp_parse_id_list( $stickies );
		}
	}

	return $retval;
}

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

	if ( is_category() && current_user_can( 'edit_others_posts' ) ) {
		$category_link = wp_nonce_url(
			add_query_arg( 'id', $post_id, get_category_link( get_queried_object() ) ),
			'stick_to_category',
			'_wptribu_nonce'
		);

		$actions[43] = array(
			'action'       => 'sticktocategory',
			'href'         => esc_url( $category_link ),
			'classes'      => array( 'wptribu-category-sticky-link' ),
			'rel'          => false,
			'initialState' => is_category_sticky( $post_id ) ? 'sticky' : 'normal'
		);
	}

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

	o2_register_post_action_states( 'sticktocategory',
		array(
			'normal' => array(
				'shortText' => __( 'Stick to category', 'wptribu-plugin' ),
				'title'     => __( 'Stick the post at the top of this category’s first page', 'wptribu-plugin' ),
				'classes'   => array(),
				'genericon' => 'genericon-sticked',
				'nextState' => 'sticky'
			),
			'sticky' => array(
				'shortText' => __( 'Unstick from category', 'wptribu-plugin' ),
				'title'     => __( 'Unstick the post from the top of this category’s first page', 'wptribu-plugin' ),
				'classes'   => array( 'category-sticky' ),
				'genericon' => 'genericon-sticked',
				'nextState' => 'normal'
			)
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\register_o2_post_action_states' );

function stick_to_category() {
	if ( isset( $_GET['_wptribu_nonce'] ) && isset( $_GET['id'] ) ) {
		\check_admin_referer( 'stick_to_category', '_wptribu_nonce' );

		$post_id     = (int) wp_unslash( $_GET['id'] );
		$category_id = (int) get_queried_object_id();
		$hash        = '';

		if ( $category_id && $post_id ) {
			$post_categories = wp_get_post_categories( $post_id, array( 'fields' => 'ids' ) );

			if ( in_array( $category_id, $post_categories, true ) ) {
				$stickies = (array) get_term_meta( $category_id, '_wptribu_category_sticky', true );

				// Remove.
				if ( in_array( $post_id, $stickies, true ) ) {
					$stickies = array_diff( $stickies, array( $post_id ) );

					// Add.
				} else {
					$stickies[] = $post_id;
					$hash = '#post-' . $post_id;
				}

				// Always update.
				update_term_meta( $category_id, '_wptribu_category_sticky', $stickies );
			}
		}

		$redirect = get_category_link( $category_id );
		if ( $hash ) {
			$redirect = trailingslashit( $redirect ) . $hash;
		}

		wp_safe_redirect( $redirect );
		exit();
	}
}
add_action( 'template_redirect', __NAMESPACE__ . '\stick_to_category' );

function post_classes( $classes = array(), $class = '', $post_id = 0 ) {
	if ( is_category() && is_category_sticky( $post_id ) ) {
		$classes[] = 'category-sticky';
	}

	return $classes;
}
add_filter( 'post_class', __NAMESPACE__ . '\post_classes', 10, 3 );

/**
 * Keep stikies at the top of category's first page.
 *
 * @since 1.0.0
 *
 * @param array    $posts The found posts.
 * @param WP_Query $query The WP Query object.
 * @return array          The found posts.
 */
function prime_category_stickies( $posts = array(), $query = null ) {
	if ( ( ! $query->is_main_query() && ! doing_action( 'o2_read_api' ) ) || ! is_category() || 0 !== (int) get_query_var( 'paged' ) ) {
		return $posts;
	}

	$category_id = get_query_var( 'cat' );
	if ( ! $category_id ) {
		return $posts;
	}

	// Validate the category.
	$category = get_category( $category_id );
	if ( ! isset( $category->taxonomy ) || 'category' !== $category->taxonomy ) {
		return $posts;
	}

	// Get stickies.
	$sticky_ids = (array) get_term_meta( $category_id, '_wptribu_category_sticky', true );
	if ( ! $sticky_ids ) {
		return $posts;
	}

	$sticky_posts = get_posts(
		array(
			'include' => wp_parse_id_list( $sticky_ids )
		)
	);
	wp_reset_postdata();

	if ( $sticky_posts ) {
		$sticky_posts    = array_slice( $sticky_posts, 0, get_query_var( 'posts_per_page' ) );
		$sticky_post_ids = wp_list_pluck( $sticky_posts, 'ID' );
	}

	foreach ( $posts as $key => $post ) {
		if ( in_array( $post->ID, $sticky_post_ids, true ) ) {
			unset( $posts[ $key ] );
		}
	}

	return array_merge( $sticky_posts, $posts );
}
add_filter( 'posts_results', __NAMESPACE__ . '\prime_category_stickies', 10, 2 );

function enqueue_assets() {
	if ( ! is_category() ) {
		return;
	}

	$wptp = wptribu_plugin();

	// Stick to category script
	wp_enqueue_script(
		'wptribu-category-sticky',
		$wptp->assets_url . '/js/posts-collection.js',
		array( 'o2-cocktail' ),
		$wptp->version
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_assets', 20 );

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
