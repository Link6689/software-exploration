<?php
/**
 * Block patterns: one per page template of the design system. Insert one in
 * a new post/page, then pick the tools in each block's settings.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_block_pattern_category( 'software-exploration', array( 'label' => 'Software Exploration' ) );

		$h2 = function ( $text ) {
			return '<!-- wp:heading --><h2 class="wp-block-heading">' . $text . '</h2><!-- /wp:heading -->';
		};
		$p = function ( $text, $class = '' ) {
			$attr = $class ? ' {"className":"' . $class . '"}' : '';
			return '<!-- wp:paragraph' . $attr . ' --><p' . ( $class ? ' class="' . $class . '"' : '' ) . '>' . $text . '</p><!-- /wp:paragraph -->';
		};
		$b = function ( $name, $attrs = array() ) {
			return '<!-- wp:se/' . $name . ( $attrs ? ' ' . wp_json_encode( $attrs ) : '' ) . ' /-->';
		};
		// Grids and tables use the wide width when the page has a narrower content column.
		$w = function ( $name, $attrs = array() ) use ( $b ) {
			return $b( $name, array_merge( array( 'align' => 'wide' ), $attrs ) );
		};
		$faq = $b(
			'faq',
			array(
				'itemsEn' => "Q: First question readers search for?\nA: Answer in the first sentence.\n\nQ: Second question?\nA: Short, factual answer.",
				'itemsFr' => "Q: Première question que les lecteurs recherchent ?\nA: Répondez dès la première phrase.\n\nQ: Deuxième question ?\nA: Réponse courte et factuelle.",
			)
		);
		$head = $b( 'meta-line' ) . $b( 'disclosure' );

		$patterns = array(
			'review'       => array(
				'title'   => 'Review / test',
				'desc'    => 'Verdict, pros and cons, scores, test, pricing, alternatives, FAQ, author. Reader reviews follow automatically.',
				'content' => $head
					. $b( 'verdict-box' )
					. $h2( 'Pros and cons' ) . $b( 'pros-cons' )
					. $h2( 'Detailed scores' ) . $b( 'score-breakdown' )
					. $h2( 'Our test' ) . $p( 'What we did, with what, for how long, and what happened. Numbers beat adjectives.' )
					. '<!-- wp:image {"sizeSlug":"large","className":"se-shot-frame"} --><figure class="wp-block-image size-large se-shot-frame"><img alt=""/><figcaption class="wp-element-caption">Screenshot caption: what the reader is looking at.</figcaption></figure><!-- /wp:image -->'
					. $h2( 'Pricing' ) . $b( 'pricing' )
					. $h2( 'Alternatives' ) . $b( 'tool-grid', array( 'columns' => '2' ) )
					. $faq . $b( 'author-box' ),
			),
			'best-x'       => array(
				'title'   => 'Best X ranking',
				'desc'    => '"The 8 best AI writing tools": summary table, ranked list, method, FAQ.',
				'content' => $p( 'One paragraph: how many tools we tested, how, and for whom this ranking is.', 'se-lede' ) . $head
					. $h2( 'At a glance' ) . $w( 'comparison-table' )
					. $h2( 'Our ranking' ) . $w( 'ranked-list' )
					. $h2( 'How we chose' ) . $p( 'Criteria, weights and the test protocol, linked to the methodology page.' )
					. $h2( 'Buying guide' ) . $p( 'What to look for, in plain words.' )
					. $faq . $b( 'author-box' ),
			),
			'comparison'   => array(
				'title'   => 'Comparison of several tools',
				'desc'    => 'One large table with grouped rows, then a card per tool.',
				'content' => $p( 'Same test, several tools: what each does well, and where it breaks.', 'se-lede' ) . $head
					. $w( 'comparison-table', array( 'rows' => "## Features | Fonctionnalités\nExample feature | Fonction exemple | yes | no | yes\n## Our scores | Nos notes\nOutput quality | Qualité du rendu | score:9.0 | score:8.1 | score:7.6" ) )
					. $h2( 'Tool by tool' ) . $w( 'tool-grid', array( 'columns' => '2' ) )
					. $faq . $b( 'author-box' ),
			),
			'versus'       => array(
				'title'   => 'Versus (A vs B)',
				'desc'    => 'Two tools face to face, criterion by criterion.',
				'content' => $head . $w( 'versus', array( 'winner' => 'a' ) )
					. $h2( 'Quick verdict, criterion by criterion' ) . $w( 'comparison-table', array( 'showCta' => false, 'highlightEn' => 'Winner', 'highlightFr' => 'Gagnant' ) )
					. $h2( 'Final verdict' ) . $p( 'Who should pick which, in two or three sentences.' )
					. $faq . $b( 'author-box' ),
			),
			'alternatives' => array(
				'title'   => 'Alternatives to X',
				'desc'    => 'The tool readers want to replace, why, and the best replacements.',
				'content' => $p( 'Who this page is for: people who use X and hit one of its limits.', 'se-lede' ) . $head
					. $w( 'alternatives-hero' )
					. $h2( 'The best alternatives at a glance' ) . $w( 'comparison-table' )
					. $h2( 'Our picks' ) . $w( 'ranked-list' )
					. $h2( 'How to choose' ) . $p( 'Match the alternative to the reason you are leaving.' )
					. $faq . $b( 'author-box' ),
			),
			'deals'        => array(
				'title'   => 'Deals and promo codes',
				'desc'    => 'Every running deal; expired ones disappear by themselves.',
				'content' => $p( 'Discounts, extended trials and lifetime deals on tools we have tested. Checked every week.', 'se-lede' ) . $b( 'disclosure' )
					. $w( 'deal-grid' )
					. $h2( 'How we pick deals' ) . $p( 'Only tools we have reviewed, only offers we could verify, removed on expiry.' )
					. $faq,
			),
			'home'         => array(
				'title'   => 'Home page',
				'desc'    => 'Categories, top ranking, latest reviews, deals.',
				'content' => $p( 'Hands-on reviews, rankings and comparisons of AI, SEO and marketing tools.', 'se-lede' )
					. $h2( 'Browse by category' ) . $w( 'category-tiles', array(
						'itemsEn' => "Artificial intelligence | Writing, images, video, chatbots | spark | /ai/ | featured\nAI writing | Tested tools | pen | /best/ai-writing-tools/\nAI images | Tested tools | image | /best/ai-image-generators/\nAI video | Tested tools | video | /best/ai-video-generators/\nSEO | Rank tracking, audits, keywords, links | chart | /seo/ | featured\nKeyword research | Tested tools | search | /best/keyword-research-tools/\nMarketing | Email, social, ads, automation | mega | /marketing/ | featured\nEmail marketing | Tested tools | users | /best/email-marketing-tools/",
						'itemsFr' => "Intelligence artificielle | Rédaction, images, vidéo, chatbots | spark | /fr/ia/ | featured\nRédaction IA | Outils testés | pen | /fr/meilleurs/outils-redaction-ia/\nImages IA | Outils testés | image | /fr/meilleurs/generateurs-images-ia/\nVidéo IA | Outils testés | video | /fr/meilleurs/generateurs-video-ia/\nSEO | Positions, audits, mots-clés, netlinking | chart | /fr/seo/ | featured\nRecherche de mots-clés | Outils testés | search | /fr/meilleurs/outils-mots-cles/\nMarketing | E-mailing, social, pubs, automatisation | mega | /fr/marketing/ | featured\nE-mailing | Outils testés | users | /fr/meilleurs/outils-emailing/",
					) )
					. $h2( 'The best AI tools right now' ) . $w( 'ranked-list', array( 'compact' => true ) )
					. $h2( 'Latest reviews' ) . $w( 'tool-grid' )
					. $h2( 'Deals of the month' ) . $w( 'deal-grid', array( 'limit' => 3 ) )
					. $h2( 'Guides and tutorials' ) . '<!-- wp:latest-posts {"postsToShow":3,"displayPostContent":false,"displayFeaturedImage":true,"postLayout":"grid","columns":3,"className":"se-latest"} /-->',
			),
		);

		foreach ( $patterns as $slug => $pt ) {
			register_block_pattern(
				'se/' . $slug,
				array(
					'title'       => $pt['title'],
					'description' => $pt['desc'],
					'categories'  => array( 'software-exploration' ),
					'content'     => $pt['content'],
				)
			);
		}
	}
);
