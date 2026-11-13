<?php
/** Dependency-free contract for the November contact/help package. */
define( 'ABSPATH', __DIR__ . '/wordpress-stub/' );

$root     = dirname( __DIR__ );
$settings = file_get_contents( $root . '/queen-alfalah-core/includes/class-qaf-core-settings.php' );
$contact  = file_get_contents( $root . '/queen-alfalah-core/includes/class-qaf-core-contact-help.php' );
$template = file_get_contents( $root . '/queen-alfalah/page-templates/template-contact.php' );
$css      = file_get_contents( $root . '/queen-alfalah/style.css' );

$check = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

foreach ( array( 'contact_general_email', 'contact_spmb_email', 'contact_curriculum_email', 'contact_student_email', 'contact_facilities_email', 'contact_industry_email', 'opening_hours' ) as $key ) {
	$check( false !== strpos( $settings, "'{$key}'" ), "Setting {$key} is missing." );
}
foreach ( array( 'render_units', 'render_faq', 'render_form', 'wp_verify_nonce', 'qaf_company', 'set_transient', 'wp_mail', 'hash_hmac' ) as $marker ) {
	$check( false !== strpos( $contact, $marker ), "Contact protection {$marker} is missing." );
}
$check( false === strpos( $contact, 'wp_insert_post' ) || false !== strpos( $contact, 'maybe_refresh_contact_page' ), 'Contact messages must not be inserted as posts.' );
$check( false !== strpos( $contact, 'tidak disimpan di basis data' ), 'The form must disclose that messages are not stored.' );
$check( false !== strpos( $template, 'QAF_Core_Contact_Help::render_units' ), 'The contact template must render unit routing.' );
$check( false !== strpos( $template, 'QAF_Core_Contact_Help::render_form' ), 'The contact template must render the help form.' );
$check( false !== strpos( $css, '.qaf-help-form__grid' ) && false !== strpos( $css, '@media (max-width: 640px)' ), 'The help form must have responsive styles.' );

fwrite( STDOUT, "PASS: contact routing, FAQ, privacy disclosure, anti-spam controls, and responsive presentation are present.\n" );
