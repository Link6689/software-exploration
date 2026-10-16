#!/usr/bin/env bash
# Installs software-exploration.com on a cPanel account in one go:
# database, WordPress, Blocksy, Polylang (EN at the root, FR under /fr/),
# the Software Exploration child theme and the se-reviews plugin.
#
# Run it from cPanel → Terminal (the domain must already be added in cPanel → Domains):
#   curl -fsSL https://raw.githubusercontent.com/Link6689/software-exploration/main/bin/install-cpanel.sh | bash -s -- software-exploration.com you@example.com
#
# It only needs curl, tar, unzip (or PHP's zip extension) and the account's PHP:
# no WP-CLI, which crashes on some shared hosts.
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
trap 'printf "\n\033[1;31mL’installation s’est arrêtée (ligne %s). Copiez ce message à Claude.\033[0m\n" "$LINENO" >&2' ERR

[ -n "$DOMAIN" ] && [ -n "$EMAIL" ] || die "usage : bash install-cpanel.sh <domaine> <email-admin>"
command -v php >/dev/null || die "php introuvable dans le terminal."
command -v curl >/dev/null || die "curl introuvable dans le terminal."

# ---------- document root ----------
DOCROOT="${SE_DOCROOT:-}"
if [ -z "$DOCROOT" ] && command -v uapi >/dev/null; then
	DOCROOT="$(uapi --output=json DomainInfo single_domain_data domain="$DOMAIN" | json result.data.documentroot)"
fi
[ -n "$DOCROOT" ] || die "dossier du domaine introuvable. Ajoutez $DOMAIN dans cPanel → Domaines, ou relancez avec SE_DOCROOT=/home/<compte>/<dossier>."
mkdir -p "$DOCROOT"
[ -f "$DOCROOT/wp-config.php" ] && die "WordPress est déjà installé dans $DOCROOT. Rien n'a été modifié."
say "Installation dans $DOCROOT"

TMP="$(mktemp -d)"
cleanup() { rm -rf "$TMP"; }
trap cleanup EXIT

# Unzip a .zip into a directory (unzip if present, PHP's ZipArchive otherwise).
extract_zip() {
	if command -v unzip >/dev/null; then
		unzip -qo "$1" -d "$2"
	else
		php -r '$z = new ZipArchive(); if ($z->open($argv[1]) !== true) { exit(1); } $z->extractTo($argv[2]); $z->close();' "$1" "$2"
	fi
}
# Download a theme or plugin from wordpress.org into wp-content.
wporg() {
	curl -fsSL -o "$TMP/$2.zip" "https://downloads.wordpress.org/$1/$2.latest-stable.zip" \
		|| curl -fsSL -o "$TMP/$2.zip" "https://downloads.wordpress.org/$1/$2.zip"
	extract_zip "$TMP/$2.zip" "$DOCROOT/wp-content/$1s/"
}

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

# ---------- files ----------
say "Téléchargement de WordPress"
curl -fsSL https://wordpress.org/latest.tar.gz | tar xz -C "$DOCROOT" --strip-components=1

say "Blocksy, Blocksy Companion et Polylang"
wporg theme blocksy
wporg plugin blocksy-companion
wporg plugin polylang

say "Thème et plugin Software Exploration ($REPO, branche $BRANCH)"
curl -fsSL "https://codeload.github.com/$REPO/tar.gz/refs/heads/$BRANCH" | tar xz -C "$TMP"
SRC="$(find "$TMP" -mindepth 1 -maxdepth 1 -type d -name 'software-exploration-*' | head -n 1)"
[ -n "$SRC" ] && [ -d "$SRC/wordpress/plugins/se-reviews" ] || die "archive du dépôt incomplète (branche $BRANCH)."
rm -rf "$DOCROOT/wp-content/plugins/se-reviews" "$DOCROOT/wp-content/themes/software-exploration"
cp -R "$SRC/wordpress/plugins/se-reviews" "$DOCROOT/wp-content/plugins/"
cp -R "$SRC/wordpress/themes/software-exploration" "$DOCROOT/wp-content/themes/"

# ---------- wp-config.php ----------
say "Configuration"
SALTS="$(curl -fsSL https://api.wordpress.org/secret-key/1.1/salt/)"
DB_NAME="$DB_NAME" DB_USER="$DB_USER" DB_PASS="$DB_PASS" SALTS="$SALTS" php -r '
	$c = file_get_contents($argv[1] . "/wp-config-sample.php");
	$c = str_replace(array("database_name_here", "username_here", "password_here"), array(getenv("DB_NAME"), getenv("DB_USER"), getenv("DB_PASS")), $c);
	$c = preg_replace("/define\(\s*.AUTH_KEY.*?define\(\s*.NONCE_SALT[^;]*;/s", getenv("SALTS"), $c);
	$c = str_replace("/* Add any custom values between this line and the \"stop editing\" line. */", "/* Add any custom values between this line and the \"stop editing\" line. */\ndefine( \"DISALLOW_FILE_EDIT\", true );", $c);
	file_put_contents($argv[1] . "/wp-config.php", $c);
' "$DOCROOT"
chmod 640 "$DOCROOT/wp-config.php" 2>/dev/null || true

# Runs a PHP file with WordPress loaded, as if the page were requested on the domain.
wp_php() {
	DOMAIN="$DOMAIN" php -d memory_limit=512M -r '
		$_SERVER["HTTP_HOST"] = getenv("DOMAIN"); $_SERVER["SERVER_NAME"] = getenv("DOMAIN");
		$_SERVER["HTTPS"] = "on"; $_SERVER["SERVER_PORT"] = 443; $_SERVER["REQUEST_URI"] = "/"; $_SERVER["REQUEST_METHOD"] = "GET";
		$_SERVER["SCRIPT_NAME"] = "/index.php"; $_SERVER["PHP_SELF"] = "/index.php"; $_SERVER["REMOTE_ADDR"] = "127.0.0.1";
		require $argv[1];' "$1"
}

# ---------- step 1: install WordPress, activate theme and plugins ----------
ADMIN_USER="se-admin"
ADMIN_PASS="$(rand 20)"
cat > "$TMP/step1.php" <<PHP
<?php
define( 'WP_INSTALLING', true );
require '$DOCROOT/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
add_filter( 'pre_wp_mail', '__return_false' ); // no "new site" email during the install
if ( ! is_blog_installed() ) {
	wp_install( 'Software Exploration', '$ADMIN_USER', '$EMAIL', 1, '', '$ADMIN_PASS', 'en_US' );
}
update_option( 'siteurl', 'https://$DOMAIN' );
update_option( 'home', 'https://$DOMAIN' );
update_option( 'blogdescription', 'Hands-on software reviews' );
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'comment_moderation', 1 );
update_option( 'comment_max_links', 1 );
update_option( 'show_avatars', 0 );
switch_theme( 'software-exploration' );
foreach ( array( 'polylang/polylang.php', 'blocksy-companion/blocksy-companion.php', 'se-reviews/se-reviews.php' ) as \$plugin ) {
	\$r = activate_plugin( \$plugin );
	if ( is_wp_error( \$r ) ) {
		fwrite( STDERR, \$plugin . ' : ' . \$r->get_error_message() . "\n" );
		exit( 1 );
	}
}
echo "ok\n";
PHP
say "Installation de WordPress"
wp_php "$TMP/step1.php" >/dev/null

# ---------- step 2: languages, home pages, Blocksy header ----------
cat > "$TMP/step2.php" <<PHP
<?php
// Admin context: Polylang only exposes its API there until a language exists.
define( 'WP_ADMIN', true );
require '$DOCROOT/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/admin.php';
PHP
cat >> "$TMP/step2.php" <<'PHP'
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
flush_rewrite_rules( true );
echo "ok\n";
PHP
say "Langues, pages d'accueil et en-tête"
wp_php "$TMP/step2.php" >/dev/null

# Pretty permalinks need WordPress's rules in .htaccess (Apache / LiteSpeed).
if [ ! -f "$DOCROOT/.htaccess" ] || ! grep -q "BEGIN WordPress" "$DOCROOT/.htaccess"; then
	cat >> "$DOCROOT/.htaccess" <<'HTACCESS'
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
HTACCESS
fi

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
