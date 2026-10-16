<?php
/**
 * Dependency-free contract checks for the October content audit.
 */

define( 'ABSPATH', __DIR__ . '/wordpress-stub/' );

require dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-meta.php';
require dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-october-audit.php';

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$scope  = QAF_Core_October_Audit::get_scope();
$fields = QAF_Core_Meta::get_fields();
$expected_types = array( 'qaf_program', 'qaf_teacher', 'qaf_facility', 'qaf_extra' );

$assert( $expected_types === array_keys( $scope ), 'Audit scope must cover the four October priority content types in a stable order.' );

foreach ( $expected_types as $post_type ) {
	$assert( ! empty( $scope[ $post_type ]['label'] ), "{$post_type} must have an audit label." );
	$assert( ! empty( $scope[ $post_type ]['required_meta'] ), "{$post_type} must define required structured fields." );
	$assert( isset( $fields[ $post_type ]['_qaf_content_owner'] ), "{$post_type} must provide an editable content owner." );
	$assert( 'text' === $fields[ $post_type ]['_qaf_content_owner']['type'], "{$post_type} content owner must be text." );
	$assert( isset( $fields[ $post_type ]['_qaf_last_reviewed'] ), "{$post_type} must provide a last-reviewed date." );
	$assert( 'date' === $fields[ $post_type ]['_qaf_last_reviewed']['type'], "{$post_type} last-reviewed value must be a date." );

	foreach ( array_keys( $scope[ $post_type ]['required_meta'] ) as $meta_key ) {
		$assert( isset( $fields[ $post_type ][ $meta_key ] ), "{$post_type} audit field {$meta_key} must exist in the editor contract." );
	}
}

fwrite( STDOUT, "PASS: October audit covers Program Keahlian, Guru/Tendik, Sarana, and Ekstrakurikuler with owner and review-date governance fields.\n" );
