<?php
/**
 * Plugin Name:       JCA Catalog
 * Plugin URI:        https://github.com/stephbairey/jeffclarkart
 * Description:       Artwork catalog for Jeff Clark Artworks: artwork and exhibition post types, series taxonomy, CMB2 fields, front-page fields, price sheet importer, template helpers.
 * Version:           0.1.0
 * Author:            Lingua Ink Media
 * Author URI:        https://linguainkmedia.com
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Requires Plugins:  cmb2
 * Text Domain:       jca
 */

defined( 'ABSPATH' ) || exit;

define( 'JCA_VERSION', '0.1.0' );
define( 'JCA_DIR', plugin_dir_path( __FILE__ ) );
define( 'JCA_URL', plugin_dir_url( __FILE__ ) );
define( 'JCA_META', 'jca_' );

require_once JCA_DIR . 'inc/post-types.php';
require_once JCA_DIR . 'inc/fields.php';
require_once JCA_DIR . 'inc/template-tags.php';
require_once JCA_DIR . 'inc/query.php';
require_once JCA_DIR . 'inc/admin-columns.php';
require_once JCA_DIR . 'inc/price-sheet.php';

register_activation_hook( __FILE__, function () {
	jca_register_post_types();
	jca_seed_series_terms();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

add_action( 'admin_notices', function () {
	if ( ! defined( 'CMB2_LOADED' ) && current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-error"><p><strong>JCA Catalog</strong> needs the CMB2 plugin active for the artwork fields to appear.</p></div>';
	}
} );
