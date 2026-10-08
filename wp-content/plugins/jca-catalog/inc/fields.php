<?php
/**
 * CMB2 metaboxes: artwork fields, exhibition fields, front-page statement, page SEO.
 */

defined( 'ABSPATH' ) || exit;

const JCA_STATUSES = [
	'available' => 'Available',
	'sold'      => 'Sold',
	'reserved'  => 'Reserved',
	'inquire'   => 'Inquire (hide price)',
];

add_action( 'cmb2_admin_init', function () {
	$p = JCA_META;

	// ---- Artwork ----------------------------------------------------------
	$box = new_cmb2_box( [
		'id'           => 'jca_artwork_box',
		'title'        => 'Painting details',
		'object_types' => [ 'artwork' ],
		'context'      => 'normal',
		'priority'     => 'high',
		'show_in_rest' => WP_REST_Server::ALLMETHODS,
	] );

	$box->add_field( [ 'id' => $p . 'year', 'name' => 'Year', 'type' => 'text_small', 'attributes' => [ 'type' => 'number', 'min' => 1900, 'max' => 2100, 'step' => 1 ], 'sanitization_cb' => 'absint' ] );
	$box->add_field( [ 'id' => $p . 'medium', 'name' => 'Medium', 'type' => 'text_medium', 'default' => 'Acrylic on Canvas' ] );
	$box->add_field( [ 'id' => $p . 'height_in', 'name' => 'Height (in)', 'type' => 'text_small', 'desc' => 'Height first, then width (Portland Art Museum convention).', 'attributes' => [ 'type' => 'number', 'step' => '0.01', 'min' => 0 ], 'sanitization_cb' => 'jca_sanitize_decimal' ] );
	$box->add_field( [ 'id' => $p . 'width_in', 'name' => 'Width (in)', 'type' => 'text_small', 'attributes' => [ 'type' => 'number', 'step' => '0.01', 'min' => 0 ], 'sanitization_cb' => 'jca_sanitize_decimal' ] );
	$box->add_field( [ 'id' => $p . 'framed_height_in', 'name' => 'Framed height (in)', 'type' => 'text_small', 'desc' => 'Optional.', 'attributes' => [ 'type' => 'number', 'step' => '0.01', 'min' => 0 ], 'sanitization_cb' => 'jca_sanitize_decimal' ] );
	$box->add_field( [ 'id' => $p . 'framed_width_in', 'name' => 'Framed width (in)', 'type' => 'text_small', 'attributes' => [ 'type' => 'number', 'step' => '0.01', 'min' => 0 ], 'sanitization_cb' => 'jca_sanitize_decimal' ] );
	$box->add_field( [ 'id' => $p . 'price', 'name' => 'Price (USD)', 'type' => 'text_small', 'desc' => 'Whole dollars. Leave blank if there is no price yet.', 'attributes' => [ 'type' => 'number', 'step' => '1', 'min' => 0 ], 'sanitization_cb' => 'jca_sanitize_decimal' ] );
	$box->add_field( [ 'id' => $p . 'status', 'name' => 'Status', 'type' => 'select', 'options' => JCA_STATUSES, 'default' => 'available', 'desc' => 'Available shows the price. Sold shows SOLD. Reserved shows RESERVED. Inquire hides the price.' ] );
	$box->add_field( [ 'id' => $p . 'sold_note', 'name' => 'Sold note', 'type' => 'text_medium', 'desc' => 'e.g. "Private Collection". Shown after SOLD.' ] );
	$box->add_field( [ 'id' => $p . 'inventory_code', 'name' => 'Inventory code', 'type' => 'text_small', 'desc' => 'Optional.' ] );
	$box->add_field( [ 'id' => $p . 'variant_group', 'name' => 'Variant group', 'type' => 'text_medium', 'desc' => 'For sets of variants (Pop Elegies). Paintings with the same group name are shown together under that heading. Leave blank for standalone works.' ] );

	$ex = $box->add_field( [
		'id'      => $p . 'exhibition_history',
		'name'    => 'Exhibition history',
		'type'    => 'group',
		'options' => [ 'group_title' => 'Exhibition {#}', 'add_button' => 'Add exhibition', 'remove_button' => 'Remove', 'sortable' => true, 'closed' => true ],
		'desc'    => 'Where this painting has been shown. Optional.',
	] );
	$box->add_group_field( $ex, [ 'id' => 'venue', 'name' => 'Venue', 'type' => 'text' ] );
	$box->add_group_field( $ex, [ 'id' => 'city', 'name' => 'City', 'type' => 'text_medium' ] );
	$box->add_group_field( $ex, [ 'id' => 'year', 'name' => 'Year', 'type' => 'text_small' ] );
	$box->add_group_field( $ex, [ 'id' => 'note', 'name' => 'Note', 'type' => 'text' ] );

	// ---- Homepage placement (side box) -----------------------------------
	$home = new_cmb2_box( [
		'id'           => 'jca_artwork_home_box',
		'title'        => 'Homepage',
		'object_types' => [ 'artwork' ],
		'context'      => 'side',
		'priority'     => 'default',
		'show_in_rest' => WP_REST_Server::ALLMETHODS,
	] );
	$home->add_field( [ 'id' => $p . 'featured_on_home', 'name' => 'Show in the homepage collage', 'type' => 'checkbox' ] );
	$home->add_field( [ 'id' => $p . 'home_order', 'name' => 'Collage slot (1–7)', 'type' => 'text_small', 'desc' => 'Which collage position this painting takes. 1–5 always show; 6 and 7 only on desktop.', 'attributes' => [ 'type' => 'number', 'min' => 1, 'max' => 7, 'step' => 1 ], 'sanitization_cb' => 'absint' ] );
	$home->add_field( [ 'id' => $p . 'featured_order', 'name' => 'Featured row (1–3)', 'type' => 'text_small', 'desc' => 'Blank = not in the featured row under the statement.', 'attributes' => [ 'type' => 'number', 'min' => 1, 'max' => 3, 'step' => 1 ], 'sanitization_cb' => 'absint' ] );

	// ---- Exhibition --------------------------------------------------------
	$exb = new_cmb2_box( [
		'id'           => 'jca_exhibition_box',
		'title'        => 'Exhibition details',
		'object_types' => [ 'exhibition' ],
		'context'      => 'normal',
		'priority'     => 'high',
		'show_in_rest' => WP_REST_Server::ALLMETHODS,
	] );
	$exb->add_field( [ 'id' => $p . 'venue', 'name' => 'Venue', 'type' => 'text' ] );
	$exb->add_field( [ 'id' => $p . 'city', 'name' => 'City', 'type' => 'text_medium' ] );
	$exb->add_field( [ 'id' => $p . 'start_date', 'name' => 'Start date', 'type' => 'text_date', 'date_format' => 'Y-m-d' ] );
	$exb->add_field( [ 'id' => $p . 'end_date', 'name' => 'End date', 'type' => 'text_date', 'date_format' => 'Y-m-d' ] );
	$exb->add_field( [ 'id' => $p . 'note', 'name' => 'Note', 'type' => 'text' ] );
	$exb->add_field( [ 'id' => $p . 'link', 'name' => 'Link', 'type' => 'text_url' ] );

	// ---- Front page statement ----------------------------------------------
	$front = new_cmb2_box( [
		'id'           => 'jca_front_page_box',
		'title'        => 'Homepage statement',
		'object_types' => [ 'page' ],
		'show_on'      => [ 'key' => 'front-page', 'value' => true ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );
	$front->add_field( [ 'id' => $p . 'bio', 'name' => 'Bio paragraph', 'type' => 'textarea_small', 'desc' => 'One paragraph, larger type. Replace the placeholder with Jeff\'s own words.' ] );
	foreach ( [ 1, 2, 3 ] as $n ) {
		$front->add_field( [ 'id' => $p . "statement_{$n}_lead", 'name' => "Statement {$n}: lead-in", 'type' => 'text', 'desc' => 'Short opening sentence, rendered in the accent color.' ] );
		$front->add_field( [ 'id' => $p . "statement_{$n}_body", 'name' => "Statement {$n}: body", 'type' => 'textarea_small' ] );
	}

	// ---- SEO on pages and artworks --------------------------------------
	$seo = new_cmb2_box( [
		'id'           => 'jca_seo_box',
		'title'        => 'Search and sharing',
		'object_types' => [ 'page', 'artwork', 'exhibition' ],
		'context'      => 'normal',
		'priority'     => 'low',
	] );
	$seo->add_field( [ 'id' => $p . 'meta_description', 'name' => 'Meta description', 'type' => 'textarea_small', 'desc' => 'About 150 characters. Blank = generated from the content.', 'attributes' => [ 'maxlength' => 200 ] ] );
} );

/** Show-on callback: only the page set as the static front page. */
add_filter( 'cmb2_show_on', function ( $display, $meta_box ) {
	if ( ( $meta_box['show_on']['key'] ?? '' ) !== 'front-page' ) {
		return $display;
	}
	$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : ( isset( $_POST['post_ID'] ) ? (int) $_POST['post_ID'] : 0 );
	return $post_id && $post_id === (int) get_option( 'page_on_front' );
}, 10, 2 );

function jca_sanitize_decimal( $value ) {
	$value = trim( (string) $value );
	if ( $value === '' ) {
		return '';
	}
	$value = str_replace( [ ',', '$' ], '', $value );
	return is_numeric( $value ) ? (string) (float) $value : '';
}
