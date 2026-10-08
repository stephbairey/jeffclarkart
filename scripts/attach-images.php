<?php
/**
 * Attach resized painting images to artworks as featured images.
 *
 * Matches files to artworks by the `file` key in artworks.json (exact basename, extension-insensitive),
 * cross-checks the YYYYMMDD in the filename against jca_year, sideloads into the media library,
 * sets alt text, and sets the featured image. Skips artworks that already have one unless --force.
 *
 * Run on the server from the WP docroot:
 *   ~/bin/wp eval-file scripts/attach-images.php /path/to/artworks.json /path/to/images [--force]
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( "Run via wp eval-file\n" );
}

[ $json_path, $img_dir ] = [ $args[0] ?? '', rtrim( $args[1] ?? '', '/' ) ];
$force = in_array( '--force', $args, true );
if ( ! is_readable( $json_path ) || ! is_dir( $img_dir ) ) {
	WP_CLI::error( 'Usage: attach-images.php artworks.json images_dir [--force]' );
}
$data = json_decode( file_get_contents( $json_path ), true );

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

// Index available files by lowercase stem.
$files = [];
foreach ( glob( $img_dir . '/*' ) as $f ) {
	$files[ strtolower( pathinfo( $f, PATHINFO_FILENAME ) ) ] = $f;
}

$index = jca_title_index();
$done  = $skipped = $missing = 0;

foreach ( $data['artworks'] as $a ) {
	$id = $index[ jca_normalize_title( $a['title'] ) ] ?? 0;
	if ( ! $id ) {
		WP_CLI::warning( "no post: {$a['title']}" );
		$missing++;
		continue;
	}
	if ( has_post_thumbnail( $id ) && ! $force ) {
		$skipped++;
		continue;
	}
	$stem = strtolower( pathinfo( $a['file'] ?? '', PATHINFO_FILENAME ) );
	if ( ! $stem || empty( $files[ $stem ] ) ) {
		WP_CLI::warning( "no file for: {$a['title']} (expected {$a['file']})" );
		$missing++;
		continue;
	}
	$path = $files[ $stem ];

	if ( preg_match( '/JCA (\d{4})\d{4}/', basename( $path ), $m ) && ! empty( $a['year'] ) && (int) $m[1] !== (int) $a['year'] ) {
		WP_CLI::warning( "year mismatch: {$a['title']} file says {$m[1]}, catalog says {$a['year']}" );
	}

	// Copy into a temp file so media_handle_sideload can move it.
	$tmp = wp_tempnam( basename( $path ) );
	copy( $path, $tmp );
	$file_array = [ 'name' => sanitize_file_name( basename( $path ) ), 'tmp_name' => $tmp ];
	$att_id     = media_handle_sideload( $file_array, $id, $a['title'] );
	if ( is_wp_error( $att_id ) ) {
		@unlink( $tmp );
		WP_CLI::warning( "{$a['title']}: " . $att_id->get_error_message() );
		continue;
	}
	update_post_meta( $att_id, '_wp_attachment_image_alt', jca_alt( $id ) );
	set_post_thumbnail( $id, $att_id );
	$done++;
	WP_CLI::log( "attached #{$att_id} → #{$id} {$a['title']}" );
}

WP_CLI::success( "Attached {$done}, skipped {$skipped} (already had image), missing {$missing}." );
