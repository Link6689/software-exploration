<?php
/**
 * Compiles design/tokens.json (the design system's tokens) into the CSS
 * custom properties used by the plugin and the theme.
 *
 * Usage: php bin/build-tokens.php
 */

$root   = dirname( __DIR__ );
$tokens = json_decode( file_get_contents( $root . '/design/tokens.json' ), true );
if ( ! $tokens ) {
	fwrite( STDERR, "design/tokens.json is missing or invalid\n" );
	exit( 1 );
}

$themes = array_column( $tokens['color']['themes'], 'id' );
$first  = $themes[0];

$value = function ( $v, $theme ) use ( $first ) {
	if ( ! is_array( $v ) ) {
		return $v;
	}
	return isset( $v[ $theme ] ) ? $v[ $theme ] : $v[ $first ];
};
$block = function ( $theme ) use ( $tokens, $value ) {
	$lines = array();
	foreach ( array( 'color', 'shadow' ) as $family ) {
		foreach ( $tokens[ $family ]['tokens'] as $t ) {
			$v       = $value( $t['value'], $theme );
			$v       = preg_replace( '/^\{([A-Za-z0-9_.-]+)\}$/', 'var(--$1)', $v );
			$lines[] = '  --' . $t['name'] . ': ' . $v . ';';
		}
	}
	return implode( "\n", $lines );
};

$css  = "/* Software Exploration — generated from design/tokens.json by bin/build-tokens.php. Do not edit. */\n\n";
$css .= ":root {\n" . $block( $first ) . "\n}\n\n";
foreach ( array_slice( $themes, 1 ) as $theme ) {
	// Dark palette: Blocksy's colour mode switch, or any wrapper with data-theme="dark".
	$css .= "[data-color-mode=\"$theme\"],\n[data-theme=\"$theme\"],\n.se-theme-$theme {\n" . $block( $theme ) . "\n}\n\n";
}
$static = array();
foreach ( $tokens as $family => $def ) {
	if ( in_array( $family, array( 'name', 'version', 'color', 'shadow', 'type' ), true ) || ! isset( $def['tokens'] ) ) {
		continue;
	}
	foreach ( $def['tokens'] as $t ) {
		$static[] = '  --' . $t['name'] . ': ' . $t['value'] . ';';
	}
}
foreach ( $tokens['type']['families'] as $key => $stack ) {
	$static[] = '  --font-' . $key . ': ' . $stack . ';';
}
$css .= ":root {\n" . implode( "\n", $static ) . "\n}\n";

$out = $root . '/wordpress/plugins/se-reviews/assets/css/tokens.css';
file_put_contents( $out, $css );
echo "Wrote $out\n";
