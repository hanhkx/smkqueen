<?php
/**
 * Industry-partner profiles researched in July 2026.
 *
 * Public profile facts are separated from the cooperation statement supplied
 * by the school. No item claims a currently active MoU, placement quota, or
 * employment guarantee without a school document supporting that claim.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$qaf_partner_item = static function ( $slug, $title, $sector, $profile, $programs, $expertise, $cooperation, $verification, $website = '', $source = '', $legal_name = '', $location = '', $order = 0, $legacy = array() ) {
	$verification_note = 'needs-confirmation' === $verification
		? 'Identitas atau profil publik mitra masih perlu dikonfirmasi melalui dokumen sekolah.'
		: 'Keterangan hubungan dengan SMK Queen Al-Falah mengikuti daftar internal yang diberikan sekolah; profil publik tidak otomatis membuktikan MoU masih aktif.';

	$item = array(
		'seed_key'   => 'partner:' . $slug,
		'title'      => $title,
		'slug'       => $slug,
		'status'     => 'publish',
		'menu_order' => $order,
		'excerpt'    => $profile,
		'content'    => '<h2>Profil Mitra</h2><p>' . esc_html( $profile ) . '</p><h2>Kesesuaian dengan Program Keahlian</h2><p>' . esc_html( $cooperation ) . '</p><p><strong>Catatan verifikasi:</strong> ' . esc_html( $verification_note ) . '</p>',
		'meta'       => array_filter(
			array(
				'_qaf_partner_url'          => $website,
				'_qaf_partner_sector'       => $sector,
				'_qaf_partner_legal_name'   => $legal_name,
				'_qaf_partner_location'     => $location,
				'_qaf_partner_programs'     => implode( "\n", $programs ),
				'_qaf_partner_expertise'    => implode( "\n", $expertise ),
				'_qaf_partner_cooperation'  => $cooperation,
				'_qaf_partner_source_url'   => $source,
				'_qaf_partner_verification' => $verification,
			)
		),
		'terms'      => array( 'qaf_partner_sector' => array( $sector ) ),
	);

	if ( ! empty( $legacy['profile'] ) && is_string( $legacy['profile'] ) ) {
		$item['legacy_excerpt'] = $legacy['profile'];
		$item['legacy_content'] = '<h2>Profil Mitra</h2><p>' . esc_html( $legacy['profile'] ) . '</p><h2>Kesesuaian dengan Program Keahlian</h2><p>' . esc_html( $cooperation ) . '</p><p><strong>Catatan verifikasi:</strong> ' . esc_html( $verification_note ) . '</p>';
	}
	if ( ! empty( $legacy['meta'] ) && is_array( $legacy['meta'] ) ) {
		$item['legacy_meta'] = $legacy['meta'];
	}

	return $item;
};

return array(
	'version' => '1.1.0',
	'items'   => array(
		$qaf_partner_item(
			'jtv-kediri',
			'JTV Kediri',
			'Media dan Industri Kreatif',
			'JTV Kediri merupakan kanal televisi regional yang memproduksi dan menayangkan program berita untuk wilayah Kediri dan sekitarnya. Produksi siaran tersebut relevan dengan kompetensi DKV seperti videografi, fotografi, audio, pencahayaan, editing, grafis siaran, dan komunikasi media.',
			array( 'Desain Komunikasi Visual (DKV)' ),
			array(
				'Produksi video dan program televisi',
				'Videografi, fotografi, audio, dan pencahayaan',
				'Editing, grafis siaran, dan jurnalistik visual',
				'Konten promosi serta komunikasi media',
			),
			'Berdasarkan data sekolah, JTV Kediri menjadi mitra bidang DKV. Kesesuaiannya mencakup pengenalan alur kerja produksi siaran, dokumentasi, pengolahan visual, editing, dan komunikasi profesional dalam kegiatan PKL atau pembelajaran industri.',
			'verified-profile',
			'https://www.portaljtv.com/',
			'https://web.komdigi.go.id/resource/ZHJ1cGFsL3VzZXJzLzQ3NjEvMS4gTGFtcGlyYW5fTGVtYmFnYV9QZW55aWFyYW4ucGRm',
			'PT Jaya Kediri Televisi',
			'Kabupaten Kediri, Jawa Timur',
			1,
			array(
				'profile' => 'JTV Kediri merupakan televisi regional yang melayani produksi dan penayangan program, berita, iklan, film, fotografi, serta konten audiovisual untuk wilayah Kediri dan sekitarnya.',
			),
		),
		$qaf_partner_item(
			'fa-cinema',
			'FA Cinema',
			'Media dan Industri Kreatif',
			'FA Cinema dicatat sekolah sebagai mitra bidang produksi audiovisual. Sampai pembaruan data ini, situs resmi, alamat, dan bentuk badan usahanya belum dapat dipastikan melalui sumber publik yang dapat dipertanggungjawabkan.',
			array( 'Desain Komunikasi Visual (DKV)' ),
			array(
				'Produksi film dan video (berdasarkan data sekolah)',
				'Pengoperasian kamera dan pencahayaan',
				'Editing serta pembuatan konten',
			),
			'Berdasarkan data internal sekolah, kerja sama diarahkan pada pengalaman PKL DKV dalam produksi video, kamera, lighting, editing, dan content creation. Identitas resmi serta ruang lingkup kerja sama perlu dicocokkan dengan surat tugas PKL atau dokumen mitra.',
			'needs-confirmation',
			'',
			'',
			'',
			'',
			2
		),
		$qaf_partner_item(
			'ourweb',
			'OurWeb.id',
			'Teknologi dan Industri Kreatif',
			'OurWeb.id adalah agensi digital di Kediri yang menawarkan pembuatan website dan WordPress, UI/UX, SEO, iklan digital, branding, serta pemasaran digital. Situsnya juga mempublikasikan program magang untuk bidang terkait.',
			array( 'Desain Komunikasi Visual (DKV)', 'Teknik Jaringan Komputer dan Telekomunikasi (TJKT)' ),
			array(
				'Web design dan pengembangan WordPress',
				'UI/UX serta desain antarmuka',
				'Branding, SEO, dan pemasaran digital',
				'Konten visual dan pengelolaan proyek web',
			),
			'Berdasarkan data sekolah, OurWeb menjadi mitra DKV untuk PKL dan pengenalan proyek website, desain antarmuka, branding, serta pemasaran digital. Profil magang publiknya juga relevan untuk kolaborasi lintas DKV dan TJKT.',
			'verified-profile',
			'https://ourweb.id/',
			'https://ourweb.id/tempat-magang-kediri/',
			'PT Our Digital Creative (nama yang dicantumkan pada situs; registrasi eksternal belum diverifikasi)',
			'Kediri, Jawa Timur',
			3
		),
		$qaf_partner_item(
			'terra-computer-system-kediri',
			'Terra Computer System Kediri',
			'Teknologi dan Pelatihan',
			'Terra Computer System Kediri adalah lembaga kursus terakreditasi yang menyelenggarakan pelatihan teknik komputer jaringan, pemrograman web, aplikasi perkantoran, desain grafis, digital marketing, multimedia, video editing, dan broadcasting.',
			array( 'Teknik Jaringan Komputer dan Telekomunikasi (TJKT)' ),
			array(
				'Teknik komputer dan jaringan',
				'Pemrograman web serta aplikasi perkantoran',
				'Desain grafis, multimedia, dan digital marketing',
				'Pelatihan keterampilan teknologi informasi',
			),
			'Berdasarkan data sekolah, Terra Computer menjadi mitra TJKT. Kesesuaiannya mencakup PKL, pelatihan perangkat dan jaringan, praktik web dasar, dukungan TI, serta penguatan keterampilan teknis peserta didik.',
			'verified-profile',
			'https://terrakediri.com/',
			'https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/K5663463',
			'LKP Terra Computer System Kediri — Yayasan Pendidikan Pelita Nusantara Kediri',
			'Jl. Balowerti II/26–30, Balowerti, Kota Kediri',
			4
		),
		$qaf_partner_item(
			'cv-nusantara-media-mandiri',
			'CV Nusantara Media Mandiri',
			'Teknologi Informasi',
			'CV Nusantara Media Mandiri (CVNMM) adalah perusahaan teknologi informasi di Kabupaten Kediri dengan layanan web, aplikasi mobile, infrastruktur jaringan, POS, pelatihan TI, cloud, dan Internet of Things.',
			array( 'Teknik Jaringan Komputer dan Telekomunikasi (TJKT)' ),
			array(
				'Infrastruktur dan administrasi jaringan komputer',
				'Web design serta aplikasi mobile',
				'Cloud, Internet of Things, dan dukungan sistem',
				'Pelatihan teknologi informasi',
			),
			'Berdasarkan data sekolah, CVNMM menjadi mitra TJKT untuk pengalaman PKL dan pembelajaran terkait instalasi jaringan, dukungan sistem, web/mobile, cloud, IoT, serta tata kelola proyek teknologi.',
			'verified-profile',
			'https://cv-nmm.com/',
			'https://cv-nmm.com/',
			'CV Nusantara Media Mandiri',
			'Perum Ayanna I/56, Sambiresik, Gampengrejo, Kabupaten Kediri',
			5
		),
		$qaf_partner_item(
			'cv-besar-anugrah-djaya',
			'CV Besar Anugrah Djaya (BeAD Group)',
			'Teknologi Informasi',
			'BeAD Group atau BeAD IT Consultant merupakan perusahaan teknologi dan event organizer di Kabupaten Kediri yang bergerak pada software house, website, aplikasi, serta konsultasi teknologi informasi.',
			array( 'Teknik Jaringan Komputer dan Telekomunikasi (TJKT)' ),
			array(
				'Pengembangan website dan aplikasi',
				'Software house serta konsultasi TI',
				'Dukungan teknologi dan pengelolaan proyek',
				'Kegiatan rekrutmen dan pengenalan dunia kerja',
			),
			'Nama ini merupakan kandidat kuat untuk “CV BAD” pada daftar sekolah dan tetap perlu konfirmasi ejaan. Publikasi Bursa Kerja Khusus Kabupaten Kediri mencatat kegiatan SMK Queen Al-Falah–CV BeAD pada Februari 2022; kesesuaian TJKT meliputi PKL, pengembangan sistem, dukungan teknologi, dan pengenalan rekrutmen.',
			'needs-confirmation',
			'https://beadgrup.com/',
			'https://www.smkbhaktimuliapare.sch.id/bulan-rekrutmen-kabupaten-kediri-fbkk-smk-kab-kediri/',
			'CV Besar Anugrah Djaya',
			'Jl. Butolocoyo No. 175, Menang, Kecamatan Pagu, Kabupaten Kediri',
			6,
			array(
				'meta' => array(
					'_qaf_partner_location' => 'Kayenlor, Plemahan, Kabupaten Kediri',
				),
			),
		),
		$qaf_partner_item(
			'candradimuka-digital',
			'Candradimuka Digital',
			'Teknologi dan Industri Kreatif',
			'Candradimuka Digital adalah agensi digital di Kabupaten Kediri yang menyediakan pengembangan dan desain website, UI/UX, pemasaran digital, serta pengelolaan media sosial.',
			array( 'Desain Komunikasi Visual (DKV)', 'Teknik Jaringan Komputer dan Telekomunikasi (TJKT)' ),
			array(
				'Web development dan web design',
				'UI/UX serta desain antarmuka',
				'Digital marketing dan media sosial',
				'Branding serta konten digital',
			),
			'Berdasarkan data sekolah, Candradimuka mendukung bidang website. Kesesuaiannya mencakup PKL, pendampingan Desain Web, proyek website, UI/UX, branding, dan komunikasi digital lintas DKV–TJKT.',
			'verified-profile',
			'https://candradimukadigital.com/',
			'https://candradimukadigital.com/kontak-kami-candradimuka-digital/',
			'Candradimuka Digital (bentuk badan usaha belum dicantumkan pada profil publik)',
			'Desa Bulu, Kecamatan Semen, Kabupaten Kediri',
			7
		),
		$qaf_partner_item(
			'pt-alfiz',
			'PT Alfiz',
			'Teknologi Pendidikan',
			'PT Alfiz dicatat sekolah sebagai penyedia atau mitra sistem Computer Based Test (CBT). Nama legal lengkap, alamat, situs, dan profil badan usaha yang pasti belum ditemukan pada sumber publik.',
			array( 'Teknik Jaringan Komputer dan Telekomunikasi (TJKT)' ),
			array(
				'Sistem Computer Based Test (berdasarkan data sekolah)',
				'Pengelolaan server dan basis data ujian',
				'Dukungan operasional asesmen digital',
			),
			'Berdasarkan data internal sekolah, kerja sama berupa dukungan sistem CBT. Kesesuaiannya dengan TJKT meliputi server, jaringan, basis data peserta, keamanan akses, pemantauan ujian, dan penanganan gangguan. Profil harus diperbarui setelah nama legal atau kanal resmi diperoleh.',
			'needs-confirmation',
			'',
			'',
			'',
			'',
			8
		),
		$qaf_partner_item(
			'beneficia-tech',
			'Beneficia Tech',
			'Teknologi Informasi',
			'Beneficia Tech adalah merek jasa teknologi di Kediri yang pada data sekolah menangani website serta pemeliharaan dan servis perangkat. Kredit Beneficia Tech tampil pada footer situs resmi SMK Queen Al-Falah.',
			array( 'Teknik Jaringan Komputer dan Telekomunikasi (TJKT)', 'Desain Komunikasi Visual (DKV)' ),
			array(
				'Pembuatan dan pemeliharaan website',
				'Perawatan, troubleshooting, dan servis perangkat',
				'Dukungan teknis serta pemeliharaan sistem',
				'Antarmuka dan konten web',
			),
			'Berdasarkan data sekolah, Beneficia Tech menyalurkan dukungan website dan maintenance equipment service. Kesesuaiannya mencakup TJKT untuk troubleshooting, perangkat, sistem, dan dukungan teknis; serta DKV untuk antarmuka dan konten website.',
			'school-data',
			'',
			'https://smkqueenalfalah.sch.id/',
			'Beneficia Tech (bentuk badan usaha belum terverifikasi)',
			'Kota Kediri, Jawa Timur',
			9
		),
		$qaf_partner_item(
			'pt-jwb',
			'PT JWB',
			'Administrasi dan Bisnis',
			'PT JWB dicatat sekolah sebagai mitra MPLB. Nama singkatan ini belum cukup untuk memastikan badan usaha yang dimaksud, sehingga profil, lokasi, dan layanan perusahaan belum dapat dipublikasikan sebagai fakta.',
			array( 'Manajemen Perkantoran dan Layanan Bisnis (MPLB)' ),
			array(
				'Administrasi dan layanan bisnis (berdasarkan data sekolah)',
				'Pengelolaan dokumen dan komunikasi perkantoran',
			),
			'Berdasarkan data internal sekolah, kerja sama diarahkan pada MPLB dan pengalaman administrasi perkantoran. Nama legal lengkap, alamat, NIB, akun resmi, dan ruang lingkup PKL perlu dikonfirmasi sebelum detail tambahan diterbitkan.',
			'needs-confirmation',
			'',
			'',
			'',
			'',
			10
		),
		$qaf_partner_item(
			'lp3i-college-kediri',
			'LP3I College Kediri',
			'Pendidikan Vokasi dan Bisnis',
			'LP3I College Kediri adalah lembaga pendidikan vokasi dua tahun yang menyelenggarakan program bisnis digital, otomatisasi administrasi perkantoran, sistem informasi akuntansi, sertifikasi profesi, dan pembinaan kesiapan kerja.',
			array( 'Manajemen Perkantoran dan Layanan Bisnis (MPLB)' ),
			array(
				'Office Administration Automatization',
				'Digital Business Management',
				'Accounting Information System',
				'Kesiapan kerja dan sertifikasi profesi',
			),
			'Berdasarkan data sekolah, LP3I menjadi mitra MPLB untuk penguatan administrasi digital, komunikasi bisnis, aplikasi perkantoran, kesiapan kerja, studi lanjut, dan pengenalan budaya profesional.',
			'verified-profile',
			'https://www.lp3i.ac.id/campus/lp3i-college-kediri/',
			'https://www.lp3i.ac.id/campus/lp3i-college-kediri/',
			'LP3I College Kediri',
			'Jl. Letjend Sutoyo No. 94, Bangsal, Pesantren, Kota Kediri',
			11
		),
		$qaf_partner_item(
			'rs-bhayangkara-kediri',
			'Rumah Sakit Bhayangkara Kediri',
			'Kesehatan',
			'Rumah Sakit Bhayangkara Kediri merupakan rumah sakit milik Polri kelas B/Tingkat II dengan layanan gawat darurat, rawat jalan dan inap, perawatan intensif, bedah, hemodialisis, kemoterapi, MCU, serta layanan penunjang.',
			array( 'Layanan Kesehatan (LK)' ),
			array(
				'Layanan rumah sakit dan keselamatan pasien',
				'Komunikasi, dokumentasi, dan alur pelayanan',
				'Pencegahan infeksi serta kesehatan dan keselamatan kerja',
				'Layanan penunjang dan budaya kerja fasilitas kesehatan',
			),
			'Berdasarkan data sekolah, rumah sakit menjadi mitra Layanan Kesehatan. Kegiatan siswa dibatasi pada observasi dan praktik terbimbing sesuai kewenangan, keselamatan pasien, etika, kerahasiaan, serta kebijakan rumah sakit; tidak mencakup tindakan klinis mandiri.',
			'verified-profile',
			'https://rsbhayangkarakediri.com/',
			'https://rsbhayangkarakediri.com/profil',
			'Rumah Sakit Bhayangkara Kediri',
			'Jl. Kombes Pol. Duryat No. 17, Kota Kediri',
			12
		),
		$qaf_partner_item(
			'uptd-puskesmas-mojo',
			'UPTD Puskesmas Mojo',
			'Kesehatan',
			'UPTD Puskesmas Mojo adalah unit Dinas Kesehatan Kabupaten Kediri yang menyelenggarakan pelayanan kesehatan primer, upaya kesehatan perorangan dan masyarakat, layanan promotif-preventif, rawat inap, serta administrasi layanan.',
			array( 'Layanan Kesehatan (LK)' ),
			array(
				'Pelayanan kesehatan primer',
				'Promosi kesehatan dan pencegahan penyakit',
				'Kesehatan masyarakat serta administrasi layanan',
				'Komunikasi pasien, keselamatan, dan rujukan',
			),
			'Berdasarkan data sekolah, Puskesmas Mojo menjadi mitra Layanan Kesehatan. Kegiatan diarahkan pada observasi dan praktik terbimbing dalam pelayanan primer, komunikasi, promosi kesehatan, dokumentasi, pencegahan infeksi, serta administrasi sesuai kewenangan dan kebijakan puskesmas.',
			'verified-profile',
			'https://puskesmasmojo.kedirikab.go.id/',
			'https://regpus.kemkes.go.id/media/dokumen/publikasi/2025-07-10/KMK_No._HK.01.07-MENKES-717-2025_Semester_II_Th_2024_Com.pdf',
			'UPTD Puskesmas Mojo — Dinas Kesehatan Kabupaten Kediri',
			'Jl. Raya Mojo No. 201, Kecamatan Mojo, Kabupaten Kediri',
			13,
			array(
				'meta' => array(
					'_qaf_partner_source_url' => 'https://puskesmasmojo.kedirikab.go.id/',
				),
			),
		),
		$qaf_partner_item(
			'rsu-arga-husada',
			'Rumah Sakit Umum Arga Husada',
			'Kesehatan',
			'Rumah Sakit Umum Arga Husada adalah rumah sakit umum di Ngadiluwih, Kabupaten Kediri. Nama resmi menggunakan ejaan “Arga Husada”, bukan “Argha Husada”.',
			array( 'Layanan Kesehatan (LK)' ),
			array(
				'Pelayanan dan alur kerja rumah sakit umum',
				'Perawatan dan komunikasi pasien',
				'Dokumentasi layanan serta pencegahan infeksi',
				'Kesehatan dan keselamatan kerja fasilitas kesehatan',
			),
			'Berdasarkan data sekolah, RSU Arga Husada menjadi mitra Layanan Kesehatan. Kegiatan siswa berupa observasi dan praktik terbimbing sesuai kewenangan, etika, kerahasiaan, keselamatan pasien, dan kebijakan rumah sakit; tidak mencakup tindakan klinis mandiri.',
			'verified-profile',
			'https://rsargahusada.com/',
			'https://sirs.kemkes.go.id/fo/home/profile_rs/3506058',
			'Rumah Sakit Umum Arga Husada',
			'Jl. Raya Branggahan No. 100, Ngadiluwih, Kabupaten Kediri',
			14
		),
	),
);
