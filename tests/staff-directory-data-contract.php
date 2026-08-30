<?php
/**
 * Dependency-free contracts for missing Guru/Tendik profile data.
 */

define( 'ABSPATH', __DIR__ . '/wordpress-stub/' );

$data = require dirname( __DIR__ ) . '/queen-alfalah-core/includes/staff-directory-data.php';

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$expected_sources = array(
	'organization:2026-2027:irkham-hendi',
	'organization:2026-2027:nasywa-fauzia-zahro',
	'organization:2026-2027:moh-diky-bahtiar',
	'organization:2026-2027:haniful-khalid',
	'organization:2026-2027:akbar-junaidi',
	'organization:2026-2027:mohamad-yasin',
	'organization:2026-2027:moh-arifin',
	'organization:2026-2027:ninik-kusmiati',
	'organization:2026-2027:pujiono',
	'organization:2026-2027:pujiyanto',
	'organization:2026-2027:abdul-wahid',
	'organization:2026-2027:halimatus-syadiah',
	'organization:2026-2027:almuznah',
);

$expected_profile_aliases = array(
	'Efi Risca Ferbriana Wati, S.Pd.'       => 'Efi Risca Febriana Wati, S.Pd.',
	'Sunarto Abdillah, S.Hi.'                => 'Ust. Sunarto Abdillah, S.H.I.',
	'Mohammad Ihwan Ngisomundin, S.Kom.'     => 'Muhammad Ihwan Ngisomuddin, S.Kom.',
	'Ringga Fatma Hardiani, M.Pd.'           => 'Ringga Fatma Hardyani, M.Pd.',
	'Ns. H. M. Ida Nashrun Sakif, M.Kep.'    => 'Ns. Muhammad Ida Nashrun Sakif, M.Kep.',
	'Ida Rosyidah, S.Pd.'                    => 'Ida Rosydah, S.Pd.',
	'M. Aghisna Hadziqun Nuha, S.Tr.T.'      => 'Moh. Aghisna Hadzikunuha, S.Tr.Kom.',
	'Muhammad Husnul Hafizh Alfian, S.Pd.'   => 'Muhamad Husnul Hafizh Alfian, S.Pd.',
	'Sirajudin Ahmad, S.Kom.'                 => 'Shirajjudin Ahmad, S.Kom.',
	'Siska Mala Sari, M.Pd.'                  => 'Siska Malasari, M.Pd.',
	'Andika Ferdian Putra E., S.Pd.'          => 'Andhika Ferdian Putra Eriantara, S.Pd.',
);

$assert( is_array( $data ), 'Staff data must return an array.' );
$assert( isset( $data['version'] ) && '1.0.0' === $data['version'], 'Staff data version must be 1.0.0.' );
$assert( isset( $data['items'] ) && is_array( $data['items'] ), 'Staff items must be present.' );
$assert( 13 === count( $data['items'] ), 'Exactly 13 missing internal profiles are expected.' );
$assert( isset( $data['profile_aliases'] ) && is_array( $data['profile_aliases'] ), 'Profile aliases must be present.' );
$assert( 11 === count( $data['profile_aliases'] ), 'Exactly 11 WXR profile aliases are expected.' );

$sources     = array();
$slugs       = array();
$titles      = array();
$orders      = array();
$staff_count = array( 'Guru' => 0, 'Tendik' => 0 );
$required    = array( 'source_id', 'title', 'slug', 'status', 'staff_type', 'role', 'subject', 'order', 'duty', 'aliases' );

foreach ( $data['items'] as $index => $item ) {
	$prefix = "Record {$index}";
	$assert( is_array( $item ), "{$prefix} must be an array." );
	foreach ( $required as $field ) {
		$assert( array_key_exists( $field, $item ), "{$prefix} field {$field} is required." );
	}

	foreach ( array( 'source_id', 'title', 'slug', 'status', 'staff_type', 'role', 'subject', 'duty' ) as $field ) {
		$assert( is_string( $item[ $field ] ) && '' !== trim( $item[ $field ] ), "{$prefix} field {$field} must be non-empty text." );
	}
	$assert( preg_match( '/^[A-Za-z0-9._:-]+$/', $item['source_id'] ), "{$prefix} source ID is not portable." );
	$assert( preg_match( '/^[a-z0-9-]+$/', $item['slug'] ), "{$prefix} slug is not portable." );
	$assert( 'publish' === $item['status'], "{$prefix} must be published." );
	$assert( isset( $staff_count[ $item['staff_type'] ] ), "{$prefix} staff type must be Guru or Tendik." );
	$assert( is_int( $item['order'] ) && $item['order'] > 0, "{$prefix} order must be a positive integer." );
	$assert( is_array( $item['aliases'] ), "{$prefix} aliases must be an array." );

	foreach ( $item['aliases'] as $alias ) {
		$assert( is_string( $alias ) && '' !== trim( $alias ), "{$prefix} aliases must contain only non-empty text." );
	}

	$assert( ! in_array( $item['source_id'], $sources, true ), "Duplicate source ID {$item['source_id']}." );
	$assert( ! in_array( $item['slug'], $slugs, true ), "Duplicate slug {$item['slug']}." );
	$assert( ! in_array( $item['title'], $titles, true ), "Duplicate title {$item['title']}." );
	$assert( ! in_array( $item['order'], $orders, true ), "Duplicate display order {$item['order']}." );

	$sources[] = $item['source_id'];
	$slugs[]   = $item['slug'];
	$titles[]  = $item['title'];
	$orders[]  = $item['order'];
	$staff_count[ $item['staff_type'] ]++;
}

$assert( $expected_sources === $sources, 'The ordered list of 13 audited source records has changed.' );
$assert( 5 === $staff_count['Guru'], 'Exactly five missing Guru profiles are expected.' );
$assert( 8 === $staff_count['Tendik'], 'Exactly eight missing Tendik profiles are expected.' );

foreach ( $expected_profile_aliases as $canonical => $legacy_title ) {
	$assert( isset( $data['profile_aliases'][ $canonical ] ), "Missing canonical profile alias for {$canonical}." );
	$aliases = $data['profile_aliases'][ $canonical ];
	$assert( is_array( $aliases ) && array( $legacy_title ) === $aliases, "Unexpected WXR alias mapping for {$canonical}." );
}

$serialized = serialize( $data );
$assert( false === stripos( $serialized, 'Della dari Sanggar Tari' ), 'The external dance instructor must not be imported as Guru/Tendik.' );
$assert( false === stripos( $serialized, 'Seluruh Wali Kelas' ), 'Collective organization entries must not become profiles.' );
$assert( false === stripos( $serialized, 'Seluruh Kepala Program Keahlian' ), 'Collective organization entries must not become profiles.' );

fwrite( STDOUT, "PASS: 13 missing internal profiles (5 Guru and 8 Tendik) and 11 WXR aliases are append-only and publication-ready.\n" );
