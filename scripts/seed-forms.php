<?php
/**
 * Create the Contact and Commissions forms in Ninja Forms and wire their IDs into the theme options. Idempotent by title.
 *
 *   ~/bin/wp eval-file scripts/seed-forms.php contact
 *   ~/bin/wp eval-file scripts/seed-forms.php commissions
 *
 * One form per process: Ninja Forms' importer keeps state across calls inside a single request,
 * so importing two forms in one run merges them.
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( "Run via wp eval-file\n" );
}
if ( ! function_exists( 'Ninja_Forms' ) ) {
	WP_CLI::error( 'Ninja Forms is not active.' );
}

$to = get_option( 'jca_contact_email', 'jeffreyclark.fineart@gmail.com' );

function jca_nf_field( string $type, string $label, string $key, int $order, array $extra = [] ): array {
	return array_merge( [
		'type'        => $type,
		'label'       => $label,
		'key'         => $key,
		'order'       => $order,
		'label_pos'   => 'above',
		'required'    => 1,
		'container_class' => '',
		'element_class'   => '',
	], $extra );
}

function jca_nf_form( string $title, string $to, array $fields, string $subject, string $email_body ): array {
	return [
		'settings' => [
			'title'            => $title,
			'key'              => sanitize_title( $title ),
			'show_title'       => 0,
			'clear_complete'   => 1,
			'hide_complete'    => 1,
			'wrapper_class'    => '',
			'element_class'    => '',
			'add_submit'       => 0,
			'logged_in'        => 0,
			'currency'         => 'USD',
			'unique_field_error' => '',
		],
		'fields'  => $fields,
		'actions' => [
			[
				'type'   => 'save',
				'label'  => 'Store submission',
				'active' => 1,
			],
			[
				'type'      => 'email',
				'label'     => 'Email Jeff',
				'active'    => 1,
				'to'        => $to,
				'from_name' => '{field:name}',
				'reply_to'  => '{field:email}',
				'email_subject' => $subject,
				'cc'        => '',
				'bcc'       => '',
				'email_message' => $email_body,
				'email_format'  => 'html',
				'attach_csv'    => 0,
			],
			[
				'type'    => 'successmessage',
				'label'   => 'Success Message',
				'active'  => 1,
				'message' => "Thanks. Jeff reads every message and will write back to {field:email}.",
			],
		],
	];
}

$contact = jca_nf_form( 'Contact', $to, [
	jca_nf_field( 'textbox',  'Name',    'name',    1 ),
	jca_nf_field( 'email',    'Email',   'email',   2 ),
	jca_nf_field( 'hidden',   'Artwork', 'artwork', 3, [ 'required' => 0, 'default' => '' ] ),
	jca_nf_field( 'textarea', 'Message', 'message', 4, [ 'placeholder' => '' ] ),
	jca_nf_field( 'hp',       'Leave blank', 'hp', 5, [ 'required' => 0 ] ),
	jca_nf_field( 'submit',   'Send',    'submit',  6, [ 'required' => 0, 'processing_label' => 'Sending' ] ),
], 'Website inquiry: {field:artwork}', "<p><strong>{field:name}</strong> ({field:email})</p><p>About: {field:artwork}</p><p>{field:message}</p>" );

$commissions = jca_nf_form( 'Commissions', $to, [
	jca_nf_field( 'textbox',  'Name',    'name',    1 ),
	jca_nf_field( 'email',    'Email',   'email',   2 ),
	jca_nf_field( 'textarea', 'Message', 'message', 3 ),
	jca_nf_field( 'hp',       'Leave blank', 'hp', 4, [ 'required' => 0 ] ),
	jca_nf_field( 'submit',   'Send',    'submit',  5, [ 'required' => 0, 'processing_label' => 'Sending' ] ),
], 'Commission inquiry from {field:name}', "<p><strong>{field:name}</strong> ({field:email})</p><p>{field:message}</p>" );
// Note: on staging the Commissions page reuses the Contact form (jca_form_commissions = contact form id), per Jeff 2026-10-08.

$which = $args[0] ?? '';
$defs  = [ 'contact' => $contact, 'commissions' => $commissions ];
if ( ! isset( $defs[ $which ] ) ) {
	WP_CLI::error( 'Pass contact or commissions.' );
}
foreach ( [ $which => $defs[ $which ] ] as $opt => $def ) {
	$existing = (int) get_option( "jca_form_{$opt}", 0 );
	if ( $existing && Ninja_Forms()->form( $existing )->get()->get_id() ) {
		WP_CLI::log( "form: {$opt} exists (#{$existing})" );
		continue;
	}
	$id = Ninja_Forms()->form()->import_form( $def );
	if ( ! $id ) {
		WP_CLI::warning( "form: {$opt} import failed" );
		continue;
	}
	update_option( "jca_form_{$opt}", (int) $id );
	WP_CLI::log( "form: created {$opt} (#{$id})" );
}
WP_CLI::success( 'Forms seeded.' );
