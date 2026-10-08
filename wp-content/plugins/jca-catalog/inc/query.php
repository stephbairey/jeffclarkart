<?php
/**
 * Query helpers: catalog ordering, homepage selections, prev/next, variant grouping.
 */

defined( 'ABSPATH' ) || exit;

/** Shared ordering: newest year first, then title. Applied to artwork archives and series pages. */
add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_post_type_archive( 'artwork' ) || $q->is_tax( 'series' ) ) {
		$q->set( 'posts_per_page', -1 );
		jca_apply_catalog_order( $q );
		// Optional ?year=2026 filter on the grid.
		$year = isset( $_GET['year'] ) ? absint( $_GET['year'] ) : 0;
		if ( $year ) {
			$q->set( 'meta_query', array_merge( (array) $q->get( 'meta_query' ), [ [ 'key' => JCA_META . 'year', 'value' => $year, 'compare' => '=', 'type' => 'NUMERIC' ] ] ) );
		}
	}
	if ( $q->is_post_type_archive( 'exhibition' ) ) {
		$q->set( 'posts_per_page', -1 );
		$q->set( 'meta_key', JCA_META . 'start_date' );
		$q->set( 'orderby', [ 'meta_value' => 'DESC', 'title' => 'ASC' ] );
	}
} );

function jca_apply_catalog_order( WP_Query $q ): void {
	$q->set( 'meta_query', array_merge( (array) $q->get( 'meta_query' ), [
		'relation' => 'OR',
		'jca_year' => [ 'key' => JCA_META . 'year', 'compare' => 'EXISTS', 'type' => 'NUMERIC' ],
		[ 'key' => JCA_META . 'year', 'compare' => 'NOT EXISTS' ],
	] ) );
	$q->set( 'orderby', [ 'jca_year' => 'DESC', 'title' => 'ASC' ] );
}

/** All published artworks in catalog order (ids). Cached per request. */
function jca_catalog_ids( ?int $series_term_id = null ): array {
	static $cache = [];
	$key = (string) ( $series_term_id ?? 'all' );
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}
	$args = [
		'post_type'      => 'artwork',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	];
	if ( $series_term_id ) {
		$args['tax_query'] = [ [ 'taxonomy' => 'series', 'field' => 'term_id', 'terms' => $series_term_id ] ];
	}
	$q = new WP_Query( $args );
	jca_apply_catalog_order( $q );
	$q = new WP_Query( array_merge( $args, [ 'meta_query' => $q->get( 'meta_query' ), 'orderby' => $q->get( 'orderby' ) ] ) );
	return $cache[ $key ] = $q->posts;
}

/**
 * Prev/next within the current series (when the artwork has one) or the whole catalog.
 * Returns ['prev' => id|null, 'next' => id|null, 'scope' => 'series'|'catalog'].
 */
function jca_prev_next( int $post_id ): array {
	$terms = get_the_terms( $post_id, 'series' );
	$term  = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
	$ids   = jca_catalog_ids( $term ? $term->term_id : null );
	$i     = array_search( $post_id, $ids, true );
	if ( $i === false || count( $ids ) < 2 ) {
		$ids = jca_catalog_ids();
		$i   = array_search( $post_id, $ids, true );
		$term = null;
	}
	return [
		'prev'  => ( $i !== false && $i > 0 ) ? $ids[ $i - 1 ] : null,
		'next'  => ( $i !== false && $i < count( $ids ) - 1 ) ? $ids[ $i + 1 ] : null,
		'scope' => $term ? 'series' : 'catalog',
		'term'  => $term,
	];
}

/** Homepage collage: artworks flagged featured_on_home, keyed by slot 1..7. */
function jca_home_collage(): array {
	$q = new WP_Query( [
		'post_type'      => 'artwork',
		'post_status'    => 'publish',
		'posts_per_page' => 7,
		'no_found_rows'  => true,
		'meta_query'     => [
			[ 'key' => JCA_META . 'featured_on_home', 'value' => 'on' ],
		],
		'meta_key'       => JCA_META . 'home_order',
		'orderby'        => [ 'meta_value_num' => 'ASC', 'title' => 'ASC' ],
	] );
	$slots = [];
	$free  = 1;
	foreach ( $q->posts as $p ) {
		$slot = (int) jca_meta( $p->ID, 'home_order', 0 );
		if ( $slot < 1 || $slot > 7 || isset( $slots[ $slot ] ) ) {
			while ( isset( $slots[ $free ] ) && $free <= 7 ) {
				$free++;
			}
			$slot = $free;
		}
		if ( $slot <= 7 ) {
			$slots[ $slot ] = $p;
		}
	}
	ksort( $slots );
	return $slots;
}

/** Homepage featured row: up to three artworks ordered by featured_order. */
function jca_home_featured(): array {
	$q = new WP_Query( [
		'post_type'      => 'artwork',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
		'no_found_rows'  => true,
		'meta_query'     => [ [ 'key' => JCA_META . 'featured_order', 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC' ] ],
		'meta_key'       => JCA_META . 'featured_order',
		'orderby'        => [ 'meta_value_num' => 'ASC', 'title' => 'ASC' ],
	] );
	return $q->posts;
}

/**
 * Group a list of posts by variant_group. Ungrouped posts get key '' and keep catalog order.
 * Returns [ ['heading' => string|'', 'posts' => WP_Post[]], ... ] in first-appearance order.
 */
function jca_group_variants( array $posts ): array {
	$groups = [];
	foreach ( $posts as $p ) {
		$g = trim( (string) jca_meta( $p->ID, 'variant_group' ) );
		if ( ! isset( $groups[ $g ] ) ) {
			$groups[ $g ] = [ 'heading' => $g, 'posts' => [] ];
		}
		$groups[ $g ]['posts'][] = $p;
	}
	foreach ( $groups as &$g ) {
		if ( $g['heading'] !== '' ) {
			usort( $g['posts'], fn( $a, $b ) => strnatcasecmp( $a->post_title, $b->post_title ) );
		}
	}
	return array_values( $groups );
}

/** Distinct years present in the catalog, newest first (for the optional year filter). */
function jca_catalog_years(): array {
	global $wpdb;
	$years = $wpdb->get_col( $wpdb->prepare(
		"SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE pm.meta_key = %s AND p.post_type = 'artwork' AND p.post_status = 'publish' AND pm.meta_value <> '' ORDER BY pm.meta_value+0 DESC",
		JCA_META . 'year'
	) );
	return array_map( 'intval', $years );
}
