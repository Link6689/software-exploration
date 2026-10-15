<?php
/**
 * Affiliate links: /go/<tool-slug>/ redirects to the tool's affiliate URL.
 * One place to change a link, and outbound clicks are easy to count.
 */

defined( 'ABSPATH' ) || exit;

function se_register_go_rewrite() {
	add_rewrite_rule( '^go/([^/]+)/?$', 'index.php?se_go=$matches[1]', 'top' );
}
add_action( 'init', 'se_register_go_rewrite' );

add_filter(
	'query_vars',
	function ( $vars ) {
		$vars[] = 'se_go';
		return $vars;
	}
);

add_action(
	'template_redirect',
	function () {
		$slug = get_query_var( 'se_go' );
		if ( ! $slug ) {
			return;
		}
		$found = get_posts(
			array(
				'post_type'   => 'se_tool',
				'name'        => sanitize_title( $slug ),
				'numberposts' => 1,
				'post_status' => 'publish',
			)
		);
		$url = $found ? get_post_meta( $found[0]->ID, 'se_affiliate_url', true ) : '';
		if ( ! $url ) {
			wp_safe_redirect( se_site_url(), 302 );
			exit;
		}
		do_action( 'se_go_click', $found[0]->ID, $url );
		header( 'X-Robots-Tag: noindex, nofollow', true );
		nocache_headers();
		// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- external affiliate URL set by an editor.
		wp_redirect( esc_url_raw( $url ), 302, 'Software Exploration' );
		exit;
	},
	1
);

add_filter(
	'robots_txt',
	function ( $output ) {
		return $output . "Disallow: /go/\n";
	}
);
