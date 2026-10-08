<?php
/**
 * Template helpers used by the theme. All read from post meta; nothing is derived from page content.
 */

defined( 'ABSPATH' ) || exit;

function jca_meta( int $post_id, string $key, $default = '' ) {
	$v = get_post_meta( $post_id, JCA_META . $key, true );
	return ( $v === '' || $v === null || $v === false ) ? $default : $v;
}

function jca_status( int $post_id ): string {
	$s = (string) jca_meta( $post_id, 'status', 'available' );
	return array_key_exists( $s, JCA_STATUSES ) ? $s : 'available';
}

/** "35.25" -> "35.25", "36" -> "36", "35.50" -> "35.5" */
function jca_fmt_in( $n ): string {
	if ( $n === '' || $n === null ) {
		return '';
	}
	$n = (float) $n;
	return rtrim( rtrim( number_format( $n, 2, '.', '' ), '0' ), '.' );
}

function jca_price_fmt( $price ): string {
	return '$' . number_format( (float) $price, 0, '.', ',' );
}

/** Dimensions line, height before width. Returns '' when either is missing. */
function jca_dimensions( int $post_id, bool $framed = false ): string {
	$h = jca_meta( $post_id, $framed ? 'framed_height_in' : 'height_in' );
	$w = jca_meta( $post_id, $framed ? 'framed_width_in' : 'width_in' );
	if ( $h === '' || $w === '' ) {
		return '';
	}
	return jca_fmt_in( $h ) . ' in × ' . jca_fmt_in( $w ) . ' in';
}

/** "Acrylic on Canvas, 35.25 in × 23.5 in" (medium only when no dimensions). */
function jca_medium_line( int $post_id ): string {
	$medium = (string) jca_meta( $post_id, 'medium', 'Acrylic on Canvas' );
	$dims   = jca_dimensions( $post_id );
	return $dims ? "{$medium}, {$dims}" : $medium;
}

/**
 * Price/status line per the display rules.
 * available → "$10,800" (or "Inquire" when price is blank); sold → "SOLD" + note; reserved → "RESERVED"; inquire → "Inquire".
 */
function jca_price_line( int $post_id ): string {
	$status = jca_status( $post_id );
	$price  = jca_meta( $post_id, 'price' );
	switch ( $status ) {
		case 'sold':
			$note = trim( (string) jca_meta( $post_id, 'sold_note' ) );
			return 'SOLD' . ( $note ? ', ' . $note : '' );
		case 'reserved':
			return 'RESERVED';
		case 'inquire':
			return 'Inquire';
		default:
			return $price !== '' ? jca_price_fmt( $price ) : 'Inquire';
	}
}

function jca_is_for_sale( int $post_id ): bool {
	return jca_status( $post_id ) === 'available';
}

/** Title with the year: used in captions as TITLE, YEAR. */
function jca_year( int $post_id ): string {
	return (string) jca_meta( $post_id, 'year' );
}

/** Alt text per the design handoff: "Title, year, acrylic on canvas". */
function jca_alt( int $post_id ): string {
	$parts = array_filter( [ get_the_title( $post_id ), jca_year( $post_id ), strtolower( (string) jca_meta( $post_id, 'medium', 'Acrylic on Canvas' ) ) ] );
	return implode( ', ', $parts );
}

/** Aspect ratio "w/h" from the featured image, falling back to painting dimensions, then 2/3. */
function jca_aspect( int $post_id ): string {
	$thumb = get_post_thumbnail_id( $post_id );
	if ( $thumb ) {
		$meta = wp_get_attachment_metadata( $thumb );
		if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
			return "{$meta['width']} / {$meta['height']}";
		}
	}
	$h = (float) jca_meta( $post_id, 'height_in' );
	$w = (float) jca_meta( $post_id, 'width_in' );
	if ( $h > 0 && $w > 0 ) {
		return "{$w} / {$h}";
	}
	return '2 / 3';
}

/** Caption block (figcaption inner HTML). */
function jca_caption( int $post_id ): string {
	$title = '<span class="jca-caption__title">' . esc_html( get_the_title( $post_id ) ) . '</span>';
	$year  = jca_year( $post_id );
	$line1 = $title . ( $year ? ', ' . esc_html( $year ) : '' );
	$out   = '<div class="jca-caption__line">' . $line1 . '</div>';
	$out  .= '<div class="jca-caption__line">' . esc_html( jca_medium_line( $post_id ) ) . '</div>';
	$out  .= '<div class="jca-caption__line jca-caption__price">' . esc_html( jca_price_line( $post_id ) ) . '</div>';
	return $out;
}

/** Exhibition history rows (array of ['venue','city','year','note']). */
function jca_exhibition_history( int $post_id ): array {
	$rows = get_post_meta( $post_id, JCA_META . 'exhibition_history', true );
	if ( ! is_array( $rows ) ) {
		return [];
	}
	return array_values( array_filter( $rows, fn( $r ) => is_array( $r ) && array_filter( $r ) ) );
}

/** Contact URL pre-filled by post ID (resolved server-side on the contact page). */
function jca_contact_url( int $post_id ): string {
	$contact = get_page_by_path( 'contact' );
	$base    = $contact ? get_permalink( $contact ) : home_url( '/contact/' );
	return add_query_arg( 'artwork', $post_id, $base );
}

/**
 * Resolve ?artwork=<id> on the contact page to an artwork title. Empty string for anything invalid.
 */
function jca_contact_prefill_title(): string {
	$id = isset( $_GET['artwork'] ) ? absint( $_GET['artwork'] ) : 0;
	if ( ! $id ) {
		return '';
	}
	$post = get_post( $id );
	if ( ! $post || $post->post_type !== 'artwork' || $post->post_status !== 'publish' ) {
		return '';
	}
	return get_the_title( $post );
}

function jca_contact_prefill_artwork(): ?WP_Post {
	$id = isset( $_GET['artwork'] ) ? absint( $_GET['artwork'] ) : 0;
	$post = $id ? get_post( $id ) : null;
	return ( $post && $post->post_type === 'artwork' && $post->post_status === 'publish' ) ? $post : null;
}
