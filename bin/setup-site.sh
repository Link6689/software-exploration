#!/usr/bin/env bash
# Updates an installed Software Exploration site and fills in what the
# installer leaves to do by hand. Safe to run again: it never deletes
# content and skips pages and menus that already exist.
#
#  1. Replaces the se-reviews plugin and the child theme with the
#     repository's version (no content is touched).
#  2. Creates the section pages in English and French, linked as
#     translations: AI, SEO, Marketing, Comparisons, Alternatives, Deals, Guides.
#  3. Creates the "Main EN" and "Main FR" menus and puts them in the
#     Blocksy header (desktop and mobile) for each language.
#
# Run it from cPanel → Terminal:
#   curl -fsSL https://raw.githubusercontent.com/Link6689/software-exploration/main/bin/setup-site.sh | bash -s -- software-exploration.com
#
# Optional environment variables: SE_BRANCH (default main), SE_DOCROOT.
set -euo pipefail

DOMAIN="${1:-}"
BRANCH="${SE_BRANCH:-main}"
REPO="Link6689/software-exploration"

say() { printf '\n\033[1;32m==> %s\033[0m\n' "$*"; }
die() { printf '\n\033[1;31mErreur : %s\033[0m\n' "$*" >&2; exit 1; }
json() { php -r '$j = json_decode(stream_get_contents(STDIN), true); $v = $j; foreach (explode(".", $argv[1]) as $k) { $v = is_array($v) && array_key_exists($k, $v) ? $v[$k] : ""; } echo is_scalar($v) ? $v : json_encode($v);' "$1"; }
trap 'printf "\n\033[1;31mLa mise à jour s’est arrêtée (ligne %s). Copiez ce message à Claude.\033[0m\n" "$LINENO" >&2' ERR

[ -n "$DOMAIN" ] || die "usage : bash setup-site.sh <domaine>"

DOCROOT="${SE_DOCROOT:-}"
if [ -z "$DOCROOT" ] && command -v uapi >/dev/null; then
	DOCROOT="$(uapi --output=json DomainInfo single_domain_data domain="$DOMAIN" | json result.data.documentroot)"
fi
[ -n "$DOCROOT" ] && [ -f "$DOCROOT/wp-config.php" ] || die "WordPress introuvable pour $DOMAIN. Lancez d'abord install-cpanel.sh."

TMP="$(mktemp -d)"
cleanup() { rm -rf "$TMP"; }
trap cleanup EXIT

wp_php() {
	DOMAIN="$DOMAIN" php -d memory_limit=512M -r '
		$_SERVER["HTTP_HOST"] = getenv("DOMAIN"); $_SERVER["SERVER_NAME"] = getenv("DOMAIN");
		$_SERVER["HTTPS"] = "on"; $_SERVER["SERVER_PORT"] = 443; $_SERVER["REQUEST_URI"] = "/"; $_SERVER["REQUEST_METHOD"] = "GET";
		$_SERVER["SCRIPT_NAME"] = "/index.php"; $_SERVER["PHP_SELF"] = "/index.php"; $_SERVER["REMOTE_ADDR"] = "127.0.0.1";
		require $argv[1];' "$1"
}

# ---------- 1. code ----------
say "Mise à jour du thème et du plugin ($REPO, branche $BRANCH)"
curl -fsSL "https://codeload.github.com/$REPO/tar.gz/refs/heads/$BRANCH" | tar xz -C "$TMP"
SRC="$(find "$TMP" -mindepth 1 -maxdepth 1 -type d -name 'software-exploration-*' | head -n 1)"
[ -n "$SRC" ] && [ -d "$SRC/wordpress/plugins/se-reviews" ] && [ -d "$SRC/wordpress/themes/software-exploration" ] || die "archive du dépôt incomplète (branche $BRANCH)."
for target in plugins/se-reviews themes/software-exploration; do
	rm -rf "$DOCROOT/wp-content/$target.new"
	cp -R "$SRC/wordpress/$target" "$DOCROOT/wp-content/$target.new"
	rm -rf "$DOCROOT/wp-content/$target"
	mv "$DOCROOT/wp-content/$target.new" "$DOCROOT/wp-content/$target"
done

# ---------- 2 + 3. pages and menus ----------
cat > "$TMP/setup.php" <<PHP
<?php
define( 'WP_ADMIN', true );
require '$DOCROOT/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/admin.php';
PHP
cat >> "$TMP/setup.php" <<'PHP'
if ( ! function_exists( 'PLL' ) || ! function_exists( 'pll_set_post_language' ) ) {
	fwrite( STDERR, "Polylang n'est pas actif.\n" );
	exit( 1 );
}

$p = function ( $text, $class = '' ) {
	$attr = $class ? ' {"className":"' . $class . '"}' : '';
	return '<!-- wp:paragraph' . $attr . ' --><p' . ( $class ? ' class="' . $class . '"' : '' ) . '>' . $text . '</p><!-- /wp:paragraph -->';
};
$deals_en = WP_Block_Patterns_Registry::get_instance()->get_registered( 'se/deals' );
$deals_en = $deals_en ? $deals_en['content'] : '<!-- wp:se/deal-grid /-->';
$deals_fr = strtr(
	$deals_en,
	array(
		'Discounts, extended trials and lifetime deals on tools we have tested. Checked every week.' => 'Réductions, essais prolongés et offres à vie sur des outils que nous avons testés. Vérifiés chaque semaine.',
		'>How we pick deals<' => '>Comment nous choisissons les bons plans<',
		'Only tools we have reviewed, only offers we could verify, removed on expiry.' => 'Uniquement des outils testés, uniquement des offres vérifiées, retirées à expiration.',
	)
);

// slug, title, content — EN then FR (free Polylang needs a different slug per language).
$sections = array(
	array(
		array( 'ai', 'AI tools', $p( 'Hands-on reviews and rankings of AI tools: writing, images, video, chatbots and agents.', 'se-lede' ) ),
		array( 'ia', 'Outils IA', $p( 'Tests et classements d’outils d’IA : rédaction, images, vidéo, chatbots et agents.', 'se-lede' ) ),
	),
	array(
		array( 'seo', 'SEO tools', $p( 'Rank tracking, site audits, keyword research and link building tools, tested on real sites.', 'se-lede' ) ),
		array( 'referencement', 'Outils SEO', $p( 'Suivi de positions, audits, recherche de mots-clés et netlinking : des outils testés sur de vrais sites.', 'se-lede' ) ),
	),
	array(
		array( 'marketing', 'Marketing tools', $p( 'Email marketing, social media, ads and automation tools, compared on real campaigns.', 'se-lede' ) ),
		array( 'marketing-digital', 'Outils marketing', $p( 'E-mailing, réseaux sociaux, publicité et automatisation : des outils comparés sur de vraies campagnes.', 'se-lede' ) ),
	),
	array(
		array( 'compare', 'Comparisons', $p( 'Head-to-head comparisons and big comparison tables, criterion by criterion.', 'se-lede' ) ),
		array( 'comparatif', 'Comparatifs', $p( 'Duels et grands tableaux comparatifs, critère par critère.', 'se-lede' ) ),
	),
	array(
		array( 'alternatives', 'Alternatives', $p( 'The best alternatives to popular tools, matched to the reason you want to switch.', 'se-lede' ) ),
		array( 'alternatives-outils', 'Alternatives', $p( 'Les meilleures alternatives aux outils connus, selon la raison pour laquelle vous voulez changer.', 'se-lede' ) ),
	),
	array(
		array( 'deals', 'Deals', $deals_en ),
		array( 'bons-plans', 'Bons plans', $deals_fr ),
	),
	array(
		array( 'guides', 'Guides', $p( 'Tutorials and how-tos to get more out of the tools we review.', 'se-lede' ) ),
		array( 'tutoriels', 'Guides', $p( 'Tutoriels et guides pratiques pour tirer le meilleur des outils que nous testons.', 'se-lede' ) ),
	),
);

$find = function ( $slug, $lang ) {
	$found = get_posts( array( 'post_type' => 'page', 'name' => $slug, 'post_status' => 'any', 'numberposts' => 1, 'lang' => $lang ) );
	return $found ? $found[0]->ID : 0;
};

$menu_pages = array( 'en' => array(), 'fr' => array() );
foreach ( $sections as $pair ) {
	$ids = array();
	foreach ( array( 'en' => $pair[0], 'fr' => $pair[1] ) as $lang => $page ) {
		$id = $find( $page[0], $lang );
		if ( ! $id ) {
			$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $page[1], 'post_name' => $page[0], 'post_content' => wp_slash( $page[2] ), 'comment_status' => 'closed' ) );
			pll_set_post_language( $id, $lang );
			echo "page créée : /" . ( 'fr' === $lang ? 'fr/' : '' ) . $page[0] . "/\n";
		}
		$ids[ $lang ]           = $id;
		$menu_pages[ $lang ][] = $id;
	}
	pll_save_post_translations( $ids );
}

// Menus.
$labels = array(
	'en' => array( 'AI', 'SEO', 'Marketing', 'Comparisons', 'Alternatives', 'Deals', 'Guides' ),
	'fr' => array( 'IA', 'SEO', 'Marketing', 'Comparatifs', 'Alternatives', 'Bons plans', 'Guides' ),
);
$menus = array();
foreach ( array( 'en' => 'Main EN', 'fr' => 'Main FR' ) as $lang => $name ) {
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu ) {
		$menus[ $lang ] = $menu->term_id;
		continue;
	}
	$menu_id = wp_create_nav_menu( $name );
	foreach ( $menu_pages[ $lang ] as $i => $page_id ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $labels[ $lang ][ $i ],
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $i + 1,
			)
		);
	}
	$menus[ $lang ] = $menu_id;
	echo "menu créé : $name\n";
}

// Header menu locations: Blocksy's "Header Menu 1" and mobile menu, one menu per language (Polylang).
$locations = array( 'menu_1', 'menu_mobile' );
$theme_locations = (array) get_theme_mod( 'nav_menu_locations', array() );
foreach ( $locations as $loc ) {
	$theme_locations[ $loc ] = $menus['en'];
}
set_theme_mod( 'nav_menu_locations', $theme_locations );

$nav = array();
if ( is_object( PLL()->options ) && method_exists( PLL()->options, 'get' ) ) {
	$nav = (array) PLL()->options->get( 'nav_menus' );
} else {
	$opt = get_option( 'polylang' );
	$nav = isset( $opt['nav_menus'] ) ? (array) $opt['nav_menus'] : array();
}
foreach ( $locations as $loc ) {
	$nav[ get_stylesheet() ][ $loc ] = array( 'en' => $menus['en'], 'fr' => $menus['fr'] );
}
if ( is_object( PLL()->options ) && method_exists( PLL()->options, 'set' ) ) {
	PLL()->options->set( 'nav_menus', $nav );
	PLL()->options->save();
} else {
	$opt              = get_option( 'polylang' );
	$opt['nav_menus'] = $nav;
	update_option( 'polylang', $opt );
}
PLL()->model->clean_languages_cache();
echo "ok\n";
PHP
say "Pages et menus EN / FR"
wp_php "$TMP/setup.php"

# Rewrite rules in a fresh run, so the new pages and languages are included.
cat > "$TMP/flush.php" <<PHP
<?php
define( 'WP_ADMIN', true );
require '$DOCROOT/wp-load.php';
flush_rewrite_rules( false );
PHP
wp_php "$TMP/flush.php"

cat <<EOF

$(printf '\033[1;32m')Mise à jour terminée.$(printf '\033[0m')
Menus : https://$DOMAIN/ et https://$DOMAIN/fr/
Pages : Software → Add software pour créer les fiches, puis Pages pour compléter chaque section.
EOF
