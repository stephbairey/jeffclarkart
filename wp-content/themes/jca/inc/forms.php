<?php
/**
 * Ninja Forms integration: pre-fill the hidden "artwork" field from ?inquire=<id> resolved server-side.
 *
 * In Ninja Forms, give the hidden field the key `artwork` (Field Settings → Admin Label / Field Key).
 * Its default value stays empty; this filter supplies the title when the URL names a real, published artwork.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'ninja_forms_render_default_value', function ( $default_value, $field_type, $field_settings ) {
	if ( ( $field_settings['key'] ?? '' ) !== 'artwork' ) {
		return $default_value;
	}
	if ( ! function_exists( 'jca_contact_prefill_title' ) ) {
		return $default_value;
	}
	$title = jca_contact_prefill_title();
	return $title !== '' ? $title : $default_value;
}, 10, 3 );

/* Reply-to the submitter: set in the NF email action with {field:email}; nothing to do here. */

/** Render a Ninja Form by ID stored in theme options, with a fallback message when not configured. */
function jca_render_form( string $which ): void {
	$id = (int) get_option( "jca_form_{$which}", 0 );
	if ( $id && function_exists( 'Ninja_Forms' ) ) {
		echo do_shortcode( '[ninja_form id="' . $id . '"]' );
		return;
	}
	echo '<p class="page-plain__empty">Email <a href="mailto:' . esc_attr( jca_contact_email() ) . '">' . esc_html( jca_contact_email() ) . '</a>.</p>';
}

add_action( 'admin_init', function () {
	foreach ( [ 'contact' => 'Contact form (Ninja Forms ID)', 'commissions' => 'Commissions form (Ninja Forms ID)' ] as $key => $label ) {
		register_setting( 'general', "jca_form_{$key}", [ 'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 0 ] );
		add_settings_field( "jca_form_{$key}", $label, function () use ( $key ) {
			printf( '<input type="number" min="0" class="small-text" name="jca_form_%s" value="%d"> <span class="description">0 = show the email address instead</span>', esc_attr( $key ), (int) get_option( "jca_form_{$key}", 0 ) );
		}, 'general' );
	}
} );
