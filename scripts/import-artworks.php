<?php
/**
 * Import / update the catalog from data/artworks.json. Idempotent: matches on normalized title.
 *
 * Run on the server from the WP docroot:
 *   ~/bin/wp eval-file /path/to/import-artworks.php /path/to/artworks.json
 *
 * Does not touch featured images (see attach-images.php) or the post body.
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( "Run via wp eval-file\n" );
}

$json_path = $args[0] ?? '';
if ( ! $json_path || ! is_readable( $json_path ) ) {
	WP_CLI::error( 'Pass the path to artworks.json as the first argument.' );
}
$data = json_decode( file_get_contents( $json_path ), true );
if ( ! $data || empty( $data['artworks'] ) ) {
	WP_CLI::error( 'Could not parse artworks.json.' );
}

if ( ! function_exists( 'jca_normalize_title' ) ) {
	WP_CLI::error( 'JCA Catalog plugin is not active.' );
}

// Series terms.
foreach ( $data['series'] as $s ) {
	$t = term_exists( $s['slug'], 'series' );
	if ( ! $t ) {
		$t = wp_insert_term( $s['name'], 'series', [ 'slug' => $s['slug'], 'description' => $s['description'] ?? '' ] );
		WP_CLI::log( "series: created {$s['name']}" );
	}
}

$index   = jca_title_index();
$created = $updated = 0;

foreach ( $data['artworks'] as $a ) {
	$key    = jca_normalize_title( $a['title'] );
	$id     = $index[ $key ] ?? 0;
	$is_new = ! $id;
	if ( ! $id ) {
		$id = wp_insert_post( [
			'post_type'   => 'artwork',
			'post_status' => 'publish',
			'post_title'  => $a['title'],
			'post_name'   => sanitize_title( $a['title'] ),
		], true );
		if ( is_wp_error( $id ) ) {
			WP_CLI::warning( "{$a['title']}: " . $id->get_error_message() );
			continue;
		}
		$created++;
		$index[ $key ] = $id;
	} else {
		$updated++;
	}

	$meta = [
		'year'             => $a['year'] ?? '',
		'medium'           => $a['medium'] ?? 'Acrylic on Canvas',
		'height_in'        => $a['height_in'] ?? '',
		'width_in'         => $a['width_in'] ?? '',
		'framed_height_in' => $a['framed_height_in'] ?? '',
		'framed_width_in'  => $a['framed_width_in'] ?? '',
		'price'            => $a['price'] ?? '',
		'status'           => $a['status'] ?? 'available',
		'sold_note'        => $a['sold_note'] ?? '',
		'inventory_code'   => $a['inventory_code'] ?? '',
		'variant_group'    => $a['variant_group'] ?? '',
		'home_order'       => $a['home_order'] ?? '',
		'featured_order'   => $a['featured_order'] ?? '',
	];
	foreach ( $meta as $k => $v ) {
		if ( $v === null || $v === '' ) {
			delete_post_meta( $id, JCA_META . $k );
		} else {
			update_post_meta( $id, JCA_META . $k, (string) $v );
		}
	}
	// CMB2 checkbox convention: 'on' or absent.
	if ( ! empty( $a['featured_on_home'] ) ) {
		update_post_meta( $id, JCA_META . 'featured_on_home', 'on' );
	} else {
		delete_post_meta( $id, JCA_META . 'featured_on_home' );
	}
	if ( ! empty( $a['exhibition_history'] ) ) {
		update_post_meta( $id, JCA_META . 'exhibition_history', $a['exhibition_history'] );
	}

	wp_set_object_terms( $id, $a['series'] ?? [], 'series', false );
	WP_CLI::log( sprintf( '%-4s #%d %s', $is_new ? 'new' : 'ok', $id, $a['title'] ) );
}

WP_CLI::success( "Created {$created}, updated {$updated}." );
