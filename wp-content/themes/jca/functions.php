<?php
/**
 * Jeff Clark Artworks theme.
 */

defined( 'ABSPATH' ) || exit;

define( 'JCA_THEME_VERSION', '0.2.0' );

require_once get_theme_file_path( 'inc/seo.php' );
require_once get_theme_file_path( 'inc/forms.php' );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'script', 'style' ] );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	register_nav_menus( [
		'header' => 'Header (Work, About, Commissions, Contact)',
		'footer' => 'Footer links',
	] );
	// No big white block editor chrome colors; keep to tokens.
	add_theme_support( 'editor-color-palette', [
		[ 'name' => 'Charcoal', 'slug' => 'bg', 'color' => '#1F1E24' ],
		[ 'name' => 'Surface', 'slug' => 'surface', 'color' => '#2A2931' ],
		[ 'name' => 'Dust Grey', 'slug' => 'text', 'color' => '#D4D4D4' ],
		[ 'name' => 'Thistle', 'slug' => 'heading', 'color' => '#C8C2DB' ],
		[ 'name' => 'Amethyst Smoke', 'slug' => 'accent', 'color' => '#B290C5' ],
		[ 'name' => 'Bondi Blue', 'slug' => 'link', 'color' => '#5191A8' ],
	] );
	add_theme_support( 'disable-custom-colors' );
	add_theme_support( 'disable-custom-gradients' );
	add_theme_support( 'editor-gradient-presets', [] );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'jca-fonts', 'https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600&family=Alegreya+Sans:ital,wght@0,400;0,500;1,400&display=swap', [], null );
	wp_enqueue_style( 'jca-main', get_theme_file_uri( 'assets/main.css' ), [ 'jca-fonts' ], JCA_THEME_VERSION );
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'global-styles' );
} );

add_action( 'wp_head', function () {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1 );

/* Trim head clutter. */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
add_filter( 'emoji_svg_url', '__return_false' );
add_filter( 'xmlrpc_enabled', '__return_false' );

/* Default header nav when no menu is assigned yet. */
function jca_header_nav(): void {
	if ( has_nav_menu( 'header' ) ) {
		wp_nav_menu( [ 'theme_location' => 'header', 'container' => false, 'items_wrap' => '<ul>%3$s</ul>', 'depth' => 1 ] );
		return;
	}
	$news  = (int) get_option( 'page_for_posts' );
	$items = [
		'Work'        => get_post_type_archive_link( 'artwork' ),
		'About'       => home_url( '/about/' ),
		'News'        => $news ? get_permalink( $news ) : '',
		'Commissions' => home_url( '/commissions/' ),
		'Contact'     => home_url( '/contact/' ),
	];
	echo '<ul>';
	foreach ( array_filter( $items ) as $label => $url ) {
		$current = untrailingslashit( $url ) === untrailingslashit( jca_current_url() )
			|| ( $label === 'Work' && ( is_post_type_archive( 'artwork' ) || is_singular( 'artwork' ) || is_tax( 'series' ) ) )
			|| ( $label === 'News' && ( is_home() || is_singular( 'post' ) || is_category() || is_tag() || is_date() ) );
		echo '<li><a href="' . esc_url( $url ) . '"' . ( $current ? ' aria-current="page"' : '' ) . '>' . esc_html( $label ) . '</a></li>';
	}
	echo '</ul>';
}

function jca_current_url(): string {
	return home_url( add_query_arg( [], $GLOBALS['wp']->request ?? '' ) );
}

/* Site-wide contact details (editable under Settings → General via the options below). */
function jca_contact_email(): string {
	return (string) get_option( 'jca_contact_email', 'jeffreyclark.fineart@gmail.com' );
}
function jca_instagram_url(): string {
	return (string) get_option( 'jca_instagram_url', '' );
}
function jca_facebook_url(): string {
	return (string) get_option( 'jca_facebook_url', '' );
}

add_action( 'admin_init', function () {
	register_setting( 'general', 'jca_contact_email', [ 'type' => 'string', 'sanitize_callback' => 'sanitize_email', 'default' => 'jeffreyclark.fineart@gmail.com' ] );
	register_setting( 'general', 'jca_instagram_url', [ 'type' => 'string', 'sanitize_callback' => 'esc_url_raw', 'default' => '' ] );
	register_setting( 'general', 'jca_facebook_url', [ 'type' => 'string', 'sanitize_callback' => 'esc_url_raw', 'default' => '' ] );
	add_settings_field( 'jca_contact_email', 'Public contact email', fn() => printf( '<input type="email" class="regular-text" name="jca_contact_email" value="%s">', esc_attr( jca_contact_email() ) ), 'general' );
	add_settings_field( 'jca_instagram_url', 'Instagram URL', fn() => printf( '<input type="url" class="regular-text" name="jca_instagram_url" value="%s" placeholder="https://instagram.com/…">', esc_attr( jca_instagram_url() ) ), 'general' );
	add_settings_field( 'jca_facebook_url', 'Facebook URL', fn() => printf( '<input type="url" class="regular-text" name="jca_facebook_url" value="%s" placeholder="https://facebook.com/…">', esc_attr( jca_facebook_url() ) ), 'general' );
} );

/* Image markup helpers. */
function jca_image( int $post_id, string $size, array $attr = [] ): string {
	if ( ! has_post_thumbnail( $post_id ) ) {
		return '<div class="jca-placeholder" aria-hidden="true" style="aspect-ratio:' . esc_attr( jca_aspect( $post_id ) ) . ';background:var(--surface)"></div>';
	}
	$attr = array_merge( [ 'alt' => jca_alt( $post_id ), 'loading' => 'lazy', 'decoding' => 'async' ], $attr );
	return get_the_post_thumbnail( $post_id, $size, $attr );
}

/* Body class hooks for the two moods. */
add_filter( 'body_class', function ( $classes ) {
	$classes[] = is_front_page() ? 'mood-loud' : 'mood-quiet';
	return $classes;
} );

/* Footer menu walker: bare links, no list markup. */
class JCA_Bare_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_lvl( &$output, $depth = 0, $args = null ) {}
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$output .= '<a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a>';
	}
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}

/* Year filter link helper for the catalog head. */
function jca_filter_link( string $url, string $label, bool $current ): string {
	return '<a href="' . esc_url( $url ) . '"' . ( $current ? ' aria-current="page"' : '' ) . '>' . esc_html( $label ) . '</a>';
}
