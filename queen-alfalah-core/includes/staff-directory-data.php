<?php
/**
 * Versioned records for missing Guru and Tendik profiles.
 *
 * This dataset is append-only. The importer must use source IDs, names, aliases,
 * and slugs only to decide whether a profile is already represented; it must
 * never replace an existing administrator-managed profile.
 *
 * `profile_aliases` maps the canonical organization name to legacy titles from
 * the August 2026 WordPress export so existing portraits can be matched without
 * renaming or editing those profiles.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'version'         => '1.0.0',
	'profile_aliases' => array(
		'Efi Risca Ferbriana Wati, S.Pd.'       => array( 'Efi Risca Febriana Wati, S.Pd.' ),
		'Sunarto Abdillah, S.Hi.'                => array( 'Ust. Sunarto Abdillah, S.H.I.' ),
		'Mohammad Ihwan Ngisomundin, S.Kom.'     => array( 'Muhammad Ihwan Ngisomuddin, S.Kom.' ),
		'Ringga Fatma Hardiani, M.Pd.'           => array( 'Ringga Fatma Hardyani, M.Pd.' ),
		'Ns. H. M. Ida Nashrun Sakif, M.Kep.'    => array( 'Ns. Muhammad Ida Nashrun Sakif, M.Kep.' ),
		'Ida Rosyidah, S.Pd.'                    => array( 'Ida Rosydah, S.Pd.' ),
		'M. Aghisna Hadziqun Nuha, S.Tr.T.'      => array( 'Moh. Aghisna Hadzikunuha, S.Tr.Kom.' ),
		'Muhammad Husnul Hafizh Alfian, S.Pd.'   => array( 'Muhamad Husnul Hafizh Alfian, S.Pd.' ),
		'Sirajudin Ahmad, S.Kom.'                 => array( 'Shirajjudin Ahmad, S.Kom.' ),
		'Siska Mala Sari, M.Pd.'                  => array( 'Siska Malasari, M.Pd.' ),
		'Andika Ferdian Putra E., S.Pd.'          => array( 'Andhika Ferdian Putra Eriantara, S.Pd.' ),
	),
	'items'           => array(
		array(
			'source_id' => 'organization:2026-2027:irkham-hendi',
			'title'     => 'Irkham Hendi, S.Pd.',
			'slug'      => 'irkham-hendi-s-pd',
			'status'    => 'publish',
			'staff_type' => 'Guru',
			'role'      => 'Kepala Sekolah',
			'subject'   => 'Pimpinan Sekolah',
			'order'     => 1,
			'duty'      => 'Memimpin penyelenggaraan pendidikan, menetapkan arah pengembangan sekolah, mengoordinasikan seluruh bidang, serta mengendalikan mutu, tata kelola, dan evaluasi program sekolah.',
			'aliases'   => array(
				'Irkham Hendi, S.Pd',
				'Irkham Hendi S.Pd.',
			),
		),
		array(
			'source_id' => 'organization:2026-2027:nasywa-fauzia-zahro',
			'title'     => 'Nasywa Fauzia Zahro, S.Pd.',
			'slug'      => 'nasywa-fauzia-zahro-s-pd',
			'status'    => 'publish',
			'staff_type' => 'Guru',
			'role'      => 'Guru; Pendamping Kelas X; Wali Kelas X LK; Anggota BKK',
			'subject'   => 'Bimbingan Siswa dan Kelas X LK',
			'order'     => 57,
			'duty'      => 'Mendampingi perkembangan pribadi, sosial, belajar, dan perencanaan karier siswa kelas X; mengelola administrasi serta pembinaan Kelas X LK; dan mendukung layanan Bursa Kerja Khusus.',
			'aliases'   => array(
				'Nasywa Fauzia Zahro, S.Pd',
				'Nasywa Fauzia Zahro S.Pd.',
			),
		),
		array(
			'source_id' => 'organization:2026-2027:moh-diky-bahtiar',
			'title'     => 'Moh. Diky Bahtiar, S.Pd.',
			'slug'      => 'moh-diky-bahtiar-s-pd',
			'status'    => 'publish',
			'staff_type' => 'Guru',
			'role'      => 'Guru; Anggota TPPK; Wali Kelas X DKV 2; Pendamping Tenis Meja',
			'subject'   => 'TPPK, Kelas X DKV 2, dan Ekstrakurikuler Tenis Meja',
			'order'     => 58,
			'duty'      => 'Melaksanakan pembelajaran dan pembinaan kelas, membantu pencegahan serta penanganan kekerasan yang berpihak pada siswa, dan mendampingi pengembangan keterampilan serta sportivitas melalui tenis meja.',
			'aliases'   => array(
				'Moh Diky Bahtiar, S.Pd.',
				'Mohammad Diky Bahtiar, S.Pd.',
			),
		),
		array(
			'source_id' => 'organization:2026-2027:haniful-khalid',
			'title'     => 'Haniful Khalid, S.T.',
			'slug'      => 'haniful-khalid-s-t',
			'status'    => 'publish',
			'staff_type' => 'Guru',
			'role'      => 'Guru; Wali Kelas XII DKV 2; Pengelola Website Sekolah; Pendamping Broadcasting',
			'subject'   => 'DKV, Website Sekolah, dan Ekstrakurikuler Broadcasting',
			'order'     => 59,
			'duty'      => 'Melaksanakan pembelajaran dan pembinaan Kelas XII DKV 2, mengelola informasi serta pemeliharaan website sekolah, dan mendampingi siswa dalam produksi konten audiovisual dan broadcasting.',
			'aliases'   => array(
				'Haniful Khalid, S.T',
				'Haniful Khalid ST',
			),
		),
		array(
			'source_id' => 'organization:2026-2027:akbar-junaidi',
			'title'     => 'Akbar Junaidi, S.Pd.',
			'slug'      => 'akbar-junaidi-s-pd',
			'status'    => 'publish',
			'staff_type' => 'Guru',
			'role'      => 'Guru; Pendamping Futsal',
			'subject'   => 'Ekstrakurikuler Futsal',
			'order'     => 60,
			'duty'      => 'Mendampingi latihan futsal, membina teknik, kebugaran, disiplin, sportivitas, dan kerja sama tim, serta membantu kesiapan siswa mengikuti kegiatan atau pertandingan.',
			'aliases'   => array(
				'Akbar Junaidi, S.Pd',
				'Akbar Junaidi S.Pd.',
			),
		),
		array(
			'source_id' => 'organization:2026-2027:mohamad-yasin',
			'title'     => 'Mohamad Yasin',
			'slug'      => 'mohamad-yasin',
			'status'    => 'publish',
			'staff_type' => 'Tendik',
			'role'      => 'Satpam Gedung Kraton',
			'subject'   => 'Keamanan Gedung Kraton',
			'order'     => 101,
			'duty'      => 'Menjaga keamanan dan ketertiban Gedung Kraton, mengawasi akses lingkungan sekolah, membantu pelayanan tamu, dan menindaklanjuti gangguan sesuai prosedur sekolah.',
			'aliases'   => array( 'Mohammad Yasin' ),
		),
		array(
			'source_id' => 'organization:2026-2027:moh-arifin',
			'title'     => 'Drs. Moh. Arifin',
			'slug'      => 'drs-moh-arifin',
			'status'    => 'publish',
			'staff_type' => 'Tendik',
			'role'      => 'Pengatur Lalu Lintas',
			'subject'   => 'Keamanan dan Ketertiban Lalu Lintas',
			'order'     => 102,
			'duty'      => 'Membantu mengatur arus kedatangan dan kepulangan warga sekolah, menjaga keselamatan penyeberangan, serta mendukung ketertiban kendaraan di sekitar gerbang sekolah.',
			'aliases'   => array(
				'Moh. Arifin',
				'Mohammad Arifin',
			),
		),
		array(
			'source_id' => 'organization:2026-2027:ninik-kusmiati',
			'title'     => 'Ninik Kusmiati',
			'slug'      => 'ninik-kusmiati',
			'status'    => 'publish',
			'staff_type' => 'Tendik',
			'role'      => 'Tenaga Kebersihan dan Akomodasi Umum — Shift Pagi dan Sore',
			'subject'   => 'Kebersihan dan Akomodasi Umum',
			'order'     => 103,
			'duty'      => 'Menjaga kebersihan dan kerapian area kerja pada shift pagi dan sore, menyiapkan kebutuhan akomodasi umum, serta membantu lingkungan sekolah tetap sehat dan nyaman.',
			'aliases'   => array(),
		),
		array(
			'source_id' => 'organization:2026-2027:pujiono',
			'title'     => 'Pujiono',
			'slug'      => 'pujiono',
			'status'    => 'publish',
			'staff_type' => 'Tendik',
			'role'      => 'Tenaga Kebersihan dan Pesuruh Sekolah — Shift Pagi dan Sore; Anggota Program Sikap dan Asri',
			'subject'   => 'Kebersihan, Layanan Umum, dan Program Sikap dan Asri',
			'order'     => 104,
			'duty'      => 'Menjaga kebersihan pada shift pagi dan sore, membantu layanan operasional sebagai pesuruh sekolah, serta mendukung pelaksanaan lingkungan sekolah yang tertib, bersih, sehat, dan asri.',
			'aliases'   => array(),
		),
		array(
			'source_id' => 'organization:2026-2027:pujiyanto',
			'title'     => 'Pujiyanto',
			'slug'      => 'pujiyanto',
			'status'    => 'publish',
			'staff_type' => 'Tendik',
			'role'      => 'Tenaga Kebersihan — Shift Siang dan Sore',
			'subject'   => 'Kebersihan',
			'order'     => 105,
			'duty'      => 'Menjaga kebersihan, kerapian, dan kesiapan area sekolah pada shift siang dan sore serta melaporkan kebutuhan perawatan atau perlengkapan kebersihan.',
			'aliases'   => array(),
		),
		array(
			'source_id' => 'organization:2026-2027:abdul-wahid',
			'title'     => 'Abdul Wahid',
			'slug'      => 'abdul-wahid',
			'status'    => 'publish',
			'staff_type' => 'Tendik',
			'role'      => 'Penjaga Sekolah Malam dan Kebersihan Kamar Mandi',
			'subject'   => 'Keamanan Malam dan Kebersihan',
			'order'     => 106,
			'duty'      => 'Menjaga keamanan lingkungan sekolah pada malam hari, melakukan pemantauan rutin, dan memastikan kebersihan serta kelayakan kamar mandi sekolah.',
			'aliases'   => array(),
		),
		array(
			'source_id' => 'organization:2026-2027:halimatus-syadiah',
			'title'     => 'Halimatus Syadiah (Mba Diah)',
			'slug'      => 'halimatus-syadiah',
			'status'    => 'publish',
			'staff_type' => 'Tendik',
			'role'      => 'Penjaga Kantin',
			'subject'   => 'Layanan Kantin',
			'order'     => 107,
			'duty'      => 'Melayani kebutuhan kantin, menjaga kebersihan area dan peralatan, menata persediaan, serta mendukung penyediaan makanan dan minuman secara tertib.',
			'aliases'   => array(
				'Halimatus Syadiah',
				'Mba Diah',
			),
		),
		array(
			'source_id' => 'organization:2026-2027:almuznah',
			'title'     => 'Almuznah (Mba Al)',
			'slug'      => 'almuznah',
			'status'    => 'publish',
			'staff_type' => 'Tendik',
			'role'      => 'Penjaga Kantin',
			'subject'   => 'Layanan Kantin',
			'order'     => 108,
			'duty'      => 'Melayani kebutuhan kantin, menjaga kebersihan area dan peralatan, menata persediaan, serta mendukung penyediaan makanan dan minuman secara tertib.',
			'aliases'   => array(
				'Almuznah',
				'Mba Al',
			),
		),
	),
);
