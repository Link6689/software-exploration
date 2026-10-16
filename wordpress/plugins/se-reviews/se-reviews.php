<?php
/**
 * Plugin Name:       Software Exploration Reviews
 * Description:       Tools, scores, review blocks, comparison tables, deals, reader ratings and /go/ affiliate links for software-exploration.com.
 * Version:           0.1.6
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Software Exploration
 * License:           GPL-2.0-or-later
 * Text Domain:       se-reviews
 */

defined( 'ABSPATH' ) || exit;

define( 'SE_REVIEWS_VERSION', '0.1.6' );
define( 'SE_REVIEWS_DIR', plugin_dir_path( __FILE__ ) );
define( 'SE_REVIEWS_URL', plugin_dir_url( __FILE__ ) );

require_once SE_REVIEWS_DIR . 'includes/helpers.php';
require_once SE_REVIEWS_DIR . 'includes/tools.php';
require_once SE_REVIEWS_DIR . 'includes/render.php';
require_once SE_REVIEWS_DIR . 'includes/blocks.php';
require_once SE_REVIEWS_DIR . 'includes/comments.php';
require_once SE_REVIEWS_DIR . 'includes/redirects.php';
require_once SE_REVIEWS_DIR . 'includes/patterns.php';
require_once SE_REVIEWS_DIR . 'includes/shortcodes.php';
require_once SE_REVIEWS_DIR . 'includes/contact.php';
require_once SE_REVIEWS_DIR . 'includes/consent.php';
require_once SE_REVIEWS_DIR . 'includes/robots.php';

/**
 * Styles for blocks, comments and patterns. `enqueue_block_assets` reaches both
 * the front end and the editor iframe, so previews match the site.
 */
function se_reviews_enqueue_styles() {
	wp_enqueue_style( 'se-tokens', SE_REVIEWS_URL . 'assets/css/tokens.css', array(), SE_REVIEWS_VERSION );
	wp_enqueue_style( 'se-components', SE_REVIEWS_URL . 'assets/css/components.css', array( 'se-tokens' ), SE_REVIEWS_VERSION );
}
add_action( 'enqueue_block_assets', 'se_reviews_enqueue_styles' );
add_action( 'wp_enqueue_scripts', 'se_reviews_enqueue_styles' );

function se_reviews_activate() {
	se_register_tool_post_type();
	se_register_go_rewrite();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'se_reviews_activate' );

function se_reviews_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'se_reviews_deactivate' );
