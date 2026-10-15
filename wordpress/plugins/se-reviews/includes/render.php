<?php
/**
 * HTML for every component of the design system. Each function returns a
 * string; class names match components.css (and the design system's bundle).
 */

defined( 'ABSPATH' ) || exit;

function se_render_score( $score, $size = 'md' ) {
	$s    = (float) $score;
	$tone = $s >= 8 ? 'high' : ( $s >= 6 ? 'mid' : 'low' );
	$out  = '<span class="' . se_cx( 'se-score', 'se-score-' . $tone, 'se-score-' . $size ) . '" aria-label="' . esc_attr( se_t( 'score' ) . ' ' . se_fmt_score( $s ) . '/10' ) . '">' . esc_html( se_fmt_score( $s ) );
	if ( 'sm' !== $size ) {
		$out .= '<small>/10</small>';
	}
	return $out . '</span>';
}

function se_render_stars( $value, $count = null, $show_value = true ) {
	static $n = 0;
	$v    = max( 0, min( 5, (float) $value ) );
	$star = 'M10 2l2.4 5 5.4.6-4 3.7 1.1 5.3L10 14l-4.9 2.6 1.1-5.3-4-3.7 5.4-.6z';
	$out  = '<span class="se-stars" role="img" aria-label="' . esc_attr( se_fmt_score( $v ) . ' / 5' ) . '">';
	for ( $i = 0; $i < 5; $i++ ) {
		$id   = 'se-st' . ( ++$n );
		$f    = max( 0, min( 1, $v - $i ) );
		$out .= '<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false"><defs><clipPath id="' . $id . '"><rect x="0" y="0" width="' . ( 20 * $f ) . '" height="20"/></clipPath></defs>'
			. '<path d="' . $star . '" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>'
			. '<path d="' . $star . '" fill="currentColor" clip-path="url(#' . $id . ')"/></svg>';
	}
	if ( $show_value ) {
		$out .= '<span class="se-stars-n">' . esc_html( se_fmt_score( $v ) ) . '</span>';
	}
	if ( null !== $count ) {
		$out .= '<span class="se-small" style="margin-left:4px">(' . (int) $count . ')</span>';
	}
	return $out . '</span>';
}

function se_render_logo( $tool, $size = 48 ) {
	$name = $tool['name'];
	$hash = 0;
	for ( $i = 0, $len = strlen( $name ); $i < $len; $i++ ) {
		$hash = ( $hash * 31 + ord( $name[ $i ] ) ) % 997;
	}
	$style = 'width:' . (int) $size . 'px;height:' . (int) $size . 'px;font-size:' . round( $size * 0.42 ) . 'px';
	if ( ! empty( $tool['logo'] ) ) {
		return '<span class="se-logo" style="' . $style . '"><img src="' . esc_url( $tool['logo'] ) . '" alt="" width="' . (int) $size . '" height="' . (int) $size . '" loading="lazy"></span>';
	}
	$letters = preg_replace( '/[^A-Za-z0-9]/', '', $name );
	$mono    = strtoupper( substr( $letters, 0, 1 ) ) . strtolower( substr( $letters, 1, 1 ) );
	return '<span class="se-logo se-logo-' . ( $hash % 5 ) . '" style="' . $style . '" aria-hidden="true">' . esc_html( $mono ) . '</span>';
}

/**
 * @param array $o variant (primary|cta|secondary|ghost), size (sm|md|lg), block, arrow.
 */
function se_render_button( $label, $href, $o = array() ) {
	$variant = isset( $o['variant'] ) ? $o['variant'] : 'primary';
	$size    = isset( $o['size'] ) ? $o['size'] : 'md';
	$cls     = se_cx( 'se-btn', 'se-btn-' . $variant, 'md' !== $size ? 'se-btn-' . $size : '', ! empty( $o['block'] ) ? 'se-btn-block' : '' );
	$icon    = 'cta' === $variant ? se_icon( 'ext', true ) : ( ! empty( $o['arrow'] ) ? se_icon( 'arrow', true ) : '' );
	$attrs   = 'cta' === $variant ? ' target="_blank" rel="sponsored nofollow noopener"' : '';
	return '<a class="' . $cls . '" href="' . esc_url( $href ? $href : '#' ) . '"' . $attrs . '>' . esc_html( $label ) . $icon . '</a>';
}

function se_render_badge( $text, $tone = 'neutral', $icon = '' ) {
	return '<span class="se-badge se-badge-' . esc_attr( $tone ) . '">' . ( $icon ? se_icon( $icon, true ) : '' ) . esc_html( $text ) . '</span>';
}

function se_render_placeholder( $text = '' ) {
	if ( ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) && ! is_admin() ) {
		return '';
	}
	return '<div class="se se-placeholder">' . esc_html( $text ? $text : se_t( 'choose_tool' ) ) . '</div>';
}

/* ---------- tools ---------- */

function se_render_tool_card( $tool, $compact = false ) {
	$name = '<a href="' . esc_url( $tool['review_url'] ? $tool['review_url'] : '#' ) . '">' . esc_html( $tool['name'] ) . '</a>';
	if ( $compact ) {
		return '<article class="se-card se-tool se-tool-compact">' . se_render_logo( $tool, 40 )
			. '<div class="se-tool-name"><div class="se-h4">' . $name . '</div><div class="se-small">' . esc_html( $tool['category'] ) . '</div></div>'
			. se_render_score( $tool['score'], 'sm' ) . '</article>';
	}
	$price = $tool['price'] ? se_t( 'from' ) . ' <b>' . esc_html( $tool['price'] ) . '</b>' : '';
	if ( $tool['free_plan'] ) {
		$price .= ( $price ? ' · ' : '' ) . esc_html( se_t( 'free_plan' ) );
	}
	return '<article class="se-card se-tool"><div class="se-tool-top">' . se_render_logo( $tool )
		. '<div class="se-tool-name"><h3 class="se-h4">' . $name . '</h3><div class="se-small">' . esc_html( $tool['category'] ) . '</div></div>'
		. se_render_score( $tool['score'] ) . '</div>'
		. '<p class="se-tool-desc">' . esc_html( $tool['tagline'] ) . '</p>'
		. '<div class="se-tool-foot"><div class="se-small">' . $price . '</div>'
		. ( $tool['review_url'] ? se_render_button( se_t( 'read_review' ), $tool['review_url'], array( 'variant' => 'ghost', 'arrow' => true ) ) : '' )
		. '</div></article>';
}

function se_render_ranked( $tool, $rank, $compact = false ) {
	$n     = str_pad( (string) $rank, 2, '0', STR_PAD_LEFT );
	$top   = 1 === (int) $rank;
	$badge = $top ? se_render_badge( se_t( 'editors' ), 'lime', 'crown' ) : '';
	$cls   = se_cx( 'se-card se-ranked', $compact ? 'se-ranked-compact' : '', $top ? 'se-ranked-top' : '' );
	if ( $compact ) {
		return '<article class="' . $cls . '"><span class="se-rank">' . $n . '</span>' . se_render_logo( $tool, 44 )
			. '<div><div class="se-row" style="gap:8px"><h3 class="se-h4">' . esc_html( $tool['name'] ) . '</h3>' . $badge . '</div>'
			. '<div class="se-small">' . esc_html( se_t( 'best_for' ) . se_colon() . $tool['best_for'] ) . '</div></div>'
			. se_render_score( $tool['score'] )
			. ( $tool['review_url'] ? se_render_button( se_t( 'read_review' ), $tool['review_url'], array( 'variant' => 'secondary', 'size' => 'sm' ) ) : '<span></span>' )
			. '</article>';
	}
	$pros = '';
	foreach ( array_slice( $tool['pros'], 0, 3 ) as $p ) {
		$pros .= '<li>' . se_icon( 'check', true ) . esc_html( $p ) . '</li>';
	}
	return '<article class="' . $cls . '" id="' . esc_attr( $tool['slug'] ) . '"><span class="se-rank">' . $n . '</span>'
		. '<div class="se-stack"><div class="se-row se-ranked-head">' . se_render_logo( $tool )
		. '<div class="se-ranked-name"><h3 class="se-h3">' . esc_html( $tool['name'] ) . '</h3><div class="se-small">' . esc_html( se_t( 'best_for' ) . se_colon() ) . '<b>' . esc_html( $tool['best_for'] ) . '</b></div></div>' . $badge . '</div>'
		. '<p class="se-ranked-text">' . esc_html( $tool['verdict'] ? $tool['verdict'] : $tool['tagline'] ) . '</p>'
		. ( $pros ? '<ul class="se-check-list">' . $pros . '</ul>' : '' ) . '</div>'
		. '<div class="se-ranked-side"><div class="se-row" style="justify-content:space-between"><span class="se-label se-muted">' . esc_html( se_t( 'overall' ) ) . '</span>' . se_render_score( $tool['score'] ) . '</div>'
		. ( $tool['price'] ? '<div class="se-small">' . esc_html( se_t( 'from' ) ) . ' <b>' . esc_html( $tool['price'] ) . '</b></div>' : '' )
		. '<div class="se-small">' . esc_html( se_t( 'free_plan' ) . se_colon() . se_t( $tool['free_plan'] ? 'yes' : 'no' ) ) . '</div>'
		. ( $tool['url'] ? se_render_button( se_t( 'try', $tool['name'] ), $tool['url'], array( 'variant' => 'cta', 'block' => true ) ) : '' )
		. ( $tool['review_url'] ? se_render_button( se_t( 'read_review' ), $tool['review_url'], array( 'variant' => 'secondary', 'block' => true ) ) : '' )
		. '</div></article>';
}

function se_render_promo_code( $code ) {
	return '<div class="se-code"><span>' . esc_html( $code ) . '</span><button type="button" class="se-btn se-btn-secondary se-btn-sm" data-code="' . esc_attr( $code ) . '" data-done="' . esc_attr( se_t( 'copied' ) ) . '" onclick="navigator.clipboard&&navigator.clipboard.writeText(this.dataset.code);this.textContent=this.dataset.done">' . esc_html( se_t( 'copy' ) ) . '</button></div>';
}

function se_render_verdict( $tool, $compact = false ) {
	$facts = array(
		array( se_t( 'best_for' ), $tool['best_for'] ),
		array( se_t( 'price' ), $tool['price'] ? se_t( 'from' ) . ' ' . $tool['price'] : '' ),
		array( se_t( 'free_plan' ), se_t( $tool['free_plan'] ? 'yes' : 'no' ) ),
		array( se_t( 'french_ui' ), se_t( $tool['french_ui'] ? 'yes' : 'no' ) ),
	);
	$dl = '';
	foreach ( $facts as $f ) {
		if ( '' !== $f[1] ) {
			$dl .= '<dt>' . esc_html( $f[0] ) . '</dt><dd>' . esc_html( $f[1] ) . '</dd>';
		}
	}
	$out  = '<aside class="se-card se-verdict" aria-label="' . esc_attr( se_t( 'verdict' ) ) . '">';
	$out .= '<div class="se-verdict-head">' . se_render_logo( $tool, $compact ? 40 : 56 )
		. '<div style="flex:1"><div class="se-label" style="color:var(--brand)">' . esc_html( se_t( 'verdict' ) ) . '</div><div class="' . ( $compact ? 'se-h4' : 'se-h3' ) . '">' . esc_html( $tool['name'] ) . '</div></div>'
		. se_render_score( $tool['score'], $compact ? 'md' : 'lg' ) . '</div>';
	$out .= '<div class="se-verdict-body">';
	if ( ! $compact && $tool['verdict'] ) {
		$out .= '<p class="se-verdict-text">' . esc_html( $tool['verdict'] ) . '</p>';
	}
	$out .= '<dl class="se-facts">' . $dl . '</dl>';
	if ( ! $compact && $tool['code'] ) {
		$out .= '<div><div class="se-label se-muted" style="margin-bottom:6px">' . esc_html( se_t( 'code' ) . ( $tool['deal_offer'] ? se_colon() . $tool['deal_offer'] : '' ) ) . '</div>' . se_render_promo_code( $tool['code'] ) . '</div>';
	}
	if ( $tool['url'] ) {
		$out .= se_render_button( se_t( 'try', $tool['name'] ), $tool['url'], array( 'variant' => 'cta', 'block' => true, 'size' => $compact ? 'md' : 'lg' ) );
	}
	return $out . '</div></aside>';
}

function se_render_proscons( $tool ) {
	$col = function ( $items, $kind ) {
		$li = '';
		foreach ( $items as $x ) {
			$li .= '<li><span class="se-' . $kind . '-ic">' . se_icon( 'pro' === $kind ? 'check' : 'x', true ) . '</span>' . esc_html( $x ) . '</li>';
		}
		return '<div class="se-card"><div class="se-pc-title"><span class="se-' . $kind . '-ic">' . se_icon( 'pro' === $kind ? 'plus' : 'minus' ) . '</span><h3 class="se-h3">' . esc_html( se_t( 'pro' === $kind ? 'pros' : 'cons' ) ) . '</h3></div><ul>' . $li . '</ul></div>';
	};
	return '<div class="se-proscons">' . $col( $tool['pros'], 'pro' ) . $col( $tool['cons'], 'con' ) . '</div>';
}

/**
 * @param array $criteria [ ['label' => '', 'score' => 0] ]; with $percent the scores are 0–100 weights.
 */
function se_render_breakdown( $criteria, $title = '', $total = null, $percent = false ) {
	$rows = '';
	foreach ( $criteria as $c ) {
		$w     = $percent ? $c['score'] : $c['score'] * 10;
		$val   = $percent ? $c['score'] . ( 'fr' === se_lang() ? ' %' : '%' ) : se_fmt_score( $c['score'] );
		$rows .= '<div class="se-bar"><span>' . esc_html( $c['label'] ) . '</span><span class="se-bar-track"><span class="se-bar-fill" style="width:' . esc_attr( max( 0, min( 100, $w ) ) ) . '%"></span></span><span class="se-bar-n">' . esc_html( $val ) . '</span></div>';
	}
	return '<div class="se-card se-pad"><div class="se-row" style="justify-content:space-between;margin-bottom:20px"><h3 class="se-h3">' . esc_html( $title ? $title : se_t( 'overall' ) ) . '</h3>'
		. ( null !== $total ? se_render_score( $total ) : '' ) . '</div><div class="se-bars">' . $rows . '</div></div>';
}

/** A table cell: yes / no / score:8.5 / plain text. */
function se_render_cell( $v ) {
	$v = trim( (string) $v );
	$k = strtolower( $v );
	if ( in_array( $k, array( 'yes', 'oui', 'true', '1', '✓' ), true ) ) {
		return '<span class="se-yes">' . se_icon( 'check' ) . '<span class="se-sr">' . esc_html( se_t( 'yes' ) ) . '</span></span>';
	}
	if ( in_array( $k, array( 'no', 'non', 'false', '0', '✗' ), true ) ) {
		return '<span class="se-no">' . se_icon( 'x' ) . '<span class="se-sr">' . esc_html( se_t( 'no' ) ) . '</span></span>';
	}
	if ( preg_match( '/^score:\s*([\d.,]+)$/i', $v, $m ) ) {
		return se_render_score( (float) str_replace( ',', '.', $m[1] ), 'sm' );
	}
	return esc_html( $v );
}

/**
 * @param array $rows [ ['group' => 'Label'] | ['label' => 'Label', 'values' => [...]] ] values are raw cell strings.
 * @param int   $highlight 0-based column, -1 for none.
 */
function se_render_table( $tools, $rows, $highlight = -1, $highlight_label = '', $show_cta = true ) {
	$head = '<th scope="col"><span class="se-label">' . esc_html( se_t( 'criterion' ) ) . '</span></th>';
	foreach ( $tools as $i => $tool ) {
		$hl    = $i === $highlight;
		$head .= '<th scope="col"' . ( $hl ? ' class="se-hl"' : '' ) . '><div class="se-th-tool">'
			. ( $hl ? se_render_badge( $highlight_label ? $highlight_label : se_t( 'editors' ), 'lime', 'crown' ) : '<span class="se-th-spacer"></span>' )
			. se_render_logo( $tool, 44 )
			. '<a class="se-h4 se-th-name" href="' . esc_url( $tool['review_url'] ? $tool['review_url'] : '#' ) . '">' . esc_html( $tool['name'] ) . '</a>'
			. se_render_score( $tool['score'] )
			. ( $show_cta && $tool['url'] ? se_render_button( se_t( 'visit' ), $tool['url'], array( 'variant' => 'cta', 'size' => 'sm' ) ) : '' )
			. '</div></th>';
	}
	$body = '';
	foreach ( $rows as $r ) {
		if ( isset( $r['group'] ) ) {
			$body .= '<tr class="se-group"><td colspan="' . ( count( $tools ) + 1 ) . '"><span class="se-label">' . esc_html( $r['group'] ) . '</span></td></tr>';
			continue;
		}
		$body .= '<tr><th scope="row">' . esc_html( $r['label'] ) . '</th>';
		foreach ( $tools as $i => $tool ) {
			$v     = isset( $r['values'][ $i ] ) ? $r['values'][ $i ] : '';
			$body .= '<td' . ( $i === $highlight ? ' class="se-hl"' : '' ) . '>' . se_render_cell( $v ) . '</td>';
		}
		$body .= '</tr>';
	}
	return '<div class="se-table-wrap"><table class="se-table"><thead><tr>' . $head . '</tr></thead><tbody>' . $body . '</tbody></table></div>';
}

function se_render_versus( $a, $b, $winner, $why_a, $why_b ) {
	$side = function ( $tool, $win, $why ) {
		return '<div class="' . se_cx( 'se-card se-vs-side', $win ? 'is-winner' : '' ) . '">'
			. ( $win ? se_render_badge( se_t( 'winner' ), 'lime', 'crown' ) : '<span class="se-th-spacer"></span>' )
			. se_render_logo( $tool, 72 ) . '<h2 class="se-h2">' . esc_html( $tool['name'] ) . '</h2>' . se_render_score( $tool['score'], 'lg' )
			. ( $why ? '<p class="se-vs-if"><b>' . esc_html( se_t( 'choose_if', $tool['name'] ) ) . '</b> ' . esc_html( $why ) . '</p>' : '' )
			. ( $tool['url'] ? se_render_button( se_t( 'try', $tool['name'] ), $tool['url'], array( 'variant' => 'cta' ) ) : '' )
			. '</div>';
	};
	return '<div class="se-vs">' . $side( $a, 'a' === $winner, $why_a ) . '<div class="se-vs-mid"><div class="se-vs-disc">VS</div></div>' . $side( $b, 'b' === $winner, $why_b ) . '</div>';
}

function se_render_pricing( $plans ) {
	$out = '<div class="se-plans">';
	foreach ( $plans as $p ) {
		$feat = '';
		foreach ( $p['features'] as $f ) {
			$feat .= '<li>' . se_icon( 'check', true ) . esc_html( $f ) . '</li>';
		}
		$period = 'month' === $p['period'] ? se_t( 'month' ) : ( 'year' === $p['period'] ? se_t( 'year' ) : '' );
		$out   .= '<div class="' . se_cx( 'se-card se-plan', $p['highlight'] ? 'is-hl' : '' ) . '"><div class="se-row" style="justify-content:space-between"><h3 class="se-h4">' . esc_html( $p['name'] ) . '</h3>'
			. ( $p['highlight'] ? se_render_badge( se_t( 'popular' ), 'brand' ) : '' ) . '</div>'
			. '<div class="se-price">' . esc_html( $p['price'] ) . ( $period ? '<small>' . esc_html( $period ) . '</small>' : '' ) . '</div>'
			. ( $feat ? '<ul class="se-check-list">' . $feat . '</ul>' : '' ) . '</div>';
	}
	return $out . '</div>';
}

/**
 * Top of an "Alternatives to X" page: the tool being replaced and why readers leave it.
 */
function se_render_alternatives_hero( $tool, $reasons ) {
	$li = '';
	foreach ( $reasons as $r ) {
		$li .= '<li>' . se_icon( 'swap', true ) . '<span>' . esc_html( $r ) . '</span></li>';
	}
	return '<div class="se-card se-alt-hero"><div class="se-alt-ref"><span class="se-label se-muted">' . esc_html( se_t( 'the_reference' ) ) . '</span>'
		. '<div class="se-row">' . se_render_logo( $tool, 56 ) . '<div style="flex:1"><div class="se-h3">' . esc_html( $tool['name'] ) . '</div><div class="se-small">'
		. esc_html( $tool['category'] . ( $tool['price'] ? ' · ' . se_t( 'from' ) . ' ' . $tool['price'] : '' ) ) . '</div></div>' . se_render_score( $tool['score'] ) . '</div>'
		. ( $tool['review_url'] ? se_render_button( se_t( 'read_review' ), $tool['review_url'], array( 'variant' => 'secondary', 'size' => 'sm' ) ) : '' ) . '</div>'
		. ( $li ? '<div class="se-alt-why"><h3 class="se-h4">' . esc_html( se_t( 'why_switch' ) ) . '</h3><ul class="se-why-list">' . $li . '</ul></div>' : '' )
		. '</div>';
}

/* ---------- trust ---------- */

function se_render_disclosure( $text = '' ) {
	return '<p class="se-disclosure">' . se_icon( 'info', true ) . '<span>' . esc_html( $text ? $text : se_t( 'disclosure' ) ) . '</span></p>';
}

function se_initials( $name ) {
	$parts = preg_split( '/\s+/', trim( $name ) );
	$out   = '';
	foreach ( array_slice( $parts, 0, 2 ) as $p ) {
		$out .= function_exists( 'mb_substr' ) ? mb_strtoupper( mb_substr( $p, 0, 1 ) ) : strtoupper( substr( $p, 0, 1 ) );
	}
	return $out;
}

function se_render_meta_line( $post, $tested = '' ) {
	$name = get_the_author_meta( 'display_name', $post->post_author );
	return '<div class="se-meta"><span class="se-row" style="gap:8px"><span class="se-avatar se-avatar-sm">' . esc_html( se_initials( $name ) ) . '</span><span>' . esc_html( se_t( 'by' ) ) . ' <b>' . esc_html( $name ) . '</b></span></span>'
		. ( $tested ? '<span>' . esc_html( se_t( 'tested_on' ) . ' ' . se_fmt_date( $tested ) ) . '</span>' : '' )
		. '<span>' . se_icon( 'clock', true ) . ' ' . esc_html( se_t( 'updated' ) . ' ' . se_fmt_date( get_the_modified_date( 'Y-m-d', $post ) ) ) . '</span>'
		. '<a href="' . esc_url( se_method_url() ) . '">' . esc_html( se_t( 'how_we_test' ) ) . '</a></div>';
}

function se_render_author_box( $post, $tested = '' ) {
	$uid  = $post->post_author;
	$name = get_the_author_meta( 'display_name', $uid );
	$bio  = get_the_author_meta( 'description', $uid );
	$role = get_user_meta( $uid, 'se_role_' . se_lang(), true );
	return '<aside class="se-card se-author"><span class="se-avatar">' . esc_html( se_initials( $name ) ) . '</span><div class="se-stack" style="flex:1">'
		. '<div><div class="se-h4">' . esc_html( $name ) . '</div>' . ( $role ? '<div class="se-small">' . esc_html( $role ) . '</div>' : '' ) . '</div>'
		. ( $bio ? '<p class="se-author-bio">' . esc_html( $bio ) . '</p>' : '' )
		. '<div class="se-meta">' . ( $tested ? '<span>' . esc_html( se_t( 'tested_on' ) ) . ' <b>' . esc_html( se_fmt_date( $tested ) ) . '</b></span>' : '' )
		. '<span>' . esc_html( se_t( 'updated' ) ) . ' <b>' . esc_html( se_fmt_date( get_the_modified_date( 'Y-m-d', $post ) ) ) . '</b></span>'
		. '<a href="' . esc_url( se_method_url() ) . '">' . esc_html( se_t( 'how_we_test' ) ) . '</a></div></div></aside>';
}

/* ---------- content ---------- */

function se_render_deal( $tool ) {
	return '<article class="se-card se-deal"><div class="se-row">' . se_render_logo( $tool, 40 )
		. '<div style="flex:1"><div class="se-h4">' . esc_html( $tool['name'] ) . '</div><div class="se-small">' . esc_html( $tool['category'] ) . '</div></div>' . se_render_badge( se_t( 'deal' ), 'cta', 'tag' ) . '</div>'
		. '<div class="se-deal-off">' . esc_html( $tool['deal_offer'] ) . '</div>'
		. ( $tool['deal_detail'] ? '<div class="se-small">' . esc_html( $tool['deal_detail'] ) . '</div>' : '' )
		. ( $tool['code'] ? se_render_promo_code( $tool['code'] ) : '' )
		. '<div class="se-row se-deal-foot">' . ( $tool['deal_expires'] ? '<span class="se-small">' . esc_html( se_t( 'expires' ) . ' ' . se_fmt_date( $tool['deal_expires'] ) ) . '</span>' : '<span></span>' )
		. ( $tool['url'] ? se_render_button( se_t( 'get_deal' ), $tool['url'], array( 'variant' => 'cta', 'size' => 'sm' ) ) : '' ) . '</div></article>';
}

/**
 * @param array $items [ ['q' => '', 'a' => ''] ]
 */
function se_render_faq( $items, $title = '' ) {
	$out = '<section class="se-faq-wrap"><h2 class="se-h2">' . esc_html( $title ? $title : se_t( 'faq' ) ) . '</h2><div class="se-faq">';
	foreach ( $items as $i => $it ) {
		$out .= '<details' . ( 0 === $i ? ' open' : '' ) . '><summary>' . esc_html( $it['q'] ) . se_icon( 'chev' ) . '</summary><p>' . esc_html( $it['a'] ) . '</p></details>';
	}
	return $out . '</div></section>';
}

/**
 * @param array $items [ ['name','meta','icon','url','featured'] ]
 */
function se_render_category_tiles( $items ) {
	$out = '<div class="se-grid se-g4 se-cats">';
	foreach ( $items as $c ) {
		$out .= '<a class="' . se_cx( 'se-card se-cat', $c['featured'] ? 'se-cat-feat' : '' ) . '" href="' . esc_url( $c['url'] ? $c['url'] : '#' ) . '">'
			. '<span class="se-cat-ic">' . se_icon( $c['icon'] ? $c['icon'] : 'spark' ) . '</span>'
			. '<span><span class="se-h4" style="display:block">' . esc_html( $c['name'] ) . '</span><span class="se-small">' . esc_html( $c['meta'] ) . '</span></span></a>';
	}
	return $out . '</div>';
}
