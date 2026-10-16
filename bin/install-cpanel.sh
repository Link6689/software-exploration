#!/usr/bin/env bash
# Installs software-exploration.com on a cPanel account in one go:
# database, WordPress, Blocksy, Polylang (EN at the root, FR under /fr/),
# the Software Exploration child theme and the se-reviews plugin.
#
# Run it from cPanel → Terminal (the domain must already be added in cPanel → Domains):
#   curl -fsSL https://raw.githubusercontent.com/Link6689/software-exploration/main/bin/install-cpanel.sh | bash -s -- software-exploration.com you@example.com
#
# Optional environment variables:
#   SE_BRANCH   branch of the repository to install from (default: main)
#   SE_DOCROOT  document root, if it cannot be read from cPanel
#   SE_DB_NAME, SE_DB_USER, SE_DB_PASS  use an existing database instead of creating one
set -euo pipefail

DOMAIN="${1:-}"
EMAIL="${2:-}"
BRANCH="${SE_BRANCH:-main}"
REPO="Link6689/software-exploration"

say() { printf '\n\033[1;32m==> %s\033[0m\n' "$*"; }
die() { printf '\n\033[1;31mErreur : %s\033[0m\n' "$*" >&2; exit 1; }
rand() { LC_ALL=C tr -dc 'A-Za-z0-9' < /dev/urandom | head -c "$1" || true; }
json() { php -r '$j = json_decode(stream_get_contents(STDIN), true); $v = $j; foreach (explode(".", $argv[1]) as $k) { $v = is_array($v) && array_key_exists($k, $v) ? $v[$k] : ""; } echo is_scalar($v) ? $v : json_encode($v);' "$1"; }

[ -n "$DOMAIN" ] && [ -n "$EMAIL" ] || die "usage : bash install-cpanel.sh <domaine> <email-admin>"
command -v php >/dev/null || die "php introuvable dans le terminal."

# ---------- document root ----------
DOCROOT="${SE_DOCROOT:-}"
if [ -z "$DOCROOT" ] && command -v uapi >/dev/null; then
	DOCROOT="$(uapi --output=json DomainInfo single_domain_data domain="$DOMAIN" | json result.data.documentroot)"
fi
[ -n "$DOCROOT" ] || die "dossier du domaine introuvable. Ajoutez $DOMAIN dans cPanel → Domaines, ou relancez avec SE_DOCROOT=/home/<compte>/<dossier>."
mkdir -p "$DOCROOT"
[ -f "$DOCROOT/wp-config.php" ] && die "WordPress est déjà installé dans $DOCROOT. Rien n'a été modifié."
say "Installation dans $DOCROOT"

# ---------- WP-CLI ----------
if command -v wp >/dev/null; then
	WP=(wp)
else
	mkdir -p "$HOME/.se-tools"
	curl -fsSL -o "$HOME/.se-tools/wp-cli.phar" https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
	WP=(php "$HOME/.se-tools/wp-cli.phar")
fi
[ "$(id -u)" = "0" ] && WP+=(--allow-root)
wp() { "${WP[@]}" --path="$DOCROOT" "$@"; }

# ---------- database ----------
DB_NAME="${SE_DB_NAME:-}"
DB_USER="${SE_DB_USER:-}"
DB_PASS="${SE_DB_PASS:-}"
if [ -z "$DB_NAME" ]; then
	command -v uapi >/dev/null || die "uapi introuvable : créez une base dans cPanel → Bases de données MySQL et relancez avec SE_DB_NAME, SE_DB_USER, SE_DB_PASS."
	PREFIX="$(uapi --output=json Mysql get_restrictions | json result.data.prefix)"
	SUFFIX="se$(rand 4 | tr 'A-Z' 'a-z')"
	DB_NAME="${PREFIX}${SUFFIX}"
	DB_USER="${PREFIX}${SUFFIX}"
	DB_PASS="$(rand 24)"
	say "Création de la base $DB_NAME"
	[ "$(uapi --output=json Mysql create_database name="$DB_NAME" | json result.status)" = "1" ] || die "création de la base impossible."
	[ "$(uapi --output=json Mysql create_user name="$DB_USER" password="$DB_PASS" | json result.status)" = "1" ] || die "création de l'utilisateur MySQL impossible."
	[ "$(uapi --output=json Mysql set_privileges_on_database user="$DB_USER" database="$DB_NAME" privileges='ALL PRIVILEGES' | json result.status)" = "1" ] || die "droits MySQL impossibles à attribuer."
fi

# ---------- WordPress ----------
say "Téléchargement de WordPress"
wp core download --locale=en_US --skip-content --force >/dev/null
mkdir -p "$DOCROOT/wp-content/themes" "$DOCROOT/wp-content/plugins"
wp config create --dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS" --dbhost=localhost --skip-check >/dev/null
ADMIN_USER="se-admin"
ADMIN_PASS="$(rand 20)"
wp core install --url="https://$DOMAIN" --title="Software Exploration" --admin_user="$ADMIN_USER" --admin_password="$ADMIN_PASS" --admin_email="$EMAIL" --skip-email >/dev/null

say "Blocksy, Blocksy Companion et Polylang"
wp theme install blocksy >/dev/null
wp plugin install blocksy-companion polylang --activate >/dev/null

say "Thème et plugin Software Exploration ($REPO, branche $BRANCH)"
TMP="$(mktemp -d)"
curl -fsSL "https://codeload.github.com/$REPO/tar.gz/refs/heads/$BRANCH" | tar xz -C "$TMP"
SRC="$(find "$TMP" -mindepth 1 -maxdepth 1 -type d | head -n 1)"
[ -d "$SRC/wordpress/plugins/se-reviews" ] || die "archive du dépôt incomplète (branche $BRANCH)."
rm -rf "$DOCROOT/wp-content/plugins/se-reviews" "$DOCROOT/wp-content/themes/software-exploration"
cp -R "$SRC/wordpress/plugins/se-reviews" "$DOCROOT/wp-content/plugins/"
cp -R "$SRC/wordpress/themes/software-exploration" "$DOCROOT/wp-content/themes/"
rm -rf "$TMP"
wp theme activate software-exploration >/dev/null
wp plugin activate se-reviews >/dev/null

say "Réglages : langues, permaliens, commentaires, en-tête"
wp rewrite structure '/%postname%/' >/dev/null
wp option update comment_moderation 1 >/dev/null
wp option update comment_max_links 1 >/dev/null
wp option update show_avatars 0 >/dev/null
wp option update blogdescription "Hands-on software reviews" >/dev/null

SETUP="$(mktemp --suffix=.php)"
cat > "$SETUP" <<'PHP'
<?php
// Remove the default sample content.
foreach ( get_posts( array( 'post_type' => array( 'post', 'page' ), 'post_status' => 'any', 'numberposts' => -1 ) ) as $p ) {
	wp_delete_post( $p->ID, true );
}

// Polylang: English (default, at the root) and French under /fr/.
$model = PLL()->model;
$add   = method_exists( $model, 'add_language' ) ? array( $model, 'add_language' ) : array( $model->languages, 'add' );
foreach ( array( array( 'English', 'en', 'en_US', 'gb', 0 ), array( 'Français', 'fr', 'fr_FR', 'fr', 1 ) ) as $l ) {
	if ( ! $model->get_language( $l[1] ) ) {
		call_user_func( $add, array( 'name' => $l[0], 'slug' => $l[1], 'locale' => $l[2], 'rtl' => 0, 'term_group' => $l[4], 'flag' => $l[3] ) );
	}
}
$pll_options = array(
	'default_lang'  => 'en',
	'hide_default'  => true,  // English at the root
	'force_lang'    => 1,     // French under /fr/
	'rewrite'       => true,
	'browser'       => false,
	'redirect_lang' => true,  // /fr/ itself is the French home page
);
if ( is_object( PLL()->options ) && method_exists( PLL()->options, 'set' ) ) {
	foreach ( $pll_options as $key => $value ) {
		PLL()->options->set( $key, $value );
	}
	PLL()->options->save();
} else {
	update_option( 'polylang', array_merge( (array) get_option( 'polylang' ), $pll_options ) );
}

// Home pages from the "Home page" pattern, linked as translations.
$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'se/home' );
$homes   = array();
$fr_text = array(
	'Hands-on reviews, rankings and comparisons of AI, SEO and marketing tools.' => 'Tests, classements et comparatifs d’outils IA, SEO et marketing, en conditions réelles.',
	'>Browse by category<'          => '>Explorer par catégorie<',
	'>The best AI tools right now<' => '>Les meilleurs outils IA du moment<',
	'>Latest reviews<'              => '>Derniers avis<',
	'>Deals of the month<'          => '>Bons plans du mois<',
	'>Guides and tutorials<'        => '>Guides et tutoriels<',
);
foreach ( array( 'en' => array( 'Find the right software. We tested it first.', 'home' ), 'fr' => array( 'Trouvez le bon logiciel. On l’a testé avant vous.', 'accueil' ) ) as $lang => $page ) {
	$content = $pattern ? $pattern['content'] : '';
	if ( 'fr' === $lang ) {
		$content = strtr( $content, $fr_text );
	}
	$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $page[0], 'post_name' => $page[1], 'post_content' => wp_slash( $content ), 'comment_status' => 'closed' ) );
	pll_set_post_language( $id, $lang );
	$homes[ $lang ] = $id;
}
pll_save_post_translations( $homes );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $homes['en'] );
// Polylang caches each language's home page; rebuild it now that the home pages exist.
PLL()->model->clean_languages_cache();

// Blocksy header: wordmark on the left, menu + search + EN/FR switch on the right.
if ( function_exists( 'blocksy_manager' ) ) {
	$h = blocksy_manager()->header_builder->get_default_value();
	foreach ( array( 'desktop', 'mobile' ) as $device ) {
		foreach ( $h['sections'][0][ $device ] as &$row ) {
			if ( 'middle-row' !== $row['id'] ) {
				continue;
			}
			foreach ( $row['placements'] as &$pl ) {
				if ( 'start' === $pl['id'] ) {
					$pl['items'] = array( 'text' );
				}
				if ( 'end' === $pl['id'] && 'desktop' === $device ) {
					$pl['items'] = array( 'menu', 'search', 'text~lang' );
				}
			}
			unset( $pl );
		}
		unset( $row );
	}
	$h['sections'][0]['items'] = array(
		array( 'id' => 'text', 'values' => array( 'header_text' => '[se_wordmark]' ) ),
		array( 'id' => 'text~lang', 'values' => array( 'header_text' => '[se_lang_switch]' ) ),
	);
	set_theme_mod( 'header_placements', $h );
}
set_theme_mod( 'single_blog_post_has_comments_website', 'no' );
flush_rewrite_rules();
echo "ok\n";
PHP
wp eval-file "$SETUP" >/dev/null
rm -f "$SETUP"
wp rewrite flush >/dev/null

cat <<EOF

$(printf '\033[1;32m')Software Exploration est installé.$(printf '\033[0m')

  Site            https://$DOMAIN/  (français : https://$DOMAIN/fr/)
  Administration  https://$DOMAIN/wp-admin/
  Identifiant     $ADMIN_USER
  Mot de passe    $ADMIN_PASS

Notez ce mot de passe maintenant : il n'est affiché qu'une fois.
Si le site s'affiche sans HTTPS, lancez AutoSSL dans cPanel → SSL/TLS Status.
Il reste à faire à la main : les menus EN et FR (Apparence → Menus)
et masquer « Méta de l'article » dans Personnaliser → Articles → Article seul.
EOF
