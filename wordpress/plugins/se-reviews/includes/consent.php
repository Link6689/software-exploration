<?php
/**
 * Cookie consent for Google Analytics 4, following the CNIL rules: GA4 is only
 * loaded after the visitor clicks "Accept", refusing is as easy as accepting,
 * and the choice is kept 6 months in a `se_consent` cookie. A footer link
 * reopens the banner. The banner is static HTML and the choice is read in the
 * browser, so it works with page caching.
 *
 * Nothing is printed when the GA4 measurement ID is empty (SE_GA4_ID constant
 * or the `se_ga4_id` filter): with no tracker, no banner is needed.
 */

defined( 'ABSPATH' ) || exit;

/** The site's GA4 property. SE_GA4_ID or the se_ga4_id filter override it; '' turns GA4 and the banner off. */
function se_ga4_id() {
	$id = apply_filters( 'se_ga4_id', defined( 'SE_GA4_ID' ) ? SE_GA4_ID : 'G-JBFLR7N2NH' );
	return preg_match( '/^G-[A-Z0-9]{4,16}$/', (string) $id ) ? $id : '';
}

/** Footer link that reopens the banner, or '' when there is no tracker. */
function se_consent_link() {
	if ( ! se_ga4_id() ) {
		return '';
	}
	return '<a href="#" data-se-consent-open>' . esc_html( se_l( 'Cookie settings', 'Gérer les cookies' ) ) . '</a>';
}

add_action(
	'wp_footer',
	function () {
		$id = se_ga4_id();
		if ( ! $id || is_admin() ) {
			return;
		}
		$legal = get_page_by_path( 'fr' === se_lang() ? 'mentions-legales' : 'legal-notice' );
		$more  = $legal ? ' <a href="' . esc_url( get_permalink( $legal ) ) . '#cookies">' . esc_html( se_l( 'Learn more', 'En savoir plus' ) ) . '</a>' : '';
		?>
<div class="se se-consent" id="se-consent" role="dialog" aria-live="polite" aria-label="<?php echo esc_attr( se_l( 'Cookies', 'Cookies' ) ); ?>" hidden>
	<p class="se-consent-text"><?php echo esc_html( se_l( 'With your consent, we use Google Analytics to measure the audience of the site (pages viewed, traffic sources). No advertising cookies.', 'Avec ton accord, nous utilisons Google Analytics pour mesurer l’audience du site (pages vues, sources de trafic). Aucun cookie publicitaire.' ) ); ?><?php echo $more; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></p>
	<div class="se-consent-actions">
		<button type="button" class="se-btn se-btn-primary" data-se-consent="no"><?php echo esc_html( se_l( 'Refuse', 'Refuser' ) ); ?></button>
		<button type="button" class="se-btn se-btn-primary" data-se-consent="yes"><?php echo esc_html( se_l( 'Accept', 'Accepter' ) ); ?></button>
	</div>
</div>
<script>
(function () {
	var id = <?php echo wp_json_encode( $id ); ?>, box = document.getElementById('se-consent');
	function read() { var m = document.cookie.match(/(?:^|; )se_consent=(yes|no)/); return m ? m[1] : ''; }
	function load() {
		if (window.seGa4) { return; }
		window.seGa4 = true;
		var s = document.createElement('script');
		s.async = true;
		s.src = 'https://www.googletagmanager.com/gtag/js?id=' + id;
		document.head.appendChild(s);
		window.dataLayer = window.dataLayer || [];
		window.gtag = function () { dataLayer.push(arguments); };
		gtag('js', new Date());
		gtag('config', id, { cookie_expires: 34128000 }); // 13 months, the CNIL maximum (GA4 defaults to 2 years).
	}
	function clearGa() {
		document.cookie.split('; ').forEach(function (c) {
			var name = c.split('=')[0];
			if (/^_ga/.test(name)) {
				var host = location.hostname.replace(/^www\./, '');
				['', '; domain=.' + host, '; domain=' + host].forEach(function (d) { document.cookie = name + '=; max-age=0; path=/' + d; });
			}
		});
	}
	function choose(v) {
		document.cookie = 'se_consent=' + v + '; max-age=15552000; path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
		box.hidden = true;
		if (v === 'yes') { load(); } else { clearGa(); }
	}
	box.addEventListener('click', function (e) { var v = e.target.getAttribute('data-se-consent'); if (v) { choose(v); } });
	document.addEventListener('click', function (e) {
		if (e.target.closest && e.target.closest('[data-se-consent-open]')) { e.preventDefault(); box.hidden = false; }
	});
	var v = read();
	if (v === 'yes') { load(); } else if (!v) { box.hidden = false; }
})();
</script>
		<?php
	},
	50
);
