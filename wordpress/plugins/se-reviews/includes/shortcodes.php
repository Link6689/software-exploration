<?php
/**
 * [se_lang_switch] — EN / FR pill linking to the translation of the current
 * page (Polylang). Drop it into an HTML element of the Blocksy header builder.
 * Languages without a translation of this page link to that language's home.
 */

defined( 'ABSPATH' ) || exit;

add_shortcode(
	'se_lang_switch',
	function () {
		if ( ! function_exists( 'pll_the_languages' ) ) {
			return '';
		}
		$langs = pll_the_languages( array( 'raw' => 1, 'hide_if_no_translation' => 0 ) );
		if ( ! $langs ) {
			return '';
		}
		$out = '<nav class="se-lang" aria-label="Language">';
		foreach ( $langs as $l ) {
			$out .= '<a href="' . esc_url( $l['url'] ) . '" hreflang="' . esc_attr( $l['slug'] ) . '" lang="' . esc_attr( $l['slug'] ) . '"'
				. ( $l['current_lang'] ? ' aria-current="true"' : '' ) . '>' . esc_html( strtoupper( $l['slug'] ) ) . '</a>';
		}
		return $out . '</nav>';
	}
);

/**
 * [se_wordmark] — mark + "Software Exploration", linking to the home of the
 * current language. For the logo slot of the Blocksy header builder.
 */
add_shortcode(
	'se_wordmark',
	function () {
		$home = function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
		return '<a class="se-wordmark" href="' . esc_url( $home ) . '" aria-label="Software Exploration">' . se_mark( 30 )
			. '<span class="se-wm-txt">Software <span>Exploration</span></span></a>';
	}
);

/** [se_mark size="28"] — the contour mark, for builders that accept shortcodes. */
add_shortcode(
	'se_mark',
	function ( $atts ) {
		$atts = shortcode_atts( array( 'size' => 28 ), $atts );
		return se_mark( (int) $atts['size'] );
	}
);
