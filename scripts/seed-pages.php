<?php
/**
 * Create the Phase 1 pages, set the static front page, seed the homepage placeholder statement,
 * and write the Settings → General options. Idempotent.
 *
 *   ~/bin/wp eval-file scripts/seed-pages.php
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( "Run via wp eval-file\n" );
}

function jca_seed_page( string $slug, string $title, string $content = '', string $template = '' ): int {
	$page = get_page_by_path( $slug );
	if ( $page ) {
		WP_CLI::log( "page: exists /{$slug}/ (#{$page->ID})" );
		return $page->ID;
	}
	$id = wp_insert_post( [ 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'post_content' => $content ] );
	if ( $template ) {
		update_post_meta( $id, '_wp_page_template', $template );
	}
	WP_CLI::log( "page: created /{$slug}/ (#{$id})" );
	return $id;
}

$home = jca_seed_page( 'home', 'Home' );
jca_seed_page( 'about', 'About', "<!-- wp:paragraph --><p>Placeholder. Jeff's bio goes here.</p><!-- /wp:paragraph -->", 'page-about.php' );
jca_seed_page( 'commissions', 'Commissions', "<!-- wp:paragraph --><p>Jeff takes a small number of commissions each year. Send a message to get the conversation started.</p><!-- /wp:paragraph -->", 'page-commissions.php' );
jca_seed_page( 'exhibitions', 'Exhibitions', '' );
jca_seed_page( 'contact', 'Contact', "<!-- wp:paragraph --><p>Questions about a painting, a studio visit, or anything else: write to Jeff directly.</p><!-- /wp:paragraph -->", 'page-contact.php' );

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home );
update_option( 'page_for_posts', 0 );

// Placeholder statement copy from the design package, flagged as placeholder.
if ( ! get_post_meta( $home, 'jca_bio', true ) ) {
	update_post_meta( $home, 'jca_bio', 'PLACEHOLDER — Jeff Clark paints people the way a cartoonist remembers them and a cubist rebuilds them: purple and blue bodies, outsized eyes, flat fields of color held in place by heavy outlines. He works in acrylic on canvas in Portland, Oregon.' );
	update_post_meta( $home, 'jca_statement_1_lead', 'Ugly is a way in.' );
	update_post_meta( $home, 'jca_statement_1_body', "The faces aren't flattering and aren't meant to be. Something true turns up when the proportions go wrong." );
	update_post_meta( $home, 'jca_statement_2_lead', 'Color goes on flat.' );
	update_post_meta( $home, 'jca_statement_2_body', 'Each area is one decision: mixed, laid down, left alone.' );
	update_post_meta( $home, 'jca_statement_3_lead', 'The line holds it together.' );
	update_post_meta( $home, 'jca_statement_3_body', 'Outlines come first and stay heavy, so the color has something to push against.' );
	WP_CLI::log( 'front page: placeholder statement seeded' );
}

update_option( 'blogname', 'Jeff Clark Artworks' );
update_option( 'blogdescription', 'Original acrylic-on-canvas paintings by Jeff Clark, Portland, Oregon.' );
update_option( 'timezone_string', 'America/Los_Angeles' );
update_option( 'date_format', 'F j, Y' );
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'jca_contact_email', 'jjdclark@gmail.com' );
update_option( 'big_image_size_threshold', 3000 );
update_option( 'default_comment_status', 'closed' );
update_option( 'default_ping_status', 'closed' );
update_option( 'blog_public', str_contains( home_url(), 'staging.' ) ? 0 : 1 );

flush_rewrite_rules();
WP_CLI::success( 'Pages and options seeded.' );
