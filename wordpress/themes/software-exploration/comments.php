<?php
/**
 * Comments: reader reviews from the se-reviews plugin (rating summary, list,
 * form). Falls back to Blocksy's own template if the plugin is off.
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}

if ( function_exists( 'se_render_reader_reviews' ) ) {
	se_render_reader_reviews();
	return;
}

$se_parent_comments = get_template_directory() . '/comments.php';
if ( file_exists( $se_parent_comments ) ) {
	require $se_parent_comments;
}
