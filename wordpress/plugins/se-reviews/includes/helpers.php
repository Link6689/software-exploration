<?php
/**
 * Language, labels, formatting and icons shared by every renderer.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Current language: 'en' or 'fr'.
 *
 * In the editor's block preview (REST) Polylang has no current language, so the
 * language of the post being edited is used instead.
 */
function se_lang() {
	$lang = '';
	$post = get_post();
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST && $post && function_exists( 'pll_get_post_language' ) ) {
		$lang = pll_get_post_language( $post->ID, 'slug' );
	}
	if ( ! $lang && function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language( 'slug' );
	}
	if ( ! $lang && $post && function_exists( 'pll_get_post_language' ) ) {
		$lang = pll_get_post_language( $post->ID, 'slug' );
	}
	if ( ! $lang ) {
		$lang = 0 === strpos( determine_locale(), 'fr' ) ? 'fr' : 'en';
	}
	return apply_filters( 'se_lang', 'fr' === $lang ? 'fr' : 'en' );
}

/**
 * Pick the English or French string for the current language, falling back
 * to the other language when the field is empty (content may be written in
 * either language first).
 */
function se_l( $en, $fr ) {
	if ( 'fr' === se_lang() ) {
		return '' !== (string) $fr ? $fr : $en;
	}
	return '' !== (string) $en ? $en : $fr;
}

/** Interface labels in both languages. `{n}` is replaced by $name. */
function se_t( $key, $name = '' ) {
	static $labels = null;
	if ( null === $labels ) {
		$labels = array(
			'pros'          => array( 'Pros', 'Points forts' ),
			'cons'          => array( 'Cons', 'Points faibles' ),
			'verdict'       => array( 'Our verdict', 'Notre verdict' ),
			'best_for'      => array( 'Best for', 'Idéal pour' ),
			'price'         => array( 'Price', 'Prix' ),
			'from'          => array( 'From', 'À partir de' ),
			'free_plan'     => array( 'Free plan', 'Offre gratuite' ),
			'french_ui'     => array( 'French interface', 'Interface en français' ),
			'yes'           => array( 'Yes', 'Oui' ),
			'no'            => array( 'No', 'Non' ),
			'try'           => array( 'Try {n}', 'Essayer {n}' ),
			'visit'         => array( 'Visit', 'Voir' ),
			'read_review'   => array( 'Read review', "Lire l'avis" ),
			'editors'       => array( "Editor's choice", 'Choix de la rédaction' ),
			'tested_on'     => array( 'Tested on', 'Testé le' ),
			'updated'       => array( 'Updated', 'Mis à jour le' ),
			'by'            => array( 'By', 'Par' ),
			'faq'           => array( 'Frequently asked questions', 'Questions fréquentes' ),
			'overall'       => array( 'Overall score', 'Note globale' ),
			'how_we_test'   => array( 'How we test', 'Notre méthode de test' ),
			'disclosure'    => array( 'We test every tool hands-on. Some links earn us a commission at no extra cost to you; it never changes our scores.', 'Nous testons chaque outil en conditions réelles. Certains liens nous rapportent une commission, sans surcoût pour vous ; elle ne change jamais nos notes.' ),
			'code'          => array( 'Promo code', 'Code promo' ),
			'copy'          => array( 'Copy', 'Copier' ),
			'copied'        => array( 'Copied', 'Copié' ),
			'expires'       => array( 'Valid until', "Valable jusqu'au" ),
			'get_deal'      => array( 'Get deal', 'Profiter de l’offre' ),
			'deal'          => array( 'Deal', 'Bon plan' ),
			'month'         => array( '/mo', '/mois' ),
			'year'          => array( '/yr', '/an' ),
			'popular'       => array( 'Most popular', 'Le plus choisi' ),
			'choose_if'     => array( 'Choose {n} if', 'Choisissez {n} si' ),
			'winner'        => array( 'Winner', 'Gagnant' ),
			'criterion'     => array( 'Criterion', 'Critère' ),
			'score'         => array( 'Score', 'Note' ),
			'our_score'     => array( 'Our score', 'Notre note' ),
			'readers'       => array( 'Reader reviews', 'Avis des lecteurs' ),
			'based_on'      => array( 'based on {n} reviews', 'sur {n} avis' ),
			'no_ratings'    => array( 'No reader rating yet. Be the first.', 'Pas encore d’avis de lecteur. Soyez le premier.' ),
			'leave'         => array( 'Share your experience', 'Partagez votre expérience' ),
			'your_rating'   => array( 'Your rating', 'Votre note' ),
			'name'          => array( 'Name', 'Nom' ),
			'email'         => array( 'Email (not published)', 'E-mail (non publié)' ),
			'comment'       => array( 'Your review', 'Votre avis' ),
			'used_for'      => array( 'How long have you used it?', 'Depuis combien de temps l’utilisez-vous ?' ),
			'post'          => array( 'Post review', 'Publier mon avis' ),
			'reply'         => array( 'Reply', 'Répondre' ),
			'team'          => array( 'Team', 'Équipe' ),
			'uses'          => array( 'Uses it', 'Utilisateur' ),
			'moderation'    => array( 'Reviews are moderated before they appear. No links, no self-promotion.', 'Les avis sont modérés avant publication. Pas de liens, pas d’autopromotion.' ),
			'awaiting'      => array( 'Your review is awaiting moderation.', 'Votre avis est en attente de modération.' ),
			'choose_tool'   => array( 'Choose a tool in the block settings.', 'Choisissez un outil dans les réglages du bloc.' ),
			'alternatives'  => array( 'Alternatives to {n}', 'Alternatives à {n}' ),
			'why_switch'    => array( 'Why people look for alternatives', 'Pourquoi chercher une alternative' ),
			'the_reference' => array( 'The reference', 'La référence' ),
			'no_deals'      => array( 'No active deal right now. Check back soon.', 'Aucun bon plan en cours. Revenez bientôt.' ),
			'all'           => array( 'All', 'Tous' ),
			'search'        => array( 'Search a tool, a category…', 'Rechercher un outil, une catégorie…' ),
		);
	}
	$pair = isset( $labels[ $key ] ) ? $labels[ $key ] : array( $key, $key );
	$text = 'fr' === se_lang() ? $pair[1] : $pair[0];
	return str_replace( '{n}', $name, $text );
}

/** Separator before a value: "Best for: x" / "Idéal pour : x". */
function se_colon() {
	return 'fr' === se_lang() ? ' : ' : ': ';
}

/** 9.1 / 9,1 */
function se_fmt_score( $n ) {
	$s = number_format( (float) $n, 1, '.', '' );
	return 'fr' === se_lang() ? str_replace( '.', ',', $s ) : $s;
}

/** A Y-m-d date in the reader's language: "Oct 2, 2026" / "2 oct. 2026". */
function se_fmt_date( $ymd ) {
	if ( ! $ymd ) {
		return '';
	}
	$ts = strtotime( $ymd );
	if ( ! $ts ) {
		return esc_html( $ymd );
	}
	if ( 'fr' === se_lang() ) {
		$months = array( 'janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.' );
		return gmdate( 'j', $ts ) . ' ' . $months[ (int) gmdate( 'n', $ts ) - 1 ] . ' ' . gmdate( 'Y', $ts );
	}
	return gmdate( 'M j, Y', $ts );
}

/** Join class names, skipping empty ones. */
function se_cx( ...$names ) {
	return implode( ' ', array_filter( $names ) );
}

/** Split a textarea into trimmed, non-empty lines. */
function se_lines( $text ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $text ) ), 'strlen' ) );
}

/** Stroke icons, Lucide-style, 20px grid. */
function se_icon( $name, $small = false ) {
	$paths = array(
		'check'  => 'M4 10.5l4 4 8-9',
		'x'      => 'M5 5l10 10M15 5L5 15',
		'plus'   => 'M10 4v12M4 10h12',
		'minus'  => 'M4 10h12',
		'arrow'  => 'M4 10h12M11 5l5 5-5 5',
		'ext'    => 'M8 4H4v12h12v-4M11 4h5v5M16 4l-7 7',
		'search' => 'M9 15a6 6 0 100-12 6 6 0 000 12zM17 17l-3.5-3.5',
		'chev'   => 'M5 8l5 5 5-5',
		'info'   => 'M10 18a8 8 0 100-16 8 8 0 000 16zM10 9v5M10 6.5v.01',
		'clock'  => 'M10 18a8 8 0 100-16 8 8 0 000 16zM10 6v4l3 2',
		'spark'  => 'M10 2v4M10 14v4M2 10h4M14 10h4M4.5 4.5l2.5 2.5M13 13l2.5 2.5M15.5 4.5L13 7M7 13l-2.5 2.5',
		'pen'    => 'M13 3l4 4-9 9H4v-4l9-9z',
		'chart'  => 'M3 17h14M6 13v-3M10 13V6M14 13V9',
		'mega'   => 'M3 8v4l9 4V4L3 8zM12 7a3 3 0 010 6M5 12l1 5h2',
		'bolt'   => 'M11 2L4 11h5l-1 7 7-9h-5l1-7z',
		'code'   => 'M7 6l-4 4 4 4M13 6l4 4-4 4',
		'image'  => 'M3 4h14v12H3zM3 13l4-4 4 4 2-2 4 4M13 8v.01',
		'video'  => 'M3 5h10v10H3zM13 9l4-2v6l-4-2',
		'users'  => 'M7 9a3 3 0 100-6 3 3 0 000 6zM2 17a5 5 0 0110 0M13 4a3 3 0 010 5M15 12a5 5 0 013 5',
		'tag'    => 'M3 3h7l7 7-7 7-7-7V3zM7 7v.01',
		'crown'  => 'M3 6l4 4 3-6 3 6 4-4-2 10H5L3 6z',
		'swap'   => 'M4 7h12l-3-3M16 13H4l3 3',
	);
	$d = isset( $paths[ $name ] ) ? $paths[ $name ] : $paths['check'];
	return '<svg class="' . se_cx( 'se-ic', $small ? 'se-ic-sm' : '' ) . '" viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="' . esc_attr( $d ) . '"/></svg>';
}

/** The contour mark, drawn from tokens so it follows the theme. */
function se_mark( $size = 28 ) {
	static $n = 0;
	$id = 'se-mk' . ( ++$n );
	return '<svg class="se-mark" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 48 48" aria-hidden="true" focusable="false">'
		. '<defs><clipPath id="' . $id . '"><rect width="48" height="48" rx="12"/></clipPath></defs>'
		. '<rect width="48" height="48" rx="12" style="fill:var(--logo-ground)"/>'
		. '<g clip-path="url(#' . $id . ')" fill="none" style="stroke:var(--lime)" stroke-width="3.2"><circle cx="6" cy="42" r="12"/><circle cx="6" cy="42" r="22"/><circle cx="6" cy="42" r="32"/></g>'
		. '<circle cx="28.6" cy="19.4" r="5" stroke-width="2.5" style="fill:var(--lime);stroke:var(--logo-ground)"/></svg>';
}

/** URL of the methodology page in the current language. */
function se_method_url() {
	return apply_filters( 'se_method_url', se_site_url( 'fr' === se_lang() ? 'fr/notre-methode/' : 'how-we-test/' ), se_lang() );
}

/**
 * Absolute URL from the site root. Avoids home_url(), which Polylang rewrites
 * with the current language prefix.
 */
function se_site_url( $path = '' ) {
	return trailingslashit( get_option( 'home' ) ) . ltrim( $path, '/' );
}
