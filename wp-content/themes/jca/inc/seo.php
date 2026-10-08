<?php
/**
 * SEO basics handled in-theme: titles, meta description, Open Graph / Twitter, sitemap tweaks, noindex on staging.
 */

defined( 'ABSPATH' ) || exit;

function jca_is_staging(): bool {
	return str_contains( home_url(), 'staging.' );
}

/** Meta description: explicit field, else trimmed content, else site tagline. */
function jca_meta_description(): string {
	$desc = '';
	if ( is_singular() ) {
		$id   = get_queried_object_id();
		$desc = (string) get_post_meta( $id, 'jca_meta_description', true );
		if ( ! $desc && is_singular( 'artwork' ) ) {
			$parts = array_filter( [ get_the_title( $id ), jca_year( $id ), jca_medium_line( $id ) ] );
			$desc  = implode( ', ', $parts ) . '. Original painting by Jeff Clark, Portland, Oregon.';
			$body  = wp_strip_all_tags( get_post_field( 'post_content', $id ) );
			if ( $body ) {
				$desc = wp_trim_words( $body, 28, '…' );
			}
		} elseif ( ! $desc ) {
			$desc = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $id ) ), 28, '…' );
		}
	} elseif ( is_tax( 'series' ) ) {
		$t    = get_queried_object();
		$desc = $t->description ? wp_trim_words( $t->description, 28, '…' ) : "Paintings from the series {$t->name} by Jeff Clark.";
	} elseif ( is_post_type_archive( 'artwork' ) ) {
		$desc = 'All original acrylic-on-canvas paintings by Jeff Clark, Portland, Oregon, with prices and availability.';
	}
	return $desc ?: (string) get_bloginfo( 'description' );
}

/** Share image: the artwork itself, else the first collage painting, else nothing. */
function jca_share_image(): string {
	if ( is_singular() && has_post_thumbnail() ) {
		return (string) wp_get_attachment_image_url( get_post_thumbnail_id(), 'jca-detail' );
	}
	$collage = function_exists( 'jca_home_collage' ) ? jca_home_collage() : [];
	foreach ( $collage as $p ) {
		if ( has_post_thumbnail( $p ) ) {
			return (string) wp_get_attachment_image_url( get_post_thumbnail_id( $p ), 'jca-detail' );
		}
	}
	return '';
}

add_action( 'wp_head', function () {
	$desc  = jca_meta_description();
	$title = wp_get_document_title();
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( [], $GLOBALS['wp']->request ?? '' ) );
	$img   = jca_share_image();

	echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	if ( jca_is_staging() ) {
		echo '<meta name="robots" content="noindex, nofollow">' . "\n";
	}
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	echo '<meta property="og:type" content="' . ( is_singular( 'artwork' ) ? 'article' : 'website' ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta property="og:locale" content="en_US">' . "\n";
	if ( $img ) {
		echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	}
	if ( is_singular( 'artwork' ) ) {
		jca_artwork_jsonld( get_queried_object_id() );
	}
}, 5 );

/** Schema.org VisualArtwork. */
function jca_artwork_jsonld( int $id ): void {
	$h = jca_meta( $id, 'height_in' );
	$w = jca_meta( $id, 'width_in' );
	$data = [
		'@context'    => 'https://schema.org',
		'@type'       => 'VisualArtwork',
		'name'        => get_the_title( $id ),
		'url'         => get_permalink( $id ),
		'creator'     => [ '@type' => 'Person', 'name' => 'Jeff Clark' ],
		'artMedium'   => jca_meta( $id, 'medium', 'Acrylic on Canvas' ),
		'artform'     => 'Painting',
		'dateCreated' => jca_year( $id ),
	];
	if ( $h !== '' ) {
		$data['height'] = [ '@type' => 'Distance', 'name' => jca_fmt_in( $h ) . ' in' ];
	}
	if ( $w !== '' ) {
		$data['width'] = [ '@type' => 'Distance', 'name' => jca_fmt_in( $w ) . ' in' ];
	}
	if ( has_post_thumbnail( $id ) ) {
		$data['image'] = wp_get_attachment_image_url( get_post_thumbnail_id( $id ), 'jca-detail' );
	}
	if ( jca_is_for_sale( $id ) && jca_meta( $id, 'price' ) !== '' ) {
		$data['offers'] = [ '@type' => 'Offer', 'price' => (string) (float) jca_meta( $id, 'price' ), 'priceCurrency' => 'USD', 'availability' => 'https://schema.org/InStock' ];
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}

/* Document title: "Title, Year – Jeff Clark Artworks" for artworks. */
add_filter( 'document_title_parts', function ( $parts ) {
	if ( is_singular( 'artwork' ) ) {
		$y = jca_year( get_queried_object_id() );
		$parts['title'] = get_the_title() . ( $y ? ", {$y}" : '' );
	}
	if ( is_post_type_archive( 'artwork' ) ) {
		$parts['title'] = 'Work';
	}
	return $parts;
} );
add_filter( 'document_title_separator', fn() => '–' );

/* Sitemap: keep artworks, series, pages, exhibitions; drop users and tags. */
add_filter( 'wp_sitemaps_add_provider', fn( $provider, $name ) => $name === 'users' ? false : $provider, 10, 2 );
add_filter( 'wp_sitemaps_taxonomies', fn( $tax ) => array_intersect_key( $tax, [ 'series' => 1 ] ) );
add_filter( 'wp_sitemaps_post_types', fn( $types ) => array_intersect_key( $types, [ 'page' => 1, 'artwork' => 1, 'exhibition' => 1 ] ) );
add_filter( 'wp_sitemaps_enabled', fn() => ! jca_is_staging() );
