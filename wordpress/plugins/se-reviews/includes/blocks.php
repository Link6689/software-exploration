<?php
/**
 * Dynamic blocks. Each block is declared once here (attributes + editor
 * controls); assets/js/editor.js builds the editor UI from this config and
 * previews the PHP output with ServerSideRender.
 */

defined( 'ABSPATH' ) || exit;

function se_blocks_config() {
	$tool  = array( 'type' => 'number', 'default' => 0, 'control' => 'tool', 'label' => 'Tool' );
	$tools = array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'number' ), 'control' => 'tools', 'label' => 'Tools (in order)' );
	return array(
		'verdict-box'      => array(
			'title' => 'Verdict box',
			'icon'  => 'awards',
			'desc'  => 'Score, verdict, key facts, promo code and the affiliate button.',
			'attrs' => array(
				'toolId'  => $tool,
				'compact' => array( 'type' => 'boolean', 'default' => false, 'control' => 'toggle', 'label' => 'Compact (sidebar)' ),
			),
		),
		'pros-cons'        => array( 'title' => 'Pros and cons', 'icon' => 'yes-alt', 'desc' => 'From the tool record.', 'attrs' => array( 'toolId' => $tool ) ),
		'score-breakdown'  => array(
			'title' => 'Score breakdown',
			'icon'  => 'chart-bar',
			'desc'  => 'Scores by criterion, from the tool record.',
			'attrs' => array(
				'toolId'  => $tool,
				'titleEn' => array( 'type' => 'string', 'default' => '', 'control' => 'text', 'label' => 'Title (EN, optional)' ),
				'titleFr' => array( 'type' => 'string', 'default' => '', 'control' => 'text', 'label' => 'Title (FR, optional)' ),
			),
		),
		'pricing'          => array( 'title' => 'Pricing plans', 'icon' => 'money-alt', 'desc' => 'Plans from the tool record.', 'attrs' => array( 'toolId' => $tool ) ),
		'tool-card'        => array(
			'title' => 'Tool card',
			'icon'  => 'id',
			'desc'  => 'Teaser linking to the review.',
			'attrs' => array(
				'toolId'  => $tool,
				'compact' => array( 'type' => 'boolean', 'default' => false, 'control' => 'toggle', 'label' => 'Compact' ),
			),
		),
		'tool-grid'        => array(
			'title' => 'Tool grid',
			'icon'  => 'grid-view',
			'desc'  => 'Several tool cards: latest reviews, alternatives.',
			'attrs' => array(
				'toolIds' => $tools,
				'columns' => array( 'type' => 'string', 'default' => '3', 'control' => 'select', 'label' => 'Columns', 'options' => array( '2', '3', '4' ) ),
			),
		),
		'ranked-list'      => array(
			'title' => 'Ranked list',
			'icon'  => 'editor-ol',
			'desc'  => 'A "best X" ranking. Rank 1 gets Editor’s choice.',
			'attrs' => array(
				'toolIds' => $tools,
				'compact' => array( 'type' => 'boolean', 'default' => false, 'control' => 'toggle', 'label' => 'Compact (home page)' ),
			),
		),
		'comparison-table' => array(
			'title' => 'Comparison table',
			'icon'  => 'editor-table',
			'desc'  => 'Tools in columns, criteria in rows.',
			'attrs' => array(
				'toolIds'     => $tools,
				'highlight'   => array( 'type' => 'number', 'default' => 1, 'control' => 'number', 'label' => 'Highlighted column (1 = first, 0 = none)' ),
				'highlightEn' => array( 'type' => 'string', 'default' => '', 'control' => 'text', 'label' => 'Highlight label EN (default: Editor’s choice)' ),
				'highlightFr' => array( 'type' => 'string', 'default' => '', 'control' => 'text', 'label' => 'Highlight label FR' ),
				'autoRows'    => array( 'type' => 'boolean', 'default' => true, 'control' => 'toggle', 'label' => 'Add rows from tool records (best for, price, free plan, French UI)' ),
				'rows'        => array( 'type' => 'string', 'default' => '', 'control' => 'textarea', 'label' => 'Extra rows', 'help' => "One per line: Label EN | Label FR | value 1 | value 2 …  Values: yes, no, score:8.5 or text.  Group heading: ## Group EN | Group FR" ),
				'showCta'     => array( 'type' => 'boolean', 'default' => true, 'control' => 'toggle', 'label' => 'Affiliate buttons in the header' ),
			),
		),
		'versus'           => array(
			'title' => 'Versus (A vs B)',
			'icon'  => 'controls-repeat',
			'desc'  => 'Two tools face to face.',
			'attrs' => array(
				'toolA'  => array_merge( $tool, array( 'label' => 'Tool A' ) ),
				'toolB'  => array_merge( $tool, array( 'label' => 'Tool B' ) ),
				'winner' => array( 'type' => 'string', 'default' => '', 'control' => 'select', 'label' => 'Winner', 'options' => array( '', 'a', 'b' ) ),
				'whyAEn' => array( 'type' => 'string', 'default' => '', 'control' => 'textarea', 'label' => 'Choose A if… (EN)' ),
				'whyAFr' => array( 'type' => 'string', 'default' => '', 'control' => 'textarea', 'label' => 'Choisissez A si… (FR)' ),
				'whyBEn' => array( 'type' => 'string', 'default' => '', 'control' => 'textarea', 'label' => 'Choose B if… (EN)' ),
				'whyBFr' => array( 'type' => 'string', 'default' => '', 'control' => 'textarea', 'label' => 'Choisissez B si… (FR)' ),
			),
		),
		'alternatives-hero' => array(
			'title' => 'Alternatives: reference tool',
			'icon'  => 'randomize',
			'desc'  => 'The tool readers want to replace, and why.',
			'attrs' => array(
				'toolId'    => $tool,
				'reasonsEn' => array( 'type' => 'string', 'default' => '', 'control' => 'textarea', 'label' => 'Why people switch (EN, one per line)' ),
				'reasonsFr' => array( 'type' => 'string', 'default' => '', 'control' => 'textarea', 'label' => 'Pourquoi changer (FR, une par ligne)' ),
			),
		),
		'deal-card'        => array( 'title' => 'Deal card', 'icon' => 'tag', 'desc' => 'The deal stored on the tool record.', 'attrs' => array( 'toolId' => $tool ) ),
		'deal-grid'        => array(
			'title' => 'Active deals',
			'icon'  => 'tickets-alt',
			'desc'  => 'Every tool with a running deal. Expired deals drop out by themselves.',
			'attrs' => array(
				'limit'   => array( 'type' => 'number', 'default' => 12, 'control' => 'number', 'label' => 'Maximum' ),
				'columns' => array( 'type' => 'string', 'default' => '3', 'control' => 'select', 'label' => 'Columns', 'options' => array( '2', '3' ) ),
			),
		),
		'disclosure'       => array( 'title' => 'Affiliate disclosure', 'icon' => 'info-outline', 'desc' => 'Required on any page with an affiliate button.', 'attrs' => array() ),
		'meta-line'        => array(
			'title' => 'Author and dates',
			'icon'  => 'admin-users',
			'desc'  => 'By, tested on, updated, how we test. Goes under the H1.',
			'attrs' => array( 'tested' => array( 'type' => 'string', 'default' => '', 'control' => 'date', 'label' => 'Tested on' ) ),
		),
		'author-box'       => array(
			'title' => 'Author box',
			'icon'  => 'businessperson',
			'desc'  => 'Uses the post author’s name and biography.',
			'attrs' => array( 'tested' => array( 'type' => 'string', 'default' => '', 'control' => 'date', 'label' => 'Tested on' ) ),
		),
		'faq'              => array(
			'title' => 'FAQ',
			'icon'  => 'editor-help',
			'desc'  => 'Accordion with FAQPage structured data.',
			'attrs' => array(
				'itemsEn' => array( 'type' => 'string', 'default' => '', 'control' => 'textarea', 'label' => 'Questions EN', 'help' => "Q: question\nA: answer\n(blank line between items)" ),
				'itemsFr' => array( 'type' => 'string', 'default' => '', 'control' => 'textarea', 'label' => 'Questions FR' ),
			),
		),
		'category-tiles'   => array(
			'title' => 'Category tiles',
			'icon'  => 'category',
			'desc'  => 'Home page category grid.',
			'attrs' => array(
				'itemsEn' => array( 'type' => 'string', 'default' => '', 'control' => 'textarea', 'label' => 'Tiles EN', 'help' => 'One per line: Name | meta | icon | URL | featured. Icons: spark, pen, image, video, chart, search, mega, users, bolt, code.' ),
				'itemsFr' => array( 'type' => 'string', 'default' => '', 'control' => 'textarea', 'label' => 'Tiles FR' ),
			),
		),
	);
}

add_filter(
	'block_categories_all',
	function ( $cats ) {
		array_unshift( $cats, array( 'slug' => 'se-reviews', 'title' => 'Software Exploration' ) );
		return $cats;
	}
);

add_action(
	'init',
	function () {
		wp_register_script(
			'se-reviews-editor',
			SE_REVIEWS_URL . 'assets/js/editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-data', 'wp-core-data', 'wp-html-entities' ),
			SE_REVIEWS_VERSION,
			true
		);
		$client = array();
		foreach ( se_blocks_config() as $name => $b ) {
			$attributes = array();
			$controls   = array();
			foreach ( $b['attrs'] as $key => $a ) {
				$attributes[ $key ] = array_intersect_key( $a, array_flip( array( 'type', 'default', 'items' ) ) );
				$controls[ $key ]   = array_intersect_key( $a, array_flip( array( 'control', 'label', 'options', 'help' ) ) );
			}
			register_block_type(
				'se/' . $name,
				array(
					'api_version'     => 3,
					'title'           => $b['title'],
					'category'        => 'se-reviews',
					'attributes'      => $attributes,
					'supports'        => array( 'html' => false, 'align' => array( 'wide', 'full' ) ),
					'editor_script'   => 'se-reviews-editor',
					'render_callback' => function ( $attrs ) use ( $name ) {
						$html = se_render_block( $name, $attrs );
						if ( '' === $html ) {
							return '';
						}
						return '<div ' . get_block_wrapper_attributes( array( 'class' => 'se se-block se-b-' . $name ) ) . '>' . $html . '</div>';
					},
				)
			);
			$client[ $name ] = array(
				'title'      => $b['title'],
				'icon'       => $b['icon'],
				'desc'       => $b['desc'],
				'attributes' => $attributes,
				'controls'   => $controls,
			);
		}
		wp_add_inline_script( 'se-reviews-editor', 'window.SE_BLOCKS = ' . wp_json_encode( $client ) . ';', 'before' );
	}
);

/** "Q: … / A: …" items, or "question | answer" lines. */
function se_parse_faq( $text ) {
	$items = array();
	$q     = null;
	foreach ( se_lines( $text ) as $line ) {
		if ( preg_match( '/^(Q|Question)\s*:\s*(.+)$/iu', $line, $m ) ) {
			$q = $m[2];
		} elseif ( preg_match( '/^(A|R|Answer|Réponse)\s*:\s*(.+)$/iu', $line, $m ) && null !== $q ) {
			$items[] = array( 'q' => $q, 'a' => $m[2] );
			$q       = null;
		} elseif ( false !== strpos( $line, '|' ) ) {
			$p       = array_map( 'trim', explode( '|', $line, 2 ) );
			$items[] = array( 'q' => $p[0], 'a' => $p[1] );
		}
	}
	return $items;
}

/** Comparison rows from the tool records plus the editor's extra rows. */
function se_table_rows( $tools, $auto, $text ) {
	$rows = array();
	if ( $auto ) {
		$rows[] = array( 'label' => se_t( 'best_for' ), 'values' => wp_list_pluck( $tools, 'best_for' ) );
		$rows[] = array( 'label' => se_t( 'price' ), 'values' => wp_list_pluck( $tools, 'price' ) );
		$rows[] = array(
			'label'  => se_t( 'free_plan' ),
			'values' => array_map(
				function ( $t ) {
					return $t['free_plan'] ? 'yes' : 'no';
				},
				$tools
			),
		);
		$rows[] = array(
			'label'  => se_t( 'french_ui' ),
			'values' => array_map(
				function ( $t ) {
					return $t['french_ui'] ? 'yes' : 'no';
				},
				$tools
			),
		);
	}
	foreach ( se_lines( $text ) as $line ) {
		if ( 0 === strpos( $line, '##' ) ) {
			$p      = array_map( 'trim', explode( '|', trim( substr( $line, 2 ) ) ) );
			$rows[] = array( 'group' => se_l( $p[0], isset( $p[1] ) ? $p[1] : '' ) );
			continue;
		}
		$p = array_map( 'trim', explode( '|', $line ) );
		if ( count( $p ) >= 3 ) {
			$rows[] = array( 'label' => se_l( $p[0], $p[1] ), 'values' => array_slice( $p, 2 ) );
		}
	}
	return $rows;
}

function se_render_block( $name, $a ) {
	$tool = isset( $a['toolId'] ) ? se_tool( $a['toolId'] ) : null;
	$post = get_post();

	switch ( $name ) {
		case 'verdict-box':
			if ( ! $tool ) {
				return se_render_placeholder();
			}
			return se_render_verdict( $tool, ! empty( $a['compact'] ) ) . ( empty( $a['compact'] ) ? se_review_schema( $tool, $post ) : '' );

		case 'pros-cons':
			return $tool ? se_render_proscons( $tool ) : se_render_placeholder();

		case 'score-breakdown':
			return $tool ? se_render_breakdown( $tool['criteria'], se_l( $a['titleEn'], $a['titleFr'] ), $tool['score'] ) : se_render_placeholder();

		case 'pricing':
			return $tool ? se_render_pricing( $tool['plans'] ) : se_render_placeholder();

		case 'tool-card':
			return $tool ? se_render_tool_card( $tool, ! empty( $a['compact'] ) ) : se_render_placeholder();

		case 'tool-grid':
			$tools = se_tools( $a['toolIds'] );
			if ( ! $tools ) {
				return se_render_placeholder();
			}
			$out = '<div class="se-grid se-g' . (int) $a['columns'] . '">';
			foreach ( $tools as $t ) {
				$out .= se_render_tool_card( $t );
			}
			return $out . '</div>';

		case 'ranked-list':
			$tools = se_tools( $a['toolIds'] );
			if ( ! $tools ) {
				return se_render_placeholder();
			}
			$out = '<div class="se-grid">';
			foreach ( $tools as $i => $t ) {
				$out .= se_render_ranked( $t, $i + 1, ! empty( $a['compact'] ) );
			}
			return $out . '</div>';

		case 'comparison-table':
			$tools = se_tools( $a['toolIds'] );
			if ( ! $tools ) {
				return se_render_placeholder();
			}
			return se_render_table( $tools, se_table_rows( $tools, ! empty( $a['autoRows'] ), $a['rows'] ), (int) $a['highlight'] - 1, se_l( $a['highlightEn'], $a['highlightFr'] ), ! empty( $a['showCta'] ) );

		case 'versus':
			$ta = se_tool( $a['toolA'] );
			$tb = se_tool( $a['toolB'] );
			if ( ! $ta || ! $tb ) {
				return se_render_placeholder();
			}
			return se_render_versus( $ta, $tb, $a['winner'], se_l( $a['whyAEn'], $a['whyAFr'] ), se_l( $a['whyBEn'], $a['whyBFr'] ) );

		case 'alternatives-hero':
			return $tool ? se_render_alternatives_hero( $tool, se_lines( se_l( $a['reasonsEn'], $a['reasonsFr'] ) ) ) : se_render_placeholder();

		case 'deal-card':
			return $tool && $tool['deal_active'] ? se_render_deal( $tool ) : se_render_placeholder();

		case 'deal-grid':
			$ids   = get_posts(
				array(
					'post_type'   => 'se_tool',
					'numberposts' => 200,
					'fields'      => 'ids',
					'meta_query'  => array(
						'relation' => 'OR',
						array( 'key' => 'se_deal_offer_en', 'compare' => 'EXISTS' ),
						array( 'key' => 'se_deal_offer_fr', 'compare' => 'EXISTS' ),
					),
				)
			);
			$deals = array_filter(
				se_tools( $ids ),
				function ( $t ) {
					return $t['deal_active'];
				}
			);
			usort(
				$deals,
				function ( $x, $y ) {
					return strcmp( $x['deal_expires'] ? $x['deal_expires'] : '9999', $y['deal_expires'] ? $y['deal_expires'] : '9999' );
				}
			);
			$deals = array_slice( $deals, 0, max( 1, (int) $a['limit'] ) );
			if ( ! $deals ) {
				return '<p class="se-empty">' . esc_html( se_t( 'no_deals' ) ) . '</p>';
			}
			$out = '<div class="se-grid se-g' . (int) $a['columns'] . '">';
			foreach ( $deals as $t ) {
				$out .= se_render_deal( $t );
			}
			return $out . '</div>';

		case 'disclosure':
			return se_render_disclosure();

		case 'meta-line':
			return $post ? se_render_meta_line( $post, $a['tested'] ) : '';

		case 'author-box':
			return $post ? se_render_author_box( $post, $a['tested'] ) : '';

		case 'faq':
			$items = se_parse_faq( se_l( $a['itemsEn'], $a['itemsFr'] ) );
			return $items ? se_render_faq( $items ) . se_faq_schema( $items ) : se_render_placeholder( 'FAQ' );

		case 'category-tiles':
			$items = array();
			foreach ( se_lines( se_l( $a['itemsEn'], $a['itemsFr'] ) ) as $line ) {
				$p       = array_map( 'trim', explode( '|', $line ) );
				$items[] = array(
					'name'     => $p[0],
					'meta'     => isset( $p[1] ) ? $p[1] : '',
					'icon'     => isset( $p[2] ) ? $p[2] : '',
					'url'      => isset( $p[3] ) ? $p[3] : '',
					'featured' => ! empty( $p[4] ),
				);
			}
			return $items ? se_render_category_tiles( $items ) : se_render_placeholder( 'Category tiles' );
	}
	return '';
}

/* ---------- structured data ---------- */

function se_schema_enabled() {
	return (bool) apply_filters( 'se_reviews_output_schema', true );
}

function se_json_ld( $data ) {
	return '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
}

function se_review_schema( $tool, $post ) {
	if ( ! se_schema_enabled() || ! $post || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return '';
	}
	$item = array(
		'@type'               => 'SoftwareApplication',
		'name'                => $tool['name'],
		'applicationCategory' => 'BusinessApplication',
		'operatingSystem'     => 'Web',
	);
	$stats = se_reader_stats( $post->ID );
	if ( $stats['count'] >= 5 ) {
		$item['aggregateRating'] = array(
			'@type'       => 'AggregateRating',
			'ratingValue' => round( $stats['average'], 1 ),
			'ratingCount' => $stats['count'],
			'bestRating'  => 5,
			'worstRating' => 1,
		);
	}
	return se_json_ld(
		array(
			'@context'      => 'https://schema.org',
			'@type'         => 'Review',
			'itemReviewed'  => $item,
			'reviewRating'  => array( '@type' => 'Rating', 'ratingValue' => $tool['score'], 'bestRating' => 10, 'worstRating' => 0 ),
			'author'        => array( '@type' => 'Person', 'name' => get_the_author_meta( 'display_name', $post->post_author ) ),
			'datePublished' => get_the_date( 'c', $post ),
			'dateModified'  => get_the_modified_date( 'c', $post ),
			'reviewBody'    => $tool['verdict'],
		)
	);
}

function se_faq_schema( $items ) {
	if ( ! se_schema_enabled() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return '';
	}
	$entities = array();
	foreach ( $items as $it ) {
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => $it['q'],
			'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $it['a'] ),
		);
	}
	return se_json_ld( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $entities ) );
}
