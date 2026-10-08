<?php
/**
 * Post types and taxonomy.
 */

defined( 'ABSPATH' ) || exit;

function jca_register_post_types(): void {
	register_post_type( 'artwork', [
		'labels' => [
			'name'               => 'Artworks',
			'singular_name'      => 'Artwork',
			'add_new'            => 'Add Painting',
			'add_new_item'       => 'Add New Painting',
			'edit_item'          => 'Edit Painting',
			'new_item'           => 'New Painting',
			'view_item'          => 'View Painting',
			'search_items'       => 'Search Paintings',
			'not_found'          => 'No paintings found',
			'not_found_in_trash' => 'No paintings in Trash',
			'all_items'          => 'All Paintings',
			'menu_name'          => 'Artworks',
			'featured_image'     => 'Painting image',
			'set_featured_image' => 'Set painting image',
		],
		'public'              => true,
		'hierarchical'        => false,
		'has_archive'         => 'work',
		'rewrite'             => [ 'slug' => 'work', 'with_front' => false ],
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-art',
		'supports'            => [ 'title', 'editor', 'thumbnail', 'revisions' ],
		'show_in_rest'        => true,
		'taxonomies'          => [ 'series' ],
	] );

	register_taxonomy( 'series', 'artwork', [
		'labels' => [
			'name'          => 'Series',
			'singular_name' => 'Series',
			'all_items'     => 'All Series',
			'edit_item'     => 'Edit Series',
			'add_new_item'  => 'Add New Series',
			'menu_name'     => 'Series',
		],
		'public'            => true,
		'hierarchical'      => false,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => [ 'slug' => 'series', 'with_front' => false ],
	] );

	register_post_type( 'exhibition', [
		'labels' => [
			'name'          => 'Exhibitions',
			'singular_name' => 'Exhibition',
			'add_new_item'  => 'Add New Exhibition',
			'edit_item'     => 'Edit Exhibition',
			'all_items'     => 'All Exhibitions',
			'menu_name'     => 'Exhibitions',
		],
		'public'        => true,
		'hierarchical'  => false,
		'has_archive'   => 'exhibitions',
		'rewrite'       => [ 'slug' => 'exhibitions', 'with_front' => false ],
		'menu_position' => 6,
		'menu_icon'     => 'dashicons-calendar-alt',
		'supports'      => [ 'title', 'editor', 'thumbnail' ],
		'show_in_rest'  => true,
	] );
}
add_action( 'init', 'jca_register_post_types' );

function jca_seed_series_terms(): void {
	$terms = [
		'beautiful-oddities'   => 'Beautiful Oddities',
		'fragmented-identity'  => 'Fragmented Identity',
		'human-stories'        => 'Human Stories',
		'pop-elegies'          => "Pop Elegies: Death of the 80's",
	];
	foreach ( $terms as $slug => $name ) {
		if ( ! term_exists( $slug, 'series' ) ) {
			wp_insert_term( $name, 'series', [ 'slug' => $slug ] );
		}
	}
}

/**
 * Custom image sizes. The 3000px ingest is the "full" size.
 */
add_action( 'after_setup_theme', function () {
	add_image_size( 'jca-tile', 800, 0, false );
	add_image_size( 'jca-detail', 2000, 0, false );
}, 20 );

add_filter( 'image_size_names_choose', function ( $sizes ) {
	return $sizes + [ 'jca-tile' => 'Catalog tile', 'jca-detail' => 'Painting detail' ];
} );
