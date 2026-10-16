<?php
/**
 * Dependency-free contracts for the non-destructive achievement dataset.
 */

define( 'ABSPATH', __DIR__ . '/wordpress-stub/' );

$plugin_root = dirname( __DIR__ ) . '/queen-alfalah-core';
$data        = require $plugin_root . '/includes/achievement-data.php';
$image_dir   = realpath( $plugin_root . '/assets/images/achievements' );
$importer    = file_get_contents( $plugin_root . '/includes/class-qaf-core-achievements.php' );

$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$legacy_source_ids = array(
	'instagram:CnUV7LqpRIS:prestasi-kelas-jurusan',
	'instagram:CsnJVpTpxAR:short-movie',
	'instagram:C0EaEMoJ964:fotografi',
	'instagram:C2Z0zR6pUky:peringkat-akademik',
	'instagram:C-sWJRpzQO1:lbb-putra',
	'instagram:C-sWJRpzQO1:lbb-putri',
	'instagram:DE4ySByJl8x:peringkat-paralel',
	'instagram:DJ5bZqXTYoe:video-lawatan-sejarah',
	'instagram:DL37-OhpfR2:video-edukatif',
	'instagram:DNAZwbsv_-P:peringkat-paralel',
	'instagram:DQPEpHHCTTb:juara-harapan-1',
	'instagram:DQPEpHHCTTb:juara-3',
	'instagram:DQjhO8PiUO5:sneakers-hand-painting',
	'instagram:DVBm49GiYcL:tka-bahasa-arab',
	'instagram:DVBm49GiYcL:lks-bilingual-secretary',
	'instagram:DVBm49GiYcL:lks-health-social-care',
	'instagram:DXnc-npCTrf:ecofest-manajemen-bisnis-syariah',
);

$new_source_ids = array(
	'youtube:KL-IKv7_t40:pais-love-festival-2024',
	'web:sk-188.4-038.2-101.6.14-2025:cad-bim',
	'web:usmart:olpain-2025-gelombang-2:gavin-rufes',
	'instagram:DbPlnsACQDD:festival-anak-sholeh-2026',
	'instagram:Dbf2FQ6CcIe:kelas-terbersih-terindah-2026',
	'instagram:Db8WxS0idWH:lbb-kabupaten-kediri-2026',
	'instagram:DcPzkO8iR0g:peringkat-1-lbb-kecamatan-mojo-2026',
	'instagram:DcVPSxuCQ8m:andhika-video-reels',
	'instagram:DcVPSxuCQ8m:masyfuq-video-reels',
	'instagram:DcVPSxuCQ8m:aqil-fotografi',
	'instagram:DcVPSxuCQ8m:jalaluddin-fotografi',
	'instagram:Dcm_rkFJZCy:semarak-lomba-17-agustus-2026',
	'instagram:DdlfOvYJTI8:mikael-fadh-english-olympiad',
	'instagram:DeIN3TVTdlC:figur-bagus-arabic-speech-finalist',
);

$participation_source_ids = array(
	'youtube:KL-IKv7_t40:pais-love-festival-2024',
	'web:usmart:olpain-2025-gelombang-2:gavin-rufes',
	'instagram:DbPlnsACQDD:festival-anak-sholeh-2026',
	'instagram:Db8WxS0idWH:lbb-kabupaten-kediri-2026',
	'instagram:Dcm_rkFJZCy:semarak-lomba-17-agustus-2026',
);

$assert( is_array( $data ), 'Achievement data must return an array.' );
$assert( '1.4.0' === $data['version'], 'Achievement data version must be 1.4.0.' );
$assert( '1.2.0' === $data['image_version'], 'Achievement image version must be 1.2.0.' );
$assert( isset( $data['items'] ) && is_array( $data['items'] ), 'Achievement items must be present.' );
$assert( 31 === count( $data['items'] ), 'Exactly 31 non-destructive achievement records are expected.' );
$assert( false !== $image_dir, 'Achievement image directory must exist.' );

$source_ids = array();
$slugs      = array();
$images     = array();
$image_posts = 0;
$items_by_source = array();

foreach ( $data['items'] as $index => $item ) {
	$prefix = "Record {$index}";
	$assert( is_array( $item ), "{$prefix} must be an array." );
	foreach ( array( 'source_id', 'title', 'slug', 'status', 'date', 'recipient', 'organizer', 'award', 'field', 'level', 'source_url', 'excerpt', 'content' ) as $field ) {
		$assert( array_key_exists( $field, $item ), "{$prefix} field {$field} is required." );
		if ( 'recipient' !== $field ) {
			$assert( is_string( $item[ $field ] ) && '' !== trim( $item[ $field ] ), "{$prefix} field {$field} must be non-empty text." );
		}
	}

	$assert( is_string( $item['recipient'] ) || is_array( $item['recipient'] ), "{$prefix} recipient must be text or a list." );
	$recipient_text = is_array( $item['recipient'] ) ? implode( ' ', $item['recipient'] ) : $item['recipient'];
	$assert( '' !== trim( $recipient_text ), "{$prefix} recipient must not be empty." );
	$assert( preg_match( '/^[A-Za-z0-9._:-]+$/', $item['source_id'] ), "{$prefix} source ID is not portable." );
	$assert( preg_match( '/^[a-z0-9-]+$/', $item['slug'] ), "{$prefix} slug is not portable." );
	$assert( in_array( $item['status'], array( 'draft', 'pending', 'private', 'publish' ), true ), "{$prefix} status is invalid." );
	$assert( 0 === strpos( $item['source_url'], 'https://' ), "{$prefix} source URL must use HTTPS." );

	$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $item['date'] );
	$assert( $date && $date->format( 'Y-m-d' ) === $item['date'], "{$prefix} date is invalid." );
	$assert( ! in_array( $item['source_id'], $source_ids, true ), "Duplicate source ID {$item['source_id']}." );
	$assert( ! in_array( $item['slug'], $slugs, true ), "Duplicate slug {$item['slug']}." );

	$source_ids[] = $item['source_id'];
	$slugs[]      = $item['slug'];
	$items_by_source[ $item['source_id'] ] = $item;

	if ( ! empty( $item['image'] ) ) {
		$image_posts++;
		$assert( is_string( $item['image'] ) && preg_match( '/^[a-z0-9-]+\.(?:jpe?g|png|webp)$/', $item['image'] ), "{$prefix} image path is invalid." );
		$assert( false === strpos( $item['image'], '..' ) && false === strpos( $item['image'], '/' ), "{$prefix} image must stay in the bundled directory." );
		$file = realpath( $image_dir . DIRECTORY_SEPARATOR . $item['image'] );
		$assert( false !== $file && is_file( $file ) && is_readable( $file ), "{$prefix} bundled image is missing." );
		$assert( filesize( $file ) > 10000, "{$prefix} bundled image is unexpectedly small." );
		$dimensions = getimagesize( $file );
		$assert( is_array( $dimensions ) && $dimensions[0] >= 600 && $dimensions[1] >= 250, "{$prefix} bundled image dimensions are too small." );
		$images[] = $item['image'];
	}
}

foreach ( array_merge( $legacy_source_ids, $new_source_ids ) as $source_id ) {
	$assert( isset( $items_by_source[ $source_id ] ), "Expected source {$source_id} is missing." );
}

foreach ( $participation_source_ids as $source_id ) {
	$item = $items_by_source[ $source_id ];
	$assert( false !== stripos( $item['title'], 'Partisipasi' ), "{$source_id} title must be labelled as participation." );
	$assert( false !== stripos( $item['award'], 'Partisip' ), "{$source_id} award must be labelled as participation." );
	$assert( false !== stripos( $item['content'], 'tidak' ), "{$source_id} content must explain the absence of a claimed rank." );
}

$lks_recipients = implode( ' ', $items_by_source['web:sk-188.4-038.2-101.6.14-2025:cad-bim']['recipient'] );
$assert( false !== strpos( $lks_recipients, 'Muhammad Hasby Ash-Shiddiqiy' ), 'LKS CAD BIM must include Muhammad Hasby Ash-Shiddiqiy.' );
$assert( false !== strpos( $lks_recipients, 'M. Labib Muzakky' ), 'LKS CAD BIM must include M. Labib Muzakky.' );

$paree_alt = 'Poster prestasi empat siswa DKV SMK Queen Al-Falah pada The Silent Heritage of District Paree 2026.';
foreach ( array(
	'instagram:DcVPSxuCQ8m:andhika-video-reels',
	'instagram:DcVPSxuCQ8m:masyfuq-video-reels',
	'instagram:DcVPSxuCQ8m:aqil-fotografi',
	'instagram:DcVPSxuCQ8m:jalaluddin-fotografi',
) as $source_id ) {
	$assert( isset( $items_by_source[ $source_id ]['image_alt'] ) && $paree_alt === $items_by_source[ $source_id ]['image_alt'], "{$source_id} must use the neutral shared-poster alt text." );
}

$olpain = $items_by_source['web:usmart:olpain-2025-gelombang-2:gavin-rufes'];
$assert( 'https://www.usmart.id/beranda/detail/olimpiade-pai' === $olpain['source_url'], 'OLPAIN must link to the public event page, not a result sheet containing examination identifiers.' );
$assert( false === strpos( $olpain['award'], '66' ) && false === strpos( $olpain['content'], 'nilai 66' ), 'OLPAIN must not publish a personal score.' );

$assert( 26 === $image_posts, 'Exactly 26 pages should receive a verified bundled image.' );
$assert( 23 === count( array_unique( $images ) ), 'Exactly 23 unique verified achievement image files should be referenced.' );
$assert( false === strpos( serialize( $data ), '0092501670' ), 'Private examination identifiers must not be stored in public achievement data.' );
$assert( false === strpos( serialize( $data ), 'Muhammad Masyfuq Fadli' ), 'The Paree participant name must follow the official poster spelling.' );
$assert( false !== strpos( serialize( $data ), 'Muhammad Masyfuq Faizi' ), 'The verified Paree participant spelling must be present.' );

$assert( false !== $importer, 'Achievement importer source must be readable.' );
$assert( false !== strpos( $importer, 'const LOCK_OPTION' ), 'Importer must define an atomic upgrade lock.' );
$assert( false !== strpos( $importer, 'add_option( self::LOCK_OPTION' ), 'Importer must claim its lock atomically.' );
$assert( false !== strpos( $importer, 'delete_exact_lock' ), 'Importer must release only the exact lock payload.' );
$assert( false !== strpos( $importer, '} finally {' ), 'Importer must always release its lock.' );
$assert( false !== strpos( $importer, '// Include posts in Trash' ), 'Importer must preserve deleted seeded records.' );
$assert( false !== strpos( $importer, "'trash' === get_post_status" ), 'Importer must skip trashed items during updates.' );
$assert( false !== strpos( $importer, 'has_post_thumbnail( $post_id )' ), 'Importer must preserve administrator-selected thumbnails.' );
$assert( false !== strpos( $importer, "isset( \$item['image_alt'] )" ), 'Importer must support neutral alt text for a shared poster.' );

fwrite( STDOUT, "PASS: 31 achievement records preserve all legacy sources and add the verified English Olympiad and Arabic Speech finalist records.\n" );
