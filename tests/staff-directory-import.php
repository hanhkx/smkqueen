<?php
/**
 * Dependency-free behavior checks for the non-destructive staff importer.
 */

define( 'ABSPATH', __DIR__ . '/wordpress-stub/' );

class WP_Post {
	public $ID;
	public $post_type;
	public $post_status;
	public $post_name;
	public $post_title;
	public $post_excerpt;
	public $post_content;

	public function __construct( $values ) {
		foreach ( $values as $key => $value ) {
			$this->{$key} = $value;
		}
	}
}

class WP_Error {
	private $code;
	private $message;

	public function __construct( $code, $message ) {
		$this->code    = $code;
		$this->message = $message;
	}

	public function get_error_message() {
		return $this->message;
	}
}

$GLOBALS['qaf_staff_test_posts']          = array();
$GLOBALS['qaf_staff_test_meta']           = array();
$GLOBALS['qaf_staff_test_terms']          = array();
$GLOBALS['qaf_staff_test_insert_calls']   = array();
$GLOBALS['qaf_staff_test_term_calls']     = array();
$GLOBALS['qaf_staff_test_deleted_posts']  = array();
$GLOBALS['qaf_staff_test_fail_taxonomy']  = '';
$GLOBALS['qaf_staff_test_next_post_id']   = 100;

function qaf_staff_test_add_post( $id, $status, $slug, $title ) {
	$GLOBALS['qaf_staff_test_posts'][ $id ] = new WP_Post(
		array(
			'ID'           => $id,
			'post_type'    => 'qaf_teacher',
			'post_status'  => $status,
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_excerpt' => 'Teks suntingan admin ' . $id,
			'post_content' => 'Konten suntingan admin ' . $id,
		)
	);
}

function add_action() {}
function current_user_can() { return true; }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function absint( $value ) { return abs( (int) $value ); }
function wp_slash( $value ) { return $value; }
function wp_kses_post( $value ) { return (string) $value; }
function esc_html( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' ); }
function remove_accents( $value ) { return (string) $value; }
function wp_strip_all_tags( $value ) { return strip_tags( (string) $value ); }
function sanitize_text_field( $value ) {
	return trim( preg_replace( '/\s+/', ' ', strip_tags( is_scalar( $value ) ? (string) $value : '' ) ) );
}
function sanitize_textarea_field( $value ) {
	return trim( strip_tags( is_scalar( $value ) ? (string) $value : '' ) );
}
function sanitize_key( $value ) {
	return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $value ) );
}
function sanitize_title( $value ) {
	$value = strtolower( strip_tags( is_scalar( $value ) ? (string) $value : '' ) );
	$value = preg_replace( '/[^a-z0-9]+/', '-', $value );
	return trim( $value, '-' );
}
function get_post_stati() {
	return array(
		'publish' => 'publish',
		'future'  => 'future',
		'draft'   => 'draft',
		'pending' => 'pending',
		'private' => 'private',
		'trash'   => 'trash',
	);
}
function get_posts( $args = array() ) {
	unset( $args );
	return array_values( $GLOBALS['qaf_staff_test_posts'] );
}
function get_post_meta( $post_id, $key = '', $single = false ) {
	if ( ! isset( $GLOBALS['qaf_staff_test_meta'][ $post_id ] ) || ! array_key_exists( $key, $GLOBALS['qaf_staff_test_meta'][ $post_id ] ) ) {
		return $single ? '' : array();
	}

	$value = $GLOBALS['qaf_staff_test_meta'][ $post_id ][ $key ];
	return $single ? $value : ( is_array( $value ) ? $value : array( $value ) );
}
function wp_insert_post( $postarr, $wp_error = false ) {
	unset( $wp_error );
	$id = $GLOBALS['qaf_staff_test_next_post_id']++;
	$GLOBALS['qaf_staff_test_insert_calls'][] = $postarr;
	$GLOBALS['qaf_staff_test_posts'][ $id ]   = new WP_Post(
		array(
			'ID'           => $id,
			'post_type'    => isset( $postarr['post_type'] ) ? $postarr['post_type'] : 'post',
			'post_status'  => isset( $postarr['post_status'] ) ? $postarr['post_status'] : 'draft',
			'post_name'    => isset( $postarr['post_name'] ) ? $postarr['post_name'] : '',
			'post_title'   => isset( $postarr['post_title'] ) ? $postarr['post_title'] : '',
			'post_excerpt' => isset( $postarr['post_excerpt'] ) ? $postarr['post_excerpt'] : '',
			'post_content' => isset( $postarr['post_content'] ) ? $postarr['post_content'] : '',
		)
	);

	if ( isset( $postarr['meta_input'] ) && is_array( $postarr['meta_input'] ) ) {
		$GLOBALS['qaf_staff_test_meta'][ $id ] = $postarr['meta_input'];
	}

	return $id;
}
function wp_set_object_terms( $post_id, $terms, $taxonomy, $append = false ) {
	$GLOBALS['qaf_staff_test_term_calls'][] = array(
		'post_id'  => $post_id,
		'terms'    => $terms,
		'taxonomy' => $taxonomy,
		'append'   => $append,
	);

	if ( $taxonomy === $GLOBALS['qaf_staff_test_fail_taxonomy'] ) {
		return new WP_Error( 'term_failed', 'Taksonomi uji sengaja digagalkan.' );
	}

	$GLOBALS['qaf_staff_test_terms'][ $post_id ][ $taxonomy ] = $terms;
	return array( 1 );
}
function wp_delete_post( $post_id, $force_delete = false ) {
	unset( $force_delete );
	$post = isset( $GLOBALS['qaf_staff_test_posts'][ $post_id ] ) ? $GLOBALS['qaf_staff_test_posts'][ $post_id ] : false;
	$GLOBALS['qaf_staff_test_deleted_posts'][] = $post_id;
	unset(
		$GLOBALS['qaf_staff_test_posts'][ $post_id ],
		$GLOBALS['qaf_staff_test_meta'][ $post_id ],
		$GLOBALS['qaf_staff_test_terms'][ $post_id ]
	);
	return $post;
}

require dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-staff-directory.php';

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$record = static function ( $source_id, $title, $slug, $aliases = array(), $staff_type = 'Guru' ) {
	return array(
		'source_id'  => $source_id,
		'title'      => $title,
		'slug'       => $slug,
		'aliases'    => $aliases,
		'staff_type' => $staff_type,
		'role'       => 'Jabatan dari struktur organisasi',
		'subject'    => 'Unit Kerja Uji',
		'duty'       => 'Melaksanakan tugas sesuai struktur organisasi sekolah.',
		'order'      => 77,
		'status'     => 'publish',
	);
};

// Seed administrator-owned profiles covering every supported identity match.
qaf_staff_test_add_post( 10, 'publish', 'irkham-hendi-spd', 'Irkham Hendi, S.Pd.' );
qaf_staff_test_add_post( 11, 'publish', 'profil-waka-kesiswaan', 'M. Ida Nashrun Sakif, S.Kep., Ns.' );
qaf_staff_test_add_post( 12, 'publish', 'moh-saean-marzuki', 'Nama Waka Kurikulum Diedit Admin' );
qaf_staff_test_add_post( 13, 'private', 'profil-operator-lama', 'Nama Operator Diedit Admin' );
qaf_staff_test_add_post( 14, 'trash', 'efi-risca-ferbriana-wati', 'Efi Risca Ferbriana Wati, S.Pd.' );

$GLOBALS['qaf_staff_test_meta'][10] = array( '_qaf_role' => 'Jabatan lama pilihan admin' );
$GLOBALS['qaf_staff_test_meta'][11] = array( '_qaf_subject' => 'Bidang lama pilihan admin' );
$GLOBALS['qaf_staff_test_meta'][12] = array( '_qaf_order' => 12 );
$GLOBALS['qaf_staff_test_meta'][13] = array(
	QAF_Core_Staff_Directory::SOURCE_META => 'structure:brilliantna-mumtazah',
	'_qaf_role'                           => 'Operator hasil suntingan admin',
);
$GLOBALS['qaf_staff_test_meta'][14] = array( '_qaf_role' => 'Profil sengaja dihapus' );
$GLOBALS['qaf_staff_test_terms'][10]['qaf_staff_type'] = 'Kategori lama pilihan admin';

$existing_snapshots = array();
foreach ( array( 10, 11, 12, 13, 14 ) as $existing_id ) {
	$existing_snapshots[ $existing_id ] = serialize(
		array(
			$GLOBALS['qaf_staff_test_posts'][ $existing_id ],
			isset( $GLOBALS['qaf_staff_test_meta'][ $existing_id ] ) ? $GLOBALS['qaf_staff_test_meta'][ $existing_id ] : array(),
			isset( $GLOBALS['qaf_staff_test_terms'][ $existing_id ] ) ? $GLOBALS['qaf_staff_test_terms'][ $existing_id ] : array(),
		)
	);
}

$items = array(
	$record( 'structure:irkham-hendi', 'Irkham Hendi, S.Pd.', 'irkham-hendi' ),
	$record( 'structure:ida-nashrun-sakif', 'Ns. H. M. Ida Nashrun Sakif, M.Kep.', 'ns-h-m-ida-nashrun-sakif', array( 'M. Ida Nashrun Sakif, S.Kep., Ns.' ) ),
	$record( 'structure:moh-saean-marzuki', 'Moh. Saean Marzuki, S.Pd.', 'moh-saean-marzuki' ),
	$record( 'structure:brilliantna-mumtazah', 'Brilliantna Mumtazah, S.Pd.', 'brilliantna-mumtazah' ),
	$record( 'structure:efi-risca-ferbriana-wati', 'Efi Risca Ferbriana Wati, S.Pd.', 'efi-risca-ferbriana-wati' ),
	$record( 'structure:yusril-annajar', 'Yusril Annajar', 'yusril-annajar', array(), 'Tendik' ),
);

$insert = new ReflectionMethod( 'QAF_Core_Staff_Directory', 'insert_missing' );
$insert->setAccessible( true );
$errors = $insert->invoke( null, $items );

$assert( empty( $errors ), 'The initial append-only import should succeed.' );
$assert( 1 === count( $GLOBALS['qaf_staff_test_insert_calls'] ), 'Only the genuinely missing profile may be inserted.' );
$assert( 6 === count( $GLOBALS['qaf_staff_test_posts'] ), 'Canonical, alias, slug, source, and Trash matches must not be duplicated.' );
$assert( 'Yusril Annajar' === $GLOBALS['qaf_staff_test_insert_calls'][0]['post_title'], 'The importer inserted an unexpected profile.' );

foreach ( $existing_snapshots as $existing_id => $snapshot ) {
	$current = serialize(
		array(
			$GLOBALS['qaf_staff_test_posts'][ $existing_id ],
			isset( $GLOBALS['qaf_staff_test_meta'][ $existing_id ] ) ? $GLOBALS['qaf_staff_test_meta'][ $existing_id ] : array(),
			isset( $GLOBALS['qaf_staff_test_terms'][ $existing_id ] ) ? $GLOBALS['qaf_staff_test_terms'][ $existing_id ] : array(),
		)
	);
	$assert( $snapshot === $current, "Existing profile {$existing_id} must remain byte-for-byte unchanged." );
}

$new_id = 100;
$assert( isset( $GLOBALS['qaf_staff_test_posts'][ $new_id ] ), 'The missing Tendik profile should exist.' );
$assert( 'publish' === $GLOBALS['qaf_staff_test_posts'][ $new_id ]->post_status, 'The requested profile status should be preserved.' );
$assert( 'structure:yusril-annajar' === $GLOBALS['qaf_staff_test_meta'][ $new_id ][ QAF_Core_Staff_Directory::SOURCE_META ], 'Source metadata must be written on the new post.' );
$assert( 'Jabatan dari struktur organisasi' === $GLOBALS['qaf_staff_test_meta'][ $new_id ]['_qaf_role'], 'Role metadata must be written on the new post.' );
$assert( 'Unit Kerja Uji' === $GLOBALS['qaf_staff_test_meta'][ $new_id ]['_qaf_subject'], 'Unit metadata must be written on the new post.' );
$assert( 77 === $GLOBALS['qaf_staff_test_meta'][ $new_id ]['_qaf_order'], 'Ordering metadata must be written on the new post.' );
$assert( 'Tenaga Kependidikan' === $GLOBALS['qaf_staff_test_terms'][ $new_id ]['qaf_staff_type'], 'The Tendik term must be assigned only to the new post.' );
$assert( 'Unit Kerja Uji' === $GLOBALS['qaf_staff_test_terms'][ $new_id ]['qaf_subject'], 'The unit term must be assigned only to the new post.' );
$assert( 2 === count( $GLOBALS['qaf_staff_test_term_calls'] ), 'Exactly two taxonomy writes are expected for one new post.' );

// A repeated pass must recognize the record it just created and perform no writes.
$errors = $insert->invoke( null, $items );
$assert( empty( $errors ), 'The idempotent second pass should succeed.' );
$assert( 1 === count( $GLOBALS['qaf_staff_test_insert_calls'] ), 'The second pass must not insert another post.' );
$assert( 2 === count( $GLOBALS['qaf_staff_test_term_calls'] ), 'The second pass must not rewrite taxonomy terms.' );

// If either taxonomy assignment fails, the newly-created post and all of its data are rolled back.
$GLOBALS['qaf_staff_test_fail_taxonomy'] = 'qaf_subject';
$failed_item = $record( 'structure:taxonomy-failure', 'Profil Uji Kegagalan Taksonomi', 'profil-uji-kegagalan-taksonomi' );
$errors      = $insert->invoke( null, array( $failed_item ) );
$failed_id   = 101;

$assert( 1 === count( $errors ), 'A taxonomy failure must be reported to the upgrade routine.' );
$assert( in_array( $failed_id, $GLOBALS['qaf_staff_test_deleted_posts'], true ), 'A post with incomplete taxonomy must be force-deleted.' );
$assert( ! isset( $GLOBALS['qaf_staff_test_posts'][ $failed_id ] ), 'The failed post must not remain in the directory.' );
$assert( ! isset( $GLOBALS['qaf_staff_test_meta'][ $failed_id ] ), 'Metadata from the failed post must be rolled back.' );
$assert( ! isset( $GLOBALS['qaf_staff_test_terms'][ $failed_id ] ), 'A partially assigned taxonomy must be rolled back.' );

fwrite( STDOUT, "PASS: staff importer preserves canonical, alias, slug, source, and Trash matches; inserts only missing profiles; and rolls back taxonomy failures.\n" );
