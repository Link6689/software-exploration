<?php
/**
 * Reader reviews: native WordPress comments with a 1–5 star rating and
 * "how long have you used it". Rating fields appear on reviews only (posts
 * containing a Verdict box). Our own /10 score is shown beside the reader
 * average, never blended into it.
 */

defined( 'ABSPATH' ) || exit;

function se_uses_options() {
	return array(
		'lt1m'  => array( 'Less than a month', 'Moins d’un mois' ),
		'1-6m'  => array( '1–6 months', '1 à 6 mois' ),
		'6-12m' => array( '6–12 months', '6 à 12 mois' ),
		'gt1y'  => array( 'Over a year', 'Plus d’un an' ),
	);
}

/** Is this post a review (has a Verdict box)? */
function se_is_review( $post = null ) {
	$post = get_post( $post );
	return $post && has_block( 'se/verdict-box', $post );
}

/** The tool reviewed in this post: first non-compact Verdict box. */
function se_post_tool( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}
	$find = function ( $blocks ) use ( &$find ) {
		foreach ( $blocks as $b ) {
			if ( 'se/verdict-box' === $b['blockName'] && ! empty( $b['attrs']['toolId'] ) && empty( $b['attrs']['compact'] ) ) {
				return (int) $b['attrs']['toolId'];
			}
			if ( ! empty( $b['innerBlocks'] ) ) {
				$id = $find( $b['innerBlocks'] );
				if ( $id ) {
					return $id;
				}
			}
		}
		return 0;
	};
	$id = $find( parse_blocks( $post->post_content ) );
	return $id ? se_tool( $id ) : null;
}

/**
 * @return array{count:int,average:float,distribution:int[]} distribution is 5★ → 1★.
 */
function se_reader_stats( $post_id ) {
	$ids  = get_comments(
		array(
			'post_id'  => $post_id,
			'status'   => 'approve',
			'meta_key' => 'se_rating', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'fields'   => 'ids',
			'parent'   => 0,
		)
	);
	$dist = array( 0, 0, 0, 0, 0 );
	$sum  = 0;
	foreach ( $ids as $cid ) {
		$r = (int) get_comment_meta( $cid, 'se_rating', true );
		if ( $r >= 1 && $r <= 5 ) {
			$dist[ 5 - $r ]++;
			$sum += $r;
		}
	}
	$count = array_sum( $dist );
	return array( 'count' => $count, 'average' => $count ? $sum / $count : 0.0, 'distribution' => $dist );
}

/* ---------- form fields ---------- */

function se_rating_fields_html() {
	$star  = '<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M10 2l2.4 5 5.4.6-4 3.7 1.1 5.3L10 14l-4.9 2.6 1.1-5.3-4-3.7 5.4-.6z"/></svg>';
	$stars = '';
	for ( $i = 5; $i >= 1; $i-- ) {
		$stars .= '<input type="radio" name="se_rating" id="se-rating-' . $i . '" value="' . $i . '"><label for="se-rating-' . $i . '" title="' . $i . ' / 5"><span class="se-sr">' . $i . ' / 5</span>' . $star . '</label>';
	}
	$opts = '<option value="">—</option>';
	foreach ( se_uses_options() as $key => $labels ) {
		$opts .= '<option value="' . esc_attr( $key ) . '">' . esc_html( se_l( $labels[0], $labels[1] ) ) . '</option>';
	}
	return '<fieldset class="se-field se-rating-field"><legend class="se-ui">' . esc_html( se_t( 'your_rating' ) ) . '</legend><div class="se-star-radio">' . $stars . '</div></fieldset>'
		. '<p class="se-field"><label class="se-ui" for="se-uses">' . esc_html( se_t( 'used_for' ) ) . '</label><select class="se-input" name="se_uses" id="se-uses">' . $opts . '</select></p>';
}

// At the top of the form, so themes that reorder the fields (Blocksy does) keep the rating first.
add_action(
	'comment_form_top',
	function () {
		if ( se_is_review() ) {
			echo se_rating_fields_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts.
		}
	}
);

add_action(
	'comment_post',
	function ( $comment_id ) {
		// Nonce-free like core comments; values are whitelisted.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$comment = get_comment( $comment_id );
		if ( ! $comment || $comment->comment_parent || ! se_is_review( $comment->comment_post_ID ) ) {
			return;
		}
		$rating = isset( $_POST['se_rating'] ) ? (int) $_POST['se_rating'] : 0;
		if ( $rating >= 1 && $rating <= 5 ) {
			add_comment_meta( $comment_id, 'se_rating', $rating, true );
		}
		$uses = isset( $_POST['se_uses'] ) ? sanitize_key( wp_unslash( $_POST['se_uses'] ) ) : '';
		if ( isset( se_uses_options()[ $uses ] ) ) {
			add_comment_meta( $comment_id, 'se_uses', $uses, true );
		}
		// phpcs:enable
	}
);

/* ---------- display ---------- */

function se_comment_callback( $comment, $args, $depth ) {
	$team    = $comment->user_id && user_can( $comment->user_id, 'edit_posts' );
	$rating  = (int) get_comment_meta( $comment->comment_ID, 'se_rating', true );
	$uses    = get_comment_meta( $comment->comment_ID, 'se_uses', true );
	$options = se_uses_options();
	$name    = get_comment_author( $comment );

	echo '<li id="comment-' . (int) $comment->comment_ID . '" class="' . esc_attr( se_cx( 'se-cmt-li', $depth > 1 ? 'is-reply' : '' ) ) . '">';
	echo '<article class="' . esc_attr( se_cx( 'se-cmt', $depth > 1 ? 'se-cmt-reply' : '' ) ) . '">';
	echo $team ? se_mark( 36 ) : '<span class="se-avatar se-avatar-cmt" aria-hidden="true">' . esc_html( se_initials( $name ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<div class="se-cmt-body"><div class="se-row" style="gap:8px"><b class="se-ui">' . esc_html( $name ) . '</b>';
	if ( $team ) {
		echo se_render_badge( se_t( 'team' ), 'brand' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( $uses && isset( $options[ $uses ] ) ) {
		echo se_render_badge( se_t( 'uses' ) . ' · ' . se_l( $options[ $uses ][0], $options[ $uses ][1] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '<time class="se-small" datetime="' . esc_attr( get_comment_date( 'c', $comment ) ) . '">' . esc_html( se_fmt_date( get_comment_date( 'Y-m-d', $comment ) ) ) . '</time></div>';
	if ( $rating ) {
		echo se_render_stars( $rating, null, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( '0' === $comment->comment_approved ) {
		echo '<p class="se-small"><em>' . esc_html( se_t( 'awaiting' ) ) . '</em></p>';
	}
	echo '<div class="se-cmt-text">';
	comment_text( $comment );
	echo '</div>';
	comment_reply_link(
		array_merge(
			$args,
			array(
				'depth'      => $depth,
				'max_depth'  => $args['max_depth'],
				'reply_text' => se_t( 'reply' ),
				'before'     => '<div class="se-cmt-actions">',
				'after'      => '</div>',
			)
		),
		$comment
	);
	echo '</div></article>';
}

function se_comment_end_callback() {
	echo '</li>';
}

/** The whole reader-reviews section: summary, list and form. Called from the theme's comments.php. */
function se_render_reader_reviews() {
	$post = get_post();
	if ( ! $post || post_password_required( $post ) ) {
		return;
	}
	$review = se_is_review( $post );
	$stats  = se_reader_stats( $post->ID );
	$tool   = $review ? se_post_tool( $post ) : null;

	echo '<section id="comments" class="se se-reader-reviews se-stack">';
	echo '<h2 class="se-h2">' . esc_html( $review ? se_t( 'readers' ) : se_l( 'Comments', 'Commentaires' ) ) . '</h2>';

	if ( $review ) {
		$max  = max( 1, max( $stats['distribution'] ) );
		$bars = '';
		foreach ( $stats['distribution'] as $i => $n ) {
			$bars .= '<div class="se-bar se-bar-rr"><span class="se-small">' . ( 5 - $i ) . ' ★</span><span class="se-bar-track"><span class="se-bar-fill se-bar-star" style="width:' . round( $n / $max * 100 ) . '%"></span></span><span class="se-small" style="text-align:right">' . (int) $n . '</span></div>';
		}
		echo '<div class="se-card se-rr-sum"><div class="se-rr-avg">';
		if ( $stats['count'] ) {
			echo '<div class="se-price">' . esc_html( se_fmt_score( $stats['average'] ) ) . '<small>/5</small></div>' . se_render_stars( $stats['average'], null, false ) . '<div class="se-small">' . esc_html( se_t( 'based_on', (string) $stats['count'] ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo '<p class="se-small">' . esc_html( se_t( 'no_ratings' ) ) . '</p>';
		}
		echo '</div><div class="se-bars">' . $bars . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $tool ) {
			echo '<div class="se-rr-ours"><span class="se-label se-muted">' . esc_html( se_t( 'our_score' ) ) . '</span>' . se_render_score( $tool['score'] ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
	}

	if ( have_comments() ) {
		echo '<ol class="se-cmts">';
		wp_list_comments(
			array(
				'callback'     => 'se_comment_callback',
				'end-callback' => 'se_comment_end_callback',
				'style'        => 'ol',
				'max_depth'    => 3,
			)
		);
		echo '</ol>';
		the_comments_pagination();
	}

	if ( comments_open( $post ) ) {
		comment_form(
			array(
				'class_form'           => 'se-card se-form',
				'title_reply'          => $review ? se_t( 'leave' ) : se_l( 'Leave a comment', 'Laisser un commentaire' ),
				'title_reply_before'   => '<h3 id="reply-title" class="se-h3">',
				'title_reply_after'    => '</h3>',
				'label_submit'         => $review ? se_t( 'post' ) : se_l( 'Post comment', 'Publier' ),
				'class_submit'         => 'se-btn se-btn-primary',
				'submit_field'         => '<p class="se-form-submit"><span class="se-small">' . esc_html( se_t( 'moderation' ) ) . '</span>%1$s %2$s</p>',
				'comment_notes_before' => '',
				'comment_field'        => '<p class="se-field"><label class="se-ui" for="comment">' . esc_html( $review ? se_t( 'comment' ) : se_l( 'Comment', 'Commentaire' ) ) . '</label><textarea class="se-input" id="comment" name="comment" rows="6" maxlength="5000" required></textarea></p>',
				'fields'               => array(
					'author' => '<p class="se-field"><label class="se-ui" for="author">' . esc_html( se_t( 'name' ) ) . '</label><input class="se-input" id="author" name="author" type="text" autocomplete="name" required></p>',
					'email'   => '<p class="se-field"><label class="se-ui" for="email">' . esc_html( se_t( 'email' ) ) . '</label><input class="se-input" id="email" name="email" type="email" autocomplete="email" required></p>',
					'cookies' => '<p class="se-check"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"><label class="se-small" for="wp-comment-cookies-consent">' . esc_html( se_l( 'Save my name and email in this browser for the next time I comment.', 'Enregistrer mon nom et mon e-mail dans ce navigateur pour mon prochain commentaire.' ) ) . '</label></p>',
				),
			),
			$post
		);
	}
	echo '</section>';
}
