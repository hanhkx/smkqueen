<?php
/**
 * Verified starter records for the facilities catalog.
 *
 * Quantities, capacities, conditions, inspection dates, and inventory details
 * are deliberately left empty until the school verifies them.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'version' => '1.0.0',
	'items'   => array(
		array(
			'seed_key'  => 'facility:gedung-kebanan',
			'title'     => 'Gedung Kebanan',
			'slug'      => 'gedung-kebanan',
			'status'    => 'publish',
			'menu_order' => 1,
			'excerpt'   => 'Lokasi kegiatan pembelajaran dan pengembangan diri SMK Queen Al-Falah di Kebanan.',
			'content'   => '<p>Gedung Kebanan digunakan sebagai salah satu lokasi kegiatan pembelajaran, layanan sekolah, dan pengembangan diri SMK Queen Al-Falah. Informasi ruang, kapasitas, serta dokumentasi terbaru dapat dilengkapi oleh pengelola Sarana Prasarana melalui editor WordPress.</p>',
			'meta'      => array(
				'_qaf_facility_location' => 'Kebanan, Ploso, Kecamatan Mojo, Kabupaten Kediri',
				'_qaf_facility_function' => "Pembelajaran dan layanan sekolah\nKegiatan pengembangan diri dan ekstrakurikuler",
				'_qaf_facility_manager'  => 'Bidang Sarana dan Prasarana',
			),
			'terms'     => array( 'qaf_facility_type' => array( 'Gedung Sekolah' ) ),
		),
		array(
			'seed_key'  => 'facility:gedung-kraton',
			'title'     => 'Gedung Kraton',
			'slug'      => 'gedung-kraton',
			'status'    => 'publish',
			'menu_order' => 2,
			'excerpt'   => 'Lokasi kegiatan pembelajaran dan pengembangan diri SMK Queen Al-Falah di Kraton.',
			'content'   => '<p>Gedung Kraton menjadi bagian dari fasilitas sekolah yang mendukung pembelajaran dan kegiatan pengembangan diri. Rincian ruang, inventaris, kapasitas, dan foto aktual dapat diperbarui langsung oleh pengelola sekolah.</p>',
			'meta'      => array(
				'_qaf_facility_location' => 'Kraton, Kecamatan Mojo, Kabupaten Kediri',
				'_qaf_facility_function' => "Pembelajaran dan layanan sekolah\nKegiatan pengembangan diri dan ekstrakurikuler",
				'_qaf_facility_manager'  => 'Bidang Sarana dan Prasarana',
			),
			'terms'     => array( 'qaf_facility_type' => array( 'Gedung Sekolah' ) ),
		),
		array(
			'seed_key'  => 'facility:laboratorium-komputer',
			'title'     => 'Laboratorium Komputer',
			'slug'      => 'laboratorium-komputer',
			'status'    => 'publish',
			'menu_order' => 3,
			'excerpt'   => 'Ruang praktik komputer untuk pembelajaran, asesmen, dan pengembangan kompetensi digital.',
			'content'   => '<p>Laboratorium Komputer mendukung praktik teknologi informasi, pembelajaran berbasis perangkat, asesmen digital, dan pengembangan kompetensi peserta didik. Penggunaan ruang mengikuti jadwal, prosedur keselamatan, dan arahan pengelola laboratorium.</p>',
			'meta'      => array(
				'_qaf_facility_function' => "Praktik komputer dan jaringan\nPembelajaran serta asesmen digital\nPengembangan kompetensi teknologi informasi",
				'_qaf_facility_access'   => 'Digunakan sesuai jadwal pembelajaran dan arahan pengelola laboratorium.',
				'_qaf_facility_manager'  => 'Pengelola Sarana Prasarana dan Laboratorium Komputer',
			),
			'terms'     => array( 'qaf_facility_type' => array( 'Laboratorium' ) ),
		),
		array(
			'seed_key'  => 'facility:perpustakaan-literasi',
			'title'     => 'Perpustakaan dan Ruang Literasi',
			'slug'      => 'perpustakaan-dan-ruang-literasi',
			'status'    => 'publish',
			'menu_order' => 4,
			'excerpt'   => 'Pusat sumber belajar, koleksi bacaan, dan kegiatan literasi warga sekolah.',
			'content'   => '<p>Perpustakaan dan Ruang Literasi menyediakan dukungan sumber belajar dan kegiatan membaca bagi warga sekolah. Koleksi, jam layanan, tata tertib, serta dokumentasi kegiatan dapat diperbarui oleh pengelola perpustakaan.</p>',
			'meta'      => array(
				'_qaf_facility_function' => "Layanan koleksi dan sumber belajar\nKegiatan membaca dan literasi sekolah\nDukungan pembelajaran mandiri",
				'_qaf_facility_access'   => 'Mengikuti jam layanan dan tata tertib perpustakaan.',
				'_qaf_facility_manager'  => 'Bidang Perpustakaan dan Literasi Sekolah',
			),
			'terms'     => array( 'qaf_facility_type' => array( 'Perpustakaan' ) ),
		),
		array(
			'seed_key'  => 'facility:uks',
			'title'     => 'Usaha Kesehatan Sekolah (UKS)',
			'slug'      => 'usaha-kesehatan-sekolah-uks',
			'status'    => 'publish',
			'menu_order' => 5,
			'excerpt'   => 'Ruang layanan awal kesehatan dan pembinaan perilaku hidup bersih serta sehat di sekolah.',
			'content'   => '<p>UKS mendukung pertolongan awal, edukasi kesehatan, dan pembiasaan perilaku hidup bersih serta sehat. Layanan sekolah ini tidak menggantikan pemeriksaan atau penanganan oleh fasilitas kesehatan profesional.</p>',
			'meta'      => array(
				'_qaf_facility_function' => "Pertolongan awal di lingkungan sekolah\nEdukasi kesehatan dan perilaku hidup bersih\nKoordinasi rujukan bila diperlukan",
				'_qaf_facility_access'   => 'Digunakan sesuai kebutuhan layanan awal dan arahan pembina UKS.',
				'_qaf_facility_manager'  => 'Pembina Usaha Kesehatan Sekolah',
			),
			'terms'     => array( 'qaf_facility_type' => array( 'Layanan Kesehatan Sekolah' ) ),
		),
		array(
			'seed_key'  => 'facility:kantin',
			'title'     => 'Kantin Sekolah',
			'slug'      => 'kantin-sekolah',
			'status'    => 'publish',
			'menu_order' => 6,
			'excerpt'   => 'Fasilitas layanan konsumsi bagi warga sekolah dengan perhatian pada kebersihan dan ketertiban.',
			'content'   => '<p>Kantin Sekolah mendukung kebutuhan konsumsi warga sekolah. Pengelolaan layanan mengutamakan kebersihan, keamanan pangan, ketertiban, dan kepatuhan terhadap ketentuan sekolah.</p>',
			'meta'      => array(
				'_qaf_facility_function' => "Layanan konsumsi warga sekolah\nDukungan kebutuhan harian selama kegiatan belajar",
				'_qaf_facility_access'   => 'Mengikuti jam layanan dan tata tertib sekolah.',
				'_qaf_facility_manager'  => 'Pengelola Kantin Sekolah',
			),
			'terms'     => array( 'qaf_facility_type' => array( 'Fasilitas Umum' ) ),
		),
	),
);
