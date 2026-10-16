<?php
/**
 * The `se_tool` post type: one record per tool, shared by both languages.
 * Numbers and links are stored once; texts have an EN and a FR field.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field definitions. `i18n` fields are stored as se_<key>_en and se_<key>_fr.
 * Kinds: text, textarea, number, bool, url, date.
 */
function se_tool_fields() {
	return array(
		'score'        => array( 'kind' => 'number', 'label' => 'Score /10', 'help' => 'One decimal, e.g. 8.7' ),
		'affiliate_url' => array( 'kind' => 'url', 'label' => 'Affiliate URL', 'help' => 'Readers reach it through /go/<slug>/.' ),
		'free_plan'    => array( 'kind' => 'bool', 'label' => 'Free plan' ),
		'french_ui'    => array( 'kind' => 'bool', 'label' => 'French interface' ),
		'category'     => array( 'kind' => 'text', 'i18n' => true, 'label' => 'Category', 'help' => 'AI writing / Rédaction IA' ),
		'price'        => array( 'kind' => 'text', 'i18n' => true, 'label' => 'Starting price', 'help' => '$19/mo / 19 €/mois' ),
		'tagline'      => array( 'kind' => 'text', 'i18n' => true, 'label' => 'Tagline', 'help' => 'One line, shown on cards.' ),
		'best_for'     => array( 'kind' => 'text', 'i18n' => true, 'label' => 'Best for' ),
		'verdict'      => array( 'kind' => 'textarea', 'i18n' => true, 'label' => 'Verdict', 'help' => 'One or two sentences: the answer if the reader reads nothing else.' ),
		'pros'         => array( 'kind' => 'textarea', 'i18n' => true, 'label' => 'Pros', 'help' => 'One per line.' ),
		'cons'         => array( 'kind' => 'textarea', 'i18n' => true, 'label' => 'Cons', 'help' => 'One per line.' ),
		'criteria'     => array( 'kind' => 'textarea', 'label' => 'Scores by criterion', 'help' => 'One per line: Label EN | Label FR | score. Example: Output quality | Qualité des textes | 9.4' ),
		'plans'        => array( 'kind' => 'textarea', 'i18n' => true, 'label' => 'Pricing plans', 'help' => 'One per line: Name | Price | month or year | feature; feature; feature | popular. Example: Pro | $19 | month | Unlimited words; SEO outlines | popular' ),
		'promo_code'   => array( 'kind' => 'text', 'label' => 'Promo code' ),
		'deal_offer'   => array( 'kind' => 'text', 'i18n' => true, 'label' => 'Deal headline', 'help' => '-20% / 14 days free / $69 lifetime' ),
		'deal_detail'  => array( 'kind' => 'text', 'i18n' => true, 'label' => 'Deal detail' ),
		'deal_expires' => array( 'kind' => 'date', 'label' => 'Deal expires', 'help' => 'The deal disappears from the site after this date.' ),
		'review_url'   => array( 'kind' => 'url', 'i18n' => true, 'label' => 'Review URL', 'help' => 'Our review of this tool in each language.' ),
	);
}

/** Every stored meta key with its schema type. */
function se_tool_meta_keys() {
	$keys = array();
	foreach ( se_tool_fields() as $key => $f ) {
		$type = 'number' === $f['kind'] ? 'number' : ( 'bool' === $f['kind'] ? 'boolean' : 'string' );
		if ( ! empty( $f['i18n'] ) ) {
			$keys[ 'se_' . $key . '_en' ] = array( $type, $f );
			$keys[ 'se_' . $key . '_fr' ] = array( $type, $f );
		} else {
			$keys[ 'se_' . $key ] = array( $type, $f );
		}
	}
	return $keys;
}

function se_register_tool_post_type() {
	register_post_type(
		'se_tool',
		array(
			'labels'        => array(
				'name'          => 'Software',
				'singular_name' => 'Software',
				'add_new'       => 'Add software',
				'add_new_item'  => 'Add software',
				'edit_item'     => 'Edit software',
				'search_items'  => 'Search software',
				'all_items'     => 'All software',
				'menu_name'     => 'Software',
			),
			'public'        => false,
			'show_ui'       => true,
			'show_in_rest'  => true,
			'rest_base'     => 'se_tool',
			'menu_icon'     => 'dashicons-awards',
			'menu_position' => 21,
			'supports'      => array( 'title', 'thumbnail' ),
		)
	);

	foreach ( se_tool_meta_keys() as $meta_key => $def ) {
		register_post_meta(
			'se_tool',
			$meta_key,
			array(
				'type'          => $def[0],
				'single'        => true,
				'show_in_rest'  => true,
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'se_register_tool_post_type' );

/** Tools are one shared record for both languages: keep them out of Polylang. */
add_filter(
	'pll_get_post_types',
	function ( $types ) {
		unset( $types['se_tool'] );
		return $types;
	}
);

/* ---------- edit screen ---------- */

add_action(
	'add_meta_boxes_se_tool',
	function () {
		add_meta_box( 'se_tool_fields', 'Software details', 'se_tool_meta_box', 'se_tool', 'normal', 'high' );
	}
);

function se_tool_meta_box( $post ) {
	wp_nonce_field( 'se_tool_save', 'se_tool_nonce' );
	echo '<p>The logo is the featured image (square, 256px). The title is the tool name. Texts have an English and a French field; leave French empty to reuse English.</p>';
	echo '<table class="form-table se-tool-table" role="presentation"><tbody>';
	foreach ( se_tool_fields() as $key => $f ) {
		echo '<tr><th scope="row">' . esc_html( $f['label'] ) . '</th><td>';
		if ( ! empty( $f['i18n'] ) ) {
			echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">';
			foreach ( array( 'en' => 'EN', 'fr' => 'FR' ) as $lang => $label ) {
				echo '<div><strong style="display:block;margin-bottom:4px">' . $label . '</strong>';
				se_tool_input( 'se_' . $key . '_' . $lang, $f, get_post_meta( $post->ID, 'se_' . $key . '_' . $lang, true ) );
				echo '</div>';
			}
			echo '</div>';
		} else {
			se_tool_input( 'se_' . $key, $f, get_post_meta( $post->ID, 'se_' . $key, true ) );
		}
		if ( ! empty( $f['help'] ) ) {
			echo '<p class="description">' . esc_html( $f['help'] ) . '</p>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

function se_tool_input( $name, $f, $value ) {
	$n = esc_attr( $name );
	switch ( $f['kind'] ) {
		case 'textarea':
			echo '<textarea class="large-text" rows="4" name="' . $n . '" id="' . $n . '">' . esc_textarea( $value ) . '</textarea>';
			break;
		case 'bool':
			echo '<label><input type="checkbox" name="' . $n . '" value="1"' . checked( (bool) $value, true, false ) . '> ' . esc_html( $f['label'] ) . '</label>';
			break;
		case 'number':
			echo '<input type="number" step="0.1" min="0" max="10" name="' . $n . '" id="' . $n . '" value="' . esc_attr( $value ) . '">';
			break;
		case 'date':
			echo '<input type="date" name="' . $n . '" id="' . $n . '" value="' . esc_attr( $value ) . '">';
			break;
		case 'url':
			echo '<input type="url" class="large-text" name="' . $n . '" id="' . $n . '" value="' . esc_attr( $value ) . '">';
			break;
		default:
			echo '<input type="text" class="large-text" name="' . $n . '" id="' . $n . '" value="' . esc_attr( $value ) . '">';
	}
}

add_action(
	'save_post_se_tool',
	function ( $post_id ) {
		if ( ! isset( $_POST['se_tool_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['se_tool_nonce'] ), 'se_tool_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( se_tool_meta_keys() as $meta_key => $def ) {
			$f   = $def[1];
			$raw = isset( $_POST[ $meta_key ] ) ? wp_unslash( $_POST[ $meta_key ] ) : '';
			switch ( $f['kind'] ) {
				case 'bool':
					$value = '' !== $raw;
					break;
				case 'number':
					$value = '' === $raw ? '' : max( 0, min( 10, round( (float) $raw, 1 ) ) );
					break;
				case 'url':
					$value = esc_url_raw( $raw );
					break;
				case 'date':
					$value = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ? $raw : '';
					break;
				case 'textarea':
					$value = sanitize_textarea_field( $raw );
					break;
				default:
					$value = sanitize_text_field( $raw );
			}
			if ( '' === $value || false === $value ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
	}
);

/* ---------- list screen ---------- */

add_filter(
	'manage_se_tool_posts_columns',
	function ( $cols ) {
		$cols['se_score'] = 'Score';
		$cols['se_price'] = 'Price';
		$cols['se_go']    = 'Link';
		return $cols;
	}
);
add_action(
	'manage_se_tool_posts_custom_column',
	function ( $col, $post_id ) {
		if ( 'se_score' === $col ) {
			echo esc_html( get_post_meta( $post_id, 'se_score', true ) );
		} elseif ( 'se_price' === $col ) {
			echo esc_html( get_post_meta( $post_id, 'se_price_en', true ) );
		} elseif ( 'se_go' === $col ) {
			echo get_post_meta( $post_id, 'se_affiliate_url', true ) ? '<code>/go/' . esc_html( get_post_field( 'post_name', $post_id ) ) . '/</code>' : '—';
		}
	},
	10,
	2
);

/* ---------- reading ---------- */

/**
 * A tool as an array, texts already in the current language.
 *
 * @return array|null
 */
function se_tool( $id ) {
	$post = get_post( (int) $id );
	if ( ! $post || 'se_tool' !== $post->post_type || 'publish' !== $post->post_status ) {
		return null;
	}
	$id   = $post->ID;
	$meta = function ( $key ) use ( $id ) {
		return get_post_meta( $id, 'se_' . $key, true );
	};
	$pick = function ( $key ) use ( $id ) {
		return se_l( get_post_meta( $id, 'se_' . $key . '_en', true ), get_post_meta( $id, 'se_' . $key . '_fr', true ) );
	};

	$criteria = array();
	foreach ( se_lines( $meta( 'criteria' ) ) as $line ) {
		$p = array_map( 'trim', explode( '|', $line ) );
		if ( count( $p ) >= 3 ) {
			$criteria[] = array( 'label' => se_l( $p[0], $p[1] ), 'score' => (float) str_replace( ',', '.', $p[2] ) );
		}
	}
	$plans = array();
	foreach ( se_lines( $pick( 'plans' ) ) as $line ) {
		$p = array_map( 'trim', explode( '|', $line ) );
		if ( count( $p ) >= 2 ) {
			$plans[] = array(
				'name'      => $p[0],
				'price'     => $p[1],
				'period'    => isset( $p[2] ) ? $p[2] : '',
				'features'  => isset( $p[3] ) ? array_values( array_filter( array_map( 'trim', explode( ';', $p[3] ) ), 'strlen' ) ) : array(),
				'highlight' => isset( $p[4] ) && '' !== $p[4],
			);
		}
	}
	$expires = $meta( 'deal_expires' );

	return array(
		'id'           => $id,
		'name'         => get_the_title( $post ),
		'slug'         => $post->post_name,
		'logo'         => get_the_post_thumbnail_url( $post, 'thumbnail' ),
		'score'        => (float) $meta( 'score' ),
		'free_plan'    => (bool) $meta( 'free_plan' ),
		'french_ui'    => (bool) $meta( 'french_ui' ),
		'category'     => $pick( 'category' ),
		'price'        => $pick( 'price' ),
		'tagline'      => $pick( 'tagline' ),
		'best_for'     => $pick( 'best_for' ),
		'verdict'      => $pick( 'verdict' ),
		'pros'         => se_lines( $pick( 'pros' ) ),
		'cons'         => se_lines( $pick( 'cons' ) ),
		'criteria'     => $criteria,
		'plans'        => $plans,
		'url'          => $meta( 'affiliate_url' ) ? se_site_url( 'go/' . $post->post_name . '/' ) : '',
		'review_url'   => $pick( 'review_url' ),
		'code'         => $meta( 'promo_code' ),
		'deal_offer'   => $pick( 'deal_offer' ),
		'deal_detail'  => $pick( 'deal_detail' ),
		'deal_expires' => $expires,
		'deal_active'  => $pick( 'deal_offer' ) && ( ! $expires || $expires >= current_time( 'Y-m-d' ) ),
	);
}

/** Several tools, in the given order, skipping missing ones. */
function se_tools( $ids ) {
	return array_values( array_filter( array_map( 'se_tool', (array) $ids ) ) );
}
