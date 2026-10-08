<?php
/**
 * Admin list columns for artworks so Jeff can see year, size, price, status at a glance.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'manage_artwork_posts_columns', function ( $cols ) {
	$new = [];
	foreach ( $cols as $k => $v ) {
		if ( $k === 'title' ) {
			$new['jca_thumb'] = '';
		}
		$new[ $k ] = $v;
		if ( $k === 'title' ) {
			$new['jca_year']   = 'Year';
			$new['jca_size']   = 'H × W';
			$new['jca_price']  = 'Price';
			$new['jca_status'] = 'Status';
			$new['jca_home']   = 'Home';
		}
	}
	unset( $new['date'] );
	return $new;
} );

add_action( 'manage_artwork_posts_custom_column', function ( $col, $post_id ) {
	switch ( $col ) {
		case 'jca_thumb':
			echo get_the_post_thumbnail( $post_id, [ 48, 48 ], [ 'style' => 'width:48px;height:auto;display:block' ] );
			break;
		case 'jca_year':
			echo esc_html( jca_year( $post_id ) );
			break;
		case 'jca_size':
			echo esc_html( jca_dimensions( $post_id ) );
			break;
		case 'jca_price':
			$p = jca_meta( $post_id, 'price' );
			echo $p !== '' ? esc_html( jca_price_fmt( $p ) ) : '—';
			break;
		case 'jca_status':
			echo esc_html( JCA_STATUSES[ jca_status( $post_id ) ] ?? '' );
			break;
		case 'jca_home':
			$bits = [];
			if ( jca_meta( $post_id, 'featured_on_home' ) === 'on' ) {
				$bits[] = 'Collage ' . (int) jca_meta( $post_id, 'home_order' );
			}
			if ( (int) jca_meta( $post_id, 'featured_order' ) > 0 ) {
				$bits[] = 'Featured ' . (int) jca_meta( $post_id, 'featured_order' );
			}
			echo esc_html( implode( ', ', $bits ) );
			break;
	}
}, 10, 2 );

add_filter( 'manage_edit-artwork_sortable_columns', function ( $cols ) {
	$cols['jca_year']   = 'jca_year';
	$cols['jca_price']  = 'jca_price';
	$cols['jca_status'] = 'jca_status';
	return $cols;
} );

add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( ! is_admin() || ! $q->is_main_query() || $q->get( 'post_type' ) !== 'artwork' ) {
		return;
	}
	$map = [ 'jca_year' => 'meta_value_num', 'jca_price' => 'meta_value_num', 'jca_status' => 'meta_value' ];
	$ob  = $q->get( 'orderby' );
	if ( isset( $map[ $ob ] ) ) {
		$q->set( 'meta_key', JCA_META . substr( $ob, 4 ) );
		$q->set( 'orderby', $map[ $ob ] );
	}
} );

add_action( 'admin_head', function () {
	echo '<style>.column-jca_thumb{width:56px}.column-jca_year{width:60px}.column-jca_size{width:130px}.column-jca_price{width:90px}.column-jca_status{width:90px}.column-jca_home{width:120px}</style>';
} );
