<?php
/**
 * Pages that must stay out of search engines entirely (legal notice in each
 * language): noindex, nofollow, noarchive, noimageindex, nosnippet, sent as a
 * meta tag and as an X-Robots-Tag header. They stay allowed in robots.txt,
 * otherwise crawlers would never see the noindex.
 */

defined( 'ABSPATH' ) || exit;

function se_is_noindex_page() {
	$slugs = apply_filters( 'se_noindex_slugs', array( 'mentions-legales', 'legal-notice' ) );
	return is_page() && in_array( get_post_field( 'post_name', get_queried_object_id() ), $slugs, true );
}

add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( ! se_is_noindex_page() ) {
			return $robots;
		}
		// Replace everything WordPress or other plugins set (max-image-preview, follow…).
		return array(
			'noindex'      => true,
			'nofollow'     => true,
			'noarchive'    => true,
			'noimageindex' => true,
			'nosnippet'    => true,
		);
	},
	999
);

// Rank Math replaces the wp_robots output with its own tag.
add_filter(
	'rank_math/frontend/robots',
	function ( $robots ) {
		if ( ! se_is_noindex_page() ) {
			return $robots;
		}
		return array(
			'index'        => 'noindex',
			'follow'       => 'nofollow',
			'noarchive'    => 'noarchive',
			'noimageindex' => 'noimageindex',
			'nosnippet'    => 'nosnippet',
		);
	},
	999
);

add_action(
	'template_redirect',
	function () {
		if ( se_is_noindex_page() && ! headers_sent() ) {
			header( 'X-Robots-Tag: noindex, nofollow, noarchive, noimageindex, nosnippet', true );
		}
	}
);
