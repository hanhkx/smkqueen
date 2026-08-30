<?php
/**
 * Complete extracurricular descriptions and transferable learning outcomes.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$qaf_extra_item = static function ( $slug, $title, $type, $intro, $benefits, $career, $coach = '', $location = '', $legacy_excerpt = '', $order = 0 ) {
	$benefit_html = '';
	foreach ( $benefits as $benefit ) {
		$benefit_html .= '<li>' . esc_html( $benefit ) . '</li>';
	}
	$career_html = '';
	foreach ( $career as $outcome ) {
		$career_html .= '<li>' . esc_html( $outcome ) . '</li>';
	}

	return array(
		'seed_key'      => 'extra:' . $slug,
		'title'         => $title,
		'slug'          => $slug,
		'status'        => 'publish',
		'menu_order'    => $order,
		'legacy_excerpt' => $legacy_excerpt,
		'excerpt'       => $intro,
		'content'       => '<p>' . esc_html( $intro ) . '</p><h2>Yang Dipelajari</h2><ul>' . $benefit_html . '</ul><h2>Bekal untuk Masa Depan</h2><ul>' . $career_html . '</ul><p>Kegiatan ini membantu membangun portofolio pengalaman dan keterampilan yang dapat terus dikembangkan. Keikutsertaan tidak merupakan jaminan pekerjaan atau sertifikasi tertentu.</p>',
		'meta'          => array_filter(
			array(
				'_qaf_coach'            => $coach,
				'_qaf_extra_location'   => $location,
				'_qaf_benefits'         => implode( "\n", $benefits ),
				'_qaf_career_relevance' => implode( "\n", $career ),
			)
		),
		'terms'         => array( 'qaf_extra_type' => array( $type ) ),
	);
};

return array(
	'version' => '1.0.0',
	'items'   => array(
		$qaf_extra_item(
			'pramuka',
			'Pramuka',
			'Kepemimpinan',
			'Kegiatan pembinaan karakter melalui latihan kepemimpinan, kemandirian, kerja sama, kepedulian, dan keterampilan lapangan.',
			array(
				'Kepemimpinan, disiplin, dan tanggung jawab pribadi',
				'Kerja tim, komunikasi, serta pemecahan masalah',
				'Kesiapsiagaan, pertolongan dasar, dan kepedulian lingkungan',
				'Kemampuan merencanakan serta menjalankan kegiatan kelompok',
			),
			array(
				'Mendukung kemampuan memimpin dan bekerja dalam tim lintas bidang',
				'Melatih ketahanan, adaptasi, serta pengambilan keputusan di bawah tekanan',
				'Membentuk kebiasaan kerja tertib, aman, dan bertanggung jawab',
				'Menjadi pengalaman organisasi untuk portofolio studi atau kerja',
			),
			'',
			'',
			'Pembinaan kepemimpinan, kemandirian, kerja sama, dan kepedulian sosial.',
			1
		),
		$qaf_extra_item(
			'broadcasting',
			'Broadcasting',
			'Kreativitas',
			'Ruang praktik produksi konten audiovisual, dokumentasi, penyiaran, dan komunikasi media dari tahap perencanaan hingga publikasi.',
			array(
				'Perencanaan naskah, rundown, dan alur produksi',
				'Dasar kamera, audio, pencahayaan, serta penyutradaraan',
				'Presentasi, wawancara, dan komunikasi di depan kamera',
				'Editing, manajemen berkas, hak cipta, dan etika publikasi',
			),
			array(
				'Relevan bagi pekerjaan produksi video, dokumentasi, media sosial, dan penyiaran',
				'Melatih kerja dengan tenggat, pembagian peran, dan standar kualitas',
				'Membantu membangun showreel atau portofolio karya audiovisual',
				'Menguatkan komunikasi profesional dan literasi media di berbagai profesi',
			),
			'Haniful Khalid, S.T.; Garina Rahmi Rahmani, S.Pd.',
			'Gedung Kebanan',
			'Ruang belajar produksi konten, penyiaran, dokumentasi, dan komunikasi media.',
			2
		),
		$qaf_extra_item(
			'futsal',
			'Futsal',
			'Olahraga',
			'Latihan olahraga beregu yang mengembangkan teknik bermain, kebugaran, sportivitas, komunikasi, dan pengambilan keputusan cepat.',
			array(
				'Teknik dasar, strategi permainan, dan pemahaman posisi',
				'Kebugaran, koordinasi, kelincahan, serta daya tahan',
				'Komunikasi tim, disiplin latihan, dan sportivitas',
				'Pengelolaan emosi dan evaluasi performa',
			),
			array(
				'Membangun stamina dan kebiasaan hidup aktif yang mendukung produktivitas',
				'Melatih kolaborasi, kepemimpinan situasional, dan respons cepat',
				'Mengajarkan konsistensi latihan serta penerimaan umpan balik',
				'Dapat mendukung minat lanjut pada olahraga, kepelatihan, atau pengelolaan kegiatan',
			),
			'Akbar Junaidi, S.Pd.',
			'Gedung Kebanan',
			'Kegiatan olahraga untuk melatih kebugaran, sportivitas, disiplin, dan kerja tim.',
			3
		),
		$qaf_extra_item(
			'al-banjari',
			'Al Banjari',
			'Keagamaan',
			'Pengembangan seni musik islami melalui latihan vokal, ritme rebana, kekompakan ansambel, adab, dan keberanian tampil.',
			array(
				'Teknik dasar vokal, ritme, tempo, dan permainan rebana',
				'Kekompakan kelompok, konsentrasi, serta kepekaan musikal',
				'Kepercayaan diri dan kesiapan tampil di depan audiens',
				'Apresiasi seni islami, adab, serta tanggung jawab terhadap peralatan',
			),
			array(
				'Menguatkan kemampuan tampil, mengelola acara, dan bekerja dalam tim seni',
				'Melatih ketelitian terhadap tempo, kualitas, dan koordinasi',
				'Membantu membangun rekam pengalaman atau portofolio pertunjukan',
				'Relevan untuk kegiatan seni, event, produksi audio, dan pelayanan komunitas',
			),
			'Siska Mala Sari, M.Pd. (Gedung Kraton); pendamping Gedung Kebanan menunggu konfirmasi',
			'Gedung Kraton dan Gedung Kebanan',
			'Pengembangan seni musik islami, kekompakan, dan kepercayaan diri dalam berkarya.',
			4
		),
		$qaf_extra_item(
			'tenis-meja',
			'Tenis Meja',
			'Olahraga',
			'Latihan olahraga individu dan ganda untuk mengembangkan ketangkasan, fokus, kebugaran, strategi, serta sportivitas.',
			array(
				'Teknik servis, pukulan, footwork, dan pengembalian bola',
				'Koordinasi mata-tangan, refleks, fokus, serta ketahanan',
				'Strategi pertandingan dan pembacaan pola lawan',
				'Sportivitas, disiplin latihan, dan evaluasi diri',
			),
			array(
				'Menguatkan konsentrasi, ketelitian, dan respons terhadap perubahan cepat',
				'Melatih pengelolaan tekanan serta ketekunan memperbaiki teknik',
				'Mendukung kebugaran untuk aktivitas belajar dan kerja',
				'Dapat menjadi dasar minat pada olahraga, kepelatihan, atau penyelenggaraan kompetisi',
			),
			'Moh. Diky Bahtiar, S.Pd.',
			'Gedung Kebanan',
			'Latihan ketangkasan, konsentrasi, kebugaran, dan sportivitas.',
			5
		),
		$qaf_extra_item(
			'bola-voli',
			'Bola Voli',
			'Olahraga',
			'Kegiatan olahraga beregu yang melatih teknik permainan, kebugaran, komunikasi, pembagian peran, serta tanggung jawab tim.',
			array(
				'Servis, passing, set-up, serangan, blok, dan rotasi',
				'Kekuatan, koordinasi, kelincahan, serta daya tahan',
				'Komunikasi, kepercayaan antarpemain, dan strategi beregu',
				'Sportivitas, disiplin, keselamatan, serta evaluasi pertandingan',
			),
			array(
				'Melatih koordinasi tim dan tanggung jawab terhadap peran',
				'Membangun kebugaran, ketahanan, dan konsistensi latihan',
				'Mengembangkan kepemimpinan serta komunikasi dalam situasi cepat',
				'Dapat mendukung minat lanjut pada olahraga, kepelatihan, atau manajemen kegiatan',
			),
			'Andika Ferdian Putra E., S.Pd.',
			'Gedung Kebanan',
			'Kegiatan olahraga beregu untuk melatih teknik, komunikasi, dan kerja sama.',
			6
		),
		$qaf_extra_item(
			'desain-web',
			'Desain Web',
			'Teknologi',
			'Ruang eksplorasi struktur halaman, antarmuka, pengalaman pengguna, konten digital, dan pembuatan portofolio web.',
			array(
				'Perencanaan struktur informasi, wireframe, dan alur pengguna',
				'Dasar HTML, CSS, desain responsif, serta aksesibilitas',
				'Pengelolaan konten, aset visual, domain, dan publikasi dasar',
				'Pengujian, umpan balik pengguna, dokumentasi, dan kerja berbasis proyek',
			),
			array(
				'Relevan untuk UI/UX, web design, content management, dan dukungan digital',
				'Membantu membangun portofolio proyek yang dapat ditinjau saat studi atau melamar magang',
				'Melatih komunikasi kebutuhan klien, pemecahan masalah, dan iterasi',
				'Menguatkan kolaborasi DKV dan TJKT dalam proyek digital',
			),
			'Agus Sutrisno, S.Kom.; Mohammad Ihwan Ngisomundin, S.Kom.; M. Aghisna Hadziqun Nuha, S.Tr.T.',
			'Gedung Kraton dan Gedung Kebanan',
			'Ruang eksplorasi antarmuka, struktur halaman web, kreativitas digital, dan portofolio.',
			7
		),
		$qaf_extra_item(
			'desain-canva',
			'Desain Canva',
			'Kreativitas Digital',
			'Kegiatan desain komunikasi visual menggunakan Canva untuk membuat materi informasi, presentasi, publikasi, dan konten media sosial.',
			array(
				'Hirarki visual, tata letak, tipografi, warna, dan konsistensi desain',
				'Pengolahan aset, template, presentasi, serta konten media sosial',
				'Penyusunan brief, revisi, ekspor, dan pengelolaan berkas',
				'Hak cipta, lisensi aset, aksesibilitas, dan etika komunikasi visual',
			),
			array(
				'Relevan untuk administrasi, pemasaran digital, desain konten, dan presentasi bisnis',
				'Membantu membangun portofolio komunikasi visual yang rapi',
				'Melatih ketelitian terhadap brief, identitas merek, dan tenggat',
				'Mendukung pekerjaan lintas jurusan yang membutuhkan materi visual profesional',
			),
			'Brilliantna Mumtazah, S.Pd.',
			'Gedung Kraton',
			'',
			8
		),
		$qaf_extra_item(
			'tata-rias',
			'Tata Rias',
			'Keterampilan',
			'Kegiatan keterampilan tata rias yang menekankan kebersihan, persiapan alat, pemilihan produk, estetika, komunikasi, dan pelayanan.',
			array(
				'Sanitasi alat, kebersihan tangan, keamanan produk, dan persiapan area kerja',
				'Dasar koreksi wajah, warna, rias natural, serta rias acara',
				'Konsultasi kebutuhan, etika pelayanan, dan komunikasi dengan klien',
				'Penataan alat, dokumentasi hasil, estimasi waktu, dan evaluasi',
			),
			array(
				'Relevan bagi layanan tata rias, industri kreatif, fotografi, acara, dan wirausaha',
				'Melatih standar kebersihan, pelayanan pelanggan, serta ketelitian',
				'Membantu membangun portofolio hasil kerja dengan izin model',
				'Mengembangkan perencanaan layanan, harga dasar, dan pengelolaan perlengkapan',
			),
			'Cecilia Arisca Pratiwi, S.Sos.; Juwita Puspita Sari',
			'Gedung Kraton',
			'',
			9
		),
		$qaf_extra_item(
			'seni-tari',
			'Seni Tari',
			'Seni',
			'Kegiatan seni gerak yang mengembangkan teknik tubuh, ekspresi, ritme, kebugaran, apresiasi budaya, dan kesiapan pertunjukan.',
			array(
				'Dasar gerak, ritme, ekspresi, pola lantai, dan koordinasi',
				'Kebugaran, kelenturan, disiplin latihan, serta kesadaran keselamatan',
				'Interpretasi karya, apresiasi budaya, dan kerja ansambel',
				'Perencanaan latihan, kostum, tata panggung, serta evaluasi pertunjukan',
			),
			array(
				'Menguatkan kepercayaan diri, komunikasi nonverbal, dan kemampuan tampil',
				'Relevan untuk seni pertunjukan, event, produksi konten, dan kegiatan budaya',
				'Melatih konsistensi, ketepatan waktu, dan koordinasi kelompok',
				'Membantu membangun dokumentasi karya atau portofolio pertunjukan',
			),
			'Della (Sanggar Tari)',
			'Gedung Kraton',
			'',
			10
		),
		$qaf_extra_item(
			'seni-lukis',
			'Seni Lukis',
			'Seni',
			'Ruang berkarya untuk mempelajari observasi, komposisi, warna, teknik media, proses kreatif, dan penyajian karya visual.',
			array(
				'Sketsa, observasi bentuk, komposisi, perspektif, warna, dan pencahayaan',
				'Eksplorasi media, alat, teknik sapuan, tekstur, dan finishing',
				'Pengembangan konsep, referensi, kritik karya, dan revisi',
				'Perawatan alat, dokumentasi, penamaan, serta penyajian karya',
			),
			array(
				'Relevan untuk ilustrasi, desain, mural, seni rupa, dekorasi, dan industri kreatif',
				'Membantu membangun portofolio proses serta hasil karya',
				'Melatih observasi, kesabaran, ketelitian, dan keberanian bereksperimen',
				'Mengembangkan kemampuan menerima brief dan menjelaskan keputusan visual',
			),
			'Pradita Ratna Arianti, S.Pd.',
			'Gedung Kebanan',
			'',
			11
		),
	),
);
