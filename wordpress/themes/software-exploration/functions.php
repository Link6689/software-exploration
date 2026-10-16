<?php
/**
 * Software Exploration — Blocksy child theme.
 *
 * Appearance only: fonts, palette mapping, article typography, comments
 * template. Everything content-related is in the se-reviews plugin.
 */

defined( 'ABSPATH' ) || exit;

define( 'SE_THEME_VERSION', '0.1.2' );

function se_theme_asset( $path ) {
	return get_stylesheet_directory_uri() . '/assets/' . $path;
}

/** Front end: fonts, then the theme layer on top of Blocksy and the plugin. */
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'se-fonts', se_theme_asset( 'css/fonts.css' ), array(), SE_THEME_VERSION );
		$deps = array( 'se-fonts' );
		if ( wp_style_is( 'ct-main-styles', 'registered' ) ) {
			$deps[] = 'ct-main-styles';
		}
		if ( wp_style_is( 'se-components', 'registered' ) ) {
			$deps[] = 'se-components';
		}
		wp_enqueue_style( 'se-theme', se_theme_asset( 'css/theme.css' ), $deps, SE_THEME_VERSION );
	},
	20
);

/** Editor iframe: same fonts and article typography as the site. */
add_action(
	'enqueue_block_assets',
	function () {
		if ( is_admin() ) {
			wp_enqueue_style( 'se-fonts', se_theme_asset( 'css/fonts.css' ), array(), SE_THEME_VERSION );
			wp_enqueue_style( 'se-theme', se_theme_asset( 'css/theme.css' ), array( 'se-fonts' ), SE_THEME_VERSION );
		}
	}
);

/** Preload the two faces every page uses above the fold. */
add_action(
	'wp_head',
	function () {
		foreach ( array( 'instrument-sans-latin-wght-normal', 'bricolage-grotesque-latin-wght-normal' ) as $font ) {
			echo '<link rel="preload" href="' . esc_url( se_theme_asset( 'fonts/' . $font . '.woff2' ) ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
		}
	},
	1
);

/** Favicon from the mark until a Site Icon is set in Customizer > Site Identity. */
add_action(
	'wp_head',
	function () {
		if ( ! has_site_icon() ) {
			echo '<link rel="icon" href="' . esc_url( se_theme_asset( 'img/se-mark.svg' ) ) . '" type="image/svg+xml">' . "\n";
		}
	}
);

/** Scope the design system's base styles to the whole site. */
add_filter(
	'body_class',
	function ( $classes ) {
		$classes[] = 'se-site';
		// Posts built from our patterns carry their own author/date line: hide Blocksy's.
		if ( is_singular() && has_block( 'se/meta-line' ) ) {
			$classes[] = 'se-has-meta';
		}
		return $classes;
	}
);

/** Tool of a review: the one picked in the first se/verdict-box block of the post, or 0. */
function se_theme_review_tool_id( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || 'post' !== $post->post_type || ! has_block( 'se/verdict-box', $post ) ) {
		return 0;
	}
	foreach ( parse_blocks( $post->post_content ) as $block ) {
		if ( 'se/verdict-box' === $block['blockName'] && ! empty( $block['attrs']['toolId'] ) ) {
			return (int) $block['attrs']['toolId'];
		}
	}
	return 0;
}

/**
 * Reviews get a right sidebar instead of Blocksy's narrow single-post column.
 * Only when the post keeps "Inherit from customizer": Blocksy returns a
 * per-post choice before this filter runs.
 */
add_filter(
	'blocksy:global:page_structure',
	function ( $structure ) {
		return is_singular( 'post' ) && se_theme_review_tool_id() ? 'type-1' : $structure;
	}
);

/** On a review the sidebar holds the verdict box alone: default widgets would push it out of view. */
add_filter(
	'sidebars_widgets',
	function ( $widgets ) {
		if ( ! is_admin() && is_singular( 'post' ) && se_theme_review_tool_id() ) {
			$widgets['sidebar-1'] = array();
		}
		return $widgets;
	}
);

/** That sidebar: the compact verdict box (score, price, affiliate button), kept in view while scrolling. */
add_action(
	'blocksy:sidebar:start',
	function () {
		$tool_id = is_singular( 'post' ) ? se_theme_review_tool_id() : 0;
		if ( ! $tool_id || ! function_exists( 'se_render_block' ) ) {
			return;
		}
		$html = se_render_block( 'verdict-box', array( 'toolId' => $tool_id, 'compact' => true ) );
		if ( $html ) {
			echo '<div class="se se-block se-b-verdict-box se-sidebar-verdict">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built by the plugin's escaped renderers.
		}
	}
);
