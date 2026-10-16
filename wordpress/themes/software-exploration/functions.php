<?php
/**
 * Software Exploration — Blocksy child theme.
 *
 * Appearance only: fonts, palette mapping, article typography, comments
 * template. Everything content-related is in the se-reviews plugin.
 */

defined( 'ABSPATH' ) || exit;

define( 'SE_THEME_VERSION', '0.1.0' );

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
