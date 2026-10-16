<?php
/**
 * Contact form block (se/contact-form). Messages are sent by e-mail with
 * wp_mail() and not stored. Spam protection without a captcha or a third-party
 * service: a hidden honeypot field, a signed timestamp (bots that post at once
 * are rejected) and a limit of 5 messages per hour and per IP address.
 *
 * Messages go to the site's admin e-mail; change it with the `se_contact_to` filter.
 */

defined( 'ABSPATH' ) || exit;

function se_contact_subjects() {
	return array(
		'question' => array( 'A question about a review', 'Une question sur un test' ),
		'error'    => array( 'Report an error', 'Signaler une erreur' ),
		'partner'  => array( 'Partnership or tool to review', 'Partenariat ou outil à tester' ),
		'privacy'  => array( 'My personal data', 'Mes données personnelles' ),
		'other'    => array( 'Something else', 'Autre chose' ),
	);
}

function se_render_contact_form() {
	$status = isset( $_GET['contact'] ) ? sanitize_key( wp_unslash( $_GET['contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	if ( 'sent' === $status ) {
		return '<div class="se-card se-pad se-contact-done" id="se-contact" role="status"><div class="se-h3">' . esc_html( se_l( 'Thanks, your message is on its way.', 'Merci, ton message est bien parti.' ) ) . '</div>'
			. '<p class="se-small">' . esc_html( se_l( 'We usually reply within a few days.', 'Nous répondons en général sous quelques jours.' ) ) . '</p></div>';
	}
	$errors = array(
		'invalid' => se_l( 'Please fill in every field with a valid e-mail address.', 'Merci de remplir tous les champs avec une adresse e-mail valide.' ),
		'limit'   => se_l( 'Too many messages in a short time. Please try again later.', 'Trop de messages en peu de temps. Réessaie un peu plus tard.' ),
		'failed'  => se_l( 'The message could not be sent. Please try again later.', 'Le message n’a pas pu être envoyé. Réessaie un peu plus tard.' ),
	);

	$now  = time();
	$opts = '';
	foreach ( se_contact_subjects() as $key => $label ) {
		$opts .= '<option value="' . esc_attr( $key ) . '">' . esc_html( se_l( $label[0], $label[1] ) ) . '</option>';
	}
	$field = function ( $id, $label, $input ) {
		return '<p class="se-field"><label class="se-ui" for="se-contact-' . $id . '">' . esc_html( $label ) . '</label>' . $input . '</p>';
	};

	$out  = '<form class="se-card se-form se-contact" id="se-contact" method="post" action="' . esc_url( get_permalink() ) . '">';
	$out .= isset( $errors[ $status ] ) ? '<p class="se-contact-error" role="alert">' . esc_html( $errors[ $status ] ) . '</p>' : '';
	$out .= '<input type="hidden" name="se_contact" value="1"><input type="hidden" name="se_contact_page" value="' . esc_attr( get_the_ID() ) . '">'
		. '<input type="hidden" name="se_contact_t" value="' . esc_attr( $now . '.' . wp_hash( 'se_contact' . $now ) ) . '">'
		// Honeypot: hidden from people, filled in by bots.
		. '<p class="se-contact-hp" aria-hidden="true"><label for="se-contact-website">Website</label><input type="text" id="se-contact-website" name="website" tabindex="-1" autocomplete="off"></p>';
	$out .= '<div class="se-contact-row">'
		. $field( 'name', se_l( 'Name', 'Nom' ), '<input class="se-input" id="se-contact-name" name="se_name" type="text" autocomplete="name" maxlength="100" required>' )
		. $field( 'email', se_l( 'E-mail', 'E-mail' ), '<input class="se-input" id="se-contact-email" name="se_email" type="email" autocomplete="email" maxlength="200" required>' )
		. '</div>';
	$out .= $field( 'subject', se_l( 'Subject', 'Objet' ), '<select class="se-input" id="se-contact-subject" name="se_subject">' . $opts . '</select>' );
	$out .= $field( 'message', se_l( 'Message', 'Message' ), '<textarea class="se-input" id="se-contact-message" name="se_message" rows="7" maxlength="5000" required></textarea>' );
	$out .= '<p class="se-form-submit"><span class="se-small">' . esc_html(
		se_l(
			'Your name, e-mail and message are only used to reply to you. They are sent to us by e-mail and not stored on the site.',
			'Ton nom, ton e-mail et ton message servent uniquement à te répondre. Ils nous sont envoyés par e-mail et ne sont pas conservés sur le site.'
		)
	) . '</span><button type="submit" class="se-btn se-btn-primary">' . esc_html( se_l( 'Send', 'Envoyer' ) ) . '</button></p>';
	return $out . '</form>';
}

add_action(
	'template_redirect',
	function () {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- public form; a nonce would break on cached pages, see the file comment.
		if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) || empty( $_POST['se_contact'] ) ) {
			return;
		}
		$page = isset( $_POST['se_contact_page'] ) ? (int) $_POST['se_contact_page'] : 0;
		$back = $page && 'publish' === get_post_status( $page ) ? get_permalink( $page ) : home_url( '/' );
		$done = function ( $status ) use ( $back ) {
			wp_safe_redirect( add_query_arg( 'contact', $status, $back ) . '#se-contact', 303 );
			exit;
		};

		// Bots: honeypot filled, or a missing / forged / too quick timestamp. Pretend it worked.
		$t     = isset( $_POST['se_contact_t'] ) ? sanitize_text_field( wp_unslash( $_POST['se_contact_t'] ) ) : '';
		$parts = explode( '.', $t, 2 );
		$valid = 2 === count( $parts ) && hash_equals( wp_hash( 'se_contact' . $parts[0] ), $parts[1] ) && time() - (int) $parts[0] >= 3;
		if ( ! empty( $_POST['website'] ) || ! $valid ) {
			$done( 'sent' );
		}

		$name     = isset( $_POST['se_name'] ) ? trim( preg_replace( '/[\r\n]+/', ' ', sanitize_text_field( wp_unslash( $_POST['se_name'] ) ) ) ) : '';
		$email    = isset( $_POST['se_email'] ) ? sanitize_email( wp_unslash( $_POST['se_email'] ) ) : '';
		$message  = isset( $_POST['se_message'] ) ? trim( sanitize_textarea_field( wp_unslash( $_POST['se_message'] ) ) ) : '';
		$subjects = se_contact_subjects();
		$subject  = isset( $_POST['se_subject'] ) ? sanitize_key( wp_unslash( $_POST['se_subject'] ) ) : '';
		// phpcs:enable
		if ( '' === $name || ! is_email( $email ) || '' === $message || ! isset( $subjects[ $subject ] ) ) {
			$done( 'invalid' );
		}

		$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$key   = 'se_contact_' . md5( $ip );
		$count = (int) get_transient( $key );
		if ( $count >= 5 ) {
			$done( 'limit' );
		}
		set_transient( $key, $count + 1, HOUR_IN_SECONDS );

		$to   = apply_filters( 'se_contact_to', get_option( 'admin_email' ) );
		$sent = wp_mail(
			$to,
			'[' . wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) . '] ' . $subjects[ $subject ][1] . ' — ' . $name,
			$message . "\n\n—\n" . $name . ' <' . $email . ">\n" . $back,
			array( 'Reply-To: ' . str_replace( array( '"', '<', '>' ), '', $name ) . ' <' . $email . '>' )
		);
		$done( $sent ? 'sent' : 'failed' );
	}
);
