<?php
/**
 * Official organization structure for school year 2026/2027.
 *
 * Keep this file limited to public institutional names and assignments.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$member = static function ( $name, $role, $options = array() ) {
	return array_merge(
		array(
			'name' => $name,
			'role' => $role,
		),
		$options
	);
};

return array(
	array(
		'title'    => 'Tim Pengembangan Sekolah',
		'overview' => 'Merumuskan arah pengembangan sekolah, menyelaraskan program lintas bidang, dan memantau pencapaian mutu kelembagaan.',
		'members'  => array(
			$member( 'Irkham Hendi, S.Pd.', 'Kepala Sekolah' ),
			$member( 'Moh. Saean Marzuki, S.Pd.', 'Wakil Kepala Sekolah Bidang Kurikulum' ),
			$member( 'Efi Risca Ferbriana Wati, S.Pd.', 'Wakil Kepala Sekolah Bidang Kesiswaan' ),
			$member( 'Nur Ahmad Fatihin, S.Pd.', 'Wakil Kepala Sekolah Bidang Sarana dan Prasarana' ),
			$member( 'Ahmad Sirojudin, S.Pd.I.', 'Wakil Kepala Sekolah Bidang Hubungan Industri dan Hubungan Masyarakat', array( 'aliases' => array( 'Achmad Sirojudin, S.Pd.I.' ) ) ),
			$member( 'Novi Wahyu Harfiana, S.Pd.I.', 'Bendahara Sekolah' ),
			$member( 'Lilik Arifatu Zumaroh, S.Pd.', 'Bendahara BOS' ),
			$member( 'Brilliantna Mumtazah, S.Pd.', 'Operator Sekolah', array( 'aliases' => array( 'Brilianta Mumtazah, S.Pd.' ) ) ),
			$member( 'Sunarto Abdillah, S.Hi.', 'Komite Sekolah' ),
		),
	),
	array(
		'title'    => 'Ketua Program Keahlian',
		'overview' => 'Mengelola mutu pembelajaran kejuruan, sarana praktik, kompetensi guru, dan hubungan industri pada setiap program.',
		'members'  => array(
			$member( 'Mohammad Ihwan Ngisomundin, S.Kom.', 'Kepala Program Keahlian DKV' ),
			$member( 'Salma Febrila, S.Tr.T.', 'Kepala Program Keahlian TJKT' ),
			$member( 'Ariska Fisma Lestari, S.Pd.', 'Kepala Program Keahlian MPLB', array( 'aliases' => array( 'Ariska Fima Lestari, S.Pd.' ) ) ),
			$member( 'Siti Khamidatul Mahbubah, S.Kep., Ners.', 'Kepala Program Keahlian Layanan Kesehatan' ),
		),
	),
	array(
		'title'    => 'Kelompok Kerja Pengembangan Kurikulum',
		'overview' => 'Menyusun, meninjau, dan mengevaluasi kurikulum operasional sekolah agar relevan dengan regulasi, karakter pesantren, dan kebutuhan dunia kerja.',
		'members'  => array(
			$member( 'Moh. Saean Marzuki, S.Pd.', 'Ketua' ),
			$member( 'Ariska Fisma Lestari, S.Pd.', 'Sekretaris dan Perwakilan Kepala Program MPLB/LK', array( 'aliases' => array( 'Ariska Fima Lestari, S.Pd.' ) ) ),
			$member( 'Mohammad Ihwan Ngisomundin, S.Kom.', 'Anggota dan Kepala Program DKV' ),
			$member( 'Siti Khamidatul Mahbubah, S.Kep., Ners.', 'Anggota dan Kepala Program LK' ),
			$member( 'Salma Febrila, S.Tr.T.', 'Anggota dan Kepala Program TJKT' ),
			$member( 'Misbachul Arafat, S.Kom.', 'Anggota dan Staf Kurikulum' ),
		),
	),
	array(
		'title'    => 'Kelompok Kerja PSG/PKL Kelas XII',
		'overview' => 'Menyiapkan penempatan, pembekalan, pemantauan, penilaian, keselamatan, dan pelaporan praktik kerja lapangan kelas XII.',
		'members'  => array(
			$member( 'Irkham Hendi, S.Pd.', 'Penanggung Jawab' ),
			$member( 'Ahmad Sirojudin, S.Pd.I.', 'Ketua', array( 'aliases' => array( 'Achmad Sirojudin, S.Pd.I.' ) ) ),
			$member( 'Ibnu Ulya, S.Hi.', 'Anggota dari Tata Usaha' ),
			$member( 'Seluruh Kepala Program Keahlian', 'Anggota', array( 'collective' => true ) ),
		),
	),
	array(
		'title'    => 'Kelompok Bimbingan dan Penyuluhan Siswa',
		'overview' => 'Memberikan layanan pendampingan perkembangan pribadi, sosial, belajar, disiplin, dan perencanaan karier peserta didik.',
		'members'  => array(
			$member( 'Irkham Hendi, S.Pd.', 'Penanggung Jawab' ),
			$member( 'Novi Nurul Masrurotin, S.Pd.', 'Anggota Pendamping Kelas XII' ),
			$member( 'Kholifah Putri Suryaningsih, S.Psi.', 'Anggota Pendamping Kelas XI' ),
			$member( 'Nasywa Fauzia Zahro, S.Pd.', 'Anggota Pendamping Kelas X' ),
			$member( 'Seluruh Wali Kelas', 'Anggota', array( 'collective' => true ) ),
		),
	),
	array(
		'title'    => 'Pembina Kesiswaan dan Usaha Kesehatan Sekolah',
		'overview' => 'Mengembangkan kepemimpinan siswa, prestasi, budaya positif, kesehatan sekolah, dan kegiatan OSIS/MPK.',
		'members'  => array(
			$member( 'Efi Risca Ferbriana Wati, S.Pd.', 'Wakil Kepala Sekolah Bidang Kesiswaan dan Koordinator Bidang Kesiswaan' ),
			$member( 'Ringga Fatma Hardiani, M.Pd.', 'Pembina OSIS/MPK Putra' ),
			$member( 'Ns. H. M. Ida Nashrun Sakif, M.Kep.', 'Pembina OSIS/MPK Putri', array( 'aliases' => array( 'M. Ida Nashrun Sakif, S.Kep., Ns.' ) ) ),
			$member( 'Zalza Meira Cahyani, S.H.', 'Koordinator Bidang Perlombaan Siswa' ),
			$member( 'Putri Nita Wulandari, S.Hum.', 'Pembina UKS' ),
			$member( 'Pradita Ratna Arianti, S.Pd.', 'Anggota UKS' ),
		),
	),
	array(
		'title'    => 'Tim Pencegahan dan Penanggulangan Kekerasan',
		'overview' => 'Mencegah, menerima laporan, menindaklanjuti, mendampingi, dan mendokumentasikan penanganan kekerasan secara aman serta berpihak pada korban.',
		'members'  => array(
			$member( 'Kholifah Putri Suryaningsih, S.Psi.', 'Koordinator' ),
			$member( 'Moh. Diky Bahtiar, S.Pd.', 'Anggota' ),
			$member( 'Ridho Waalidaihi Al Imam, S.Pd.', 'Anggota' ),
		),
	),
	array(
		'title'    => 'Wali Kelas',
		'overview' => 'Menjadi penghubung utama sekolah, peserta didik, guru, dan orang tua dalam administrasi serta pembinaan setiap rombongan belajar.',
		'members'  => array(
			$member( 'Nasywa Fauzia Zahro, S.Pd.', 'Wali Kelas X LK' ),
			$member( 'Pradita Ratna Arianti, S.Pd.', 'Wali Kelas X MPLB' ),
			$member( 'Cecilia Arisca Pratiwi, S.Sos.', 'Wali Kelas X DKV 1' ),
			$member( 'Moh. Diky Bahtiar, S.Pd.', 'Wali Kelas X DKV 2' ),
			$member( 'Harlinvia Maulitha Indahsari, S.Pd.', 'Wali Kelas X DKV 3' ),
			$member( 'Umi A’mila Khoidhiroh, S.Pd.', 'Wali Kelas X DKV 4' ),
			$member( 'Ayu Titi Rahayu, S.Pd., S.T.', 'Wali Kelas X TJKT 1' ),
			$member( 'Garina Rahmi Rahmani, S.Pd.', 'Wali Kelas X TJKT 2' ),
			$member( 'Yuliana Purnama Sari, S.Pd.', 'Wali Kelas X TJKT 3' ),
			$member( 'Siti Khamidatul Mahbubah, S.Kep., Ners.', 'Wali Kelas XI LK' ),
			$member( 'Sekar Trisna Sepgiar, S.E.', 'Wali Kelas XI MPLB' ),
			$member( 'Kholifah Putri Suryaningsih, S.Psi.', 'Wali Kelas XI DKV 1' ),
			$member( 'Ida Rosyidah, S.Pd.', 'Wali Kelas XI DKV 2' ),
			$member( 'Ana Nur Lailatul Khoiriyah, S.Pd.', 'Wali Kelas XI DKV 3' ),
			$member( 'Sirajudin Ahmad, S.Kom.', 'Wali Kelas XI DKV 4' ),
			$member( 'Asmaul Khusna, S.Pd.', 'Wali Kelas XI TJKT 1', array( 'aliases' => array( 'Asmaul Husna, S.Pd.' ) ) ),
			$member( 'Putri Nita Wulandari, S.Hum.', 'Wali Kelas XI TJKT 2' ),
			$member( 'Ridho Waalidaihi Al Imam, S.Pd.', 'Wali Kelas XI TJKT 3' ),
			$member( 'Chotim Alfa Ni’amah, S.Pd.', 'Wali Kelas XI TJKT 4' ),
			$member( 'M. Aghisna Hadziqun Nuha, S.Tr.T.', 'Wali Kelas XI TJKT 5' ),
			$member( 'Ns. H. M. Ida Nashrun Sakif, M.Kep.', 'Wali Kelas XII LK', array( 'aliases' => array( 'M. Ida Nashrun Sakif, S.Kep., Ns.' ) ) ),
			$member( 'Ariska Fisma Lestari, S.Pd.', 'Wali Kelas XII MPLB', array( 'aliases' => array( 'Ariska Fima Lestari, S.Pd.' ) ) ),
			$member( 'Nurul Hidayah, S.Sos.', 'Wali Kelas XII DKV 1' ),
			$member( 'Haniful Khalid, S.T.', 'Wali Kelas XII DKV 2' ),
			$member( 'Novi Nurul Masrurotin, S.Pd.', 'Wali Kelas XII DKV 3' ),
			$member( 'Lutfi Kurniasani, S.S.', 'Wali Kelas XII DKV 4' ),
			$member( 'Agus Sutrisno, S.Kom.', 'Wali Kelas XII TJKT 1' ),
			$member( 'Misbachul Arafat, S.Kom.', 'Wali Kelas XII TJKT 2' ),
			$member( 'Zalza Meira Cahyani, S.H.', 'Wali Kelas XII TJKT 3' ),
			$member( 'Muhammad Husnul Hafizh Alfian, S.Pd.', 'Wali Kelas XII TJKT 4' ),
			$member( 'Mu’alifah, S.E.Sy., M.E.', 'Wali Kelas XII TJKT 5' ),
			$member( 'Nevyta Selvyana Wardani, S.Kom.', 'Wali Kelas XII TJKT 6' ),
		),
	),
	array(
		'title'    => 'Bidang Perpustakaan dan Literasi Sekolah',
		'overview' => 'Mengelola koleksi, layanan pemustaka, administrasi perpustakaan, dan program peningkatan budaya baca serta literasi.',
		'members'  => array(
			$member( 'Ayu Titi Rahayu, S.Pd., S.T.', 'Kepala Perpustakaan' ),
			$member( 'Mu’alifah, S.E.Sy., M.E.', 'Anggota' ),
		),
	),
	array(
		'title'    => 'Bidang Pengembangan Diri dan Ekstrakurikuler',
		'overview' => 'Menyediakan pendampingan bakat, minat, kreativitas, olahraga, seni, teknologi, dan kecakapan hidup pada dua gedung sekolah.',
		'members'  => array(
			$member( 'Agus Sutrisno, S.Kom.', 'Pendamping Desain Web — Gedung Kraton' ),
			$member( 'Brilliantna Mumtazah, S.Pd.', 'Pendamping Desain Canva — Gedung Kraton', array( 'aliases' => array( 'Brilianta Mumtazah, S.Pd.' ) ) ),
			$member( 'Cecilia Arisca Pratiwi, S.Sos.', 'Pendamping Tata Rias — Gedung Kraton' ),
			$member( 'Juwita Puspita Sari', 'Pendamping Tata Rias — Gedung Kraton' ),
			$member( 'Siska Mala Sari, M.Pd.', 'Pendamping Banjari — Gedung Kraton' ),
			$member( 'Della (Sanggar Tari)', 'Pendamping Seni Tari — Gedung Kraton' ),
			$member( 'Mohammad Ihwan Ngisomundin, S.Kom.', 'Pendamping Desain Web A — Gedung Kebanan' ),
			$member( 'M. Aghisna Hadziqun Nuha, S.Tr.T.', 'Pendamping Desain Web B — Gedung Kebanan' ),
			$member( 'Haniful Khalid, S.T.', 'Pendamping Broadcasting A — Gedung Kebanan' ),
			$member( 'Garina Rahmi Rahmani, S.Pd.', 'Pendamping Broadcasting B — Gedung Kebanan' ),
			$member( 'Moh. Diky Bahtiar, S.Pd.', 'Pendamping Tenis Meja — Gedung Kebanan' ),
			$member( 'Andika Ferdian Putra E., S.Pd.', 'Pendamping Bola Voli — Gedung Kebanan' ),
			$member( 'Akbar Junaidi, S.Pd.', 'Pendamping Futsal — Gedung Kebanan' ),
			$member( 'Pradita Ratna Arianti, S.Pd.', 'Pendamping Seni Lukis — Gedung Kebanan' ),
			$member( 'Pendamping Banjari Gedung Kebanan', 'Pendamping belum ditetapkan', array( 'collective' => true, 'duty' => 'Jabatan masih perlu ditetapkan secara resmi sebelum program, jadwal, dan tanggung jawab pendamping Banjari Gedung Kebanan dipublikasikan.' ) ),
		),
	),
	array(
		'title'    => 'Pengelola Sarana Prasarana dan Laboratorium Komputer',
		'overview' => 'Merencanakan kebutuhan, inventarisasi, pemeliharaan, keamanan, dan optimalisasi fasilitas serta laboratorium komputer.',
		'members'  => array(
			$member( 'Nur Ahmad Fatihin, S.Pd.', 'Wakil Kepala Sekolah Bidang Sarana dan Prasarana' ),
			$member( 'Agus Sutrisno, S.Kom.', 'Staf Bidang Sarana dan Prasarana' ),
			$member( 'Muhammad Husnul Hafizh Alfian, S.Pd.', 'Kepala Laboratorium Komputer' ),
			$member( 'Dimas Nur Alfiyasin', 'Laboran dan Teknisi' ),
			$member( 'Yusril Annajar', 'Laboran dan Teknisi' ),
		),
	),
	array(
		'title'    => 'Lembaga Sertifikasi Profesi Pihak Pertama',
		'overview' => 'Menjamin pelaksanaan sertifikasi kompetensi yang objektif, terdokumentasi, dan sesuai skema serta pedoman lembaga sertifikasi.',
		'members'  => array(
			$member( 'Irkham Hendi, S.Pd.', 'Dewan Pengarah' ),
			$member( 'Moh. Saean Marzuki, S.Pd.', 'Komite Skema' ),
			$member( 'Nevyta Selvyana Wardani, S.Kom.', 'Ketua LSP' ),
			$member( 'Lilik Arifatu Zumaroh, S.Pd.', 'Bendahara' ),
			$member( 'Ririn Rohmatul Umah, S.Pd.', 'Kepala Bagian Administrasi' ),
			$member( 'Mohammad Ihwan Ngisomundin, S.Kom.', 'Kepala Bagian Sertifikasi' ),
			$member( 'Siska Mala Sari, M.Pd.', 'Kepala Bagian Manajemen Mutu' ),
			$member( 'Ariska Fisma Lestari, S.Pd.', 'Anggota Bagian Administrasi', array( 'aliases' => array( 'Ariska Fima Lestari, S.Pd.' ) ) ),
			$member( 'Ahmad Sirojudin, S.Pd.I.', 'Anggota Bagian Sertifikasi', array( 'aliases' => array( 'Achmad Sirojudin, S.Pd.I.' ) ) ),
			$member( 'Ns. H. M. Ida Nashrun Sakif, M.Kep.', 'Anggota Bagian Manajemen Mutu', array( 'aliases' => array( 'M. Ida Nashrun Sakif, S.Kep., Ns.' ) ) ),
			$member( 'Haniful Khalid, S.T.', 'Anggota' ),
			$member( 'Siti Khamidatul Mahbubah, S.Kep., Ners.', 'Anggota' ),
			$member( 'Agus Sutrisno, S.Kom.', 'Anggota' ),
		),
	),
	array(
		'title'    => 'Staf Administrasi Sekolah dan Pembantu Pelaksana',
		'overview' => 'Menopang layanan administrasi, keamanan, kebersihan, lalu lintas, fasilitas umum, dan kebutuhan operasional harian sekolah.',
		'members'  => array(
			$member( 'Ibnu Ulya, S.Hi.', 'Kepala Tata Usaha' ),
			$member( 'Isna Ainun Ni’mah, S.Kom.', 'Staf Tata Usaha' ),
			$member( 'Ririn Rohmatul Umah, S.Pd.', 'Staf Tata Usaha' ),
			$member( 'Brilliantna Mumtazah, S.Pd.', 'Staf Tata Usaha', array( 'aliases' => array( 'Brilianta Mumtazah, S.Pd.' ) ) ),
			$member( 'Mohammad Ibnu Malik', 'Satpam Gedung Kebanan' ),
			$member( 'Mohamad Yasin', 'Satpam Gedung Kraton' ),
			$member( 'Drs. Moh. Arifin', 'Pengatur Lalu Lintas' ),
			$member( 'Ninik Kusmiati', 'Tenaga Kebersihan dan Akomodasi Umum — Shift Pagi dan Sore' ),
			$member( 'Pujiono', 'Tenaga Kebersihan dan Pesuruh Sekolah — Shift Pagi dan Sore' ),
			$member( 'Pujiyanto', 'Tenaga Kebersihan — Shift Siang dan Sore' ),
			$member( 'Abdul Wahid', 'Penjaga Sekolah Malam dan Kebersihan Kamar Mandi' ),
			$member( 'Dimas Nur Alfiyasin', 'Penjaga Sekolah Malam' ),
			$member( 'Halimatus Syadiah (Mba Diah)', 'Penjaga Kantin' ),
			$member( 'Almuznah (Mba Al)', 'Penjaga Kantin' ),
		),
	),
	array(
		'title'    => 'Tim Pengelola Website, Media Sosial, dan Konten Kreatif',
		'overview' => 'Mengelola kanal digital resmi, produksi konten, dokumentasi, standar editorial, keamanan akun, dan konsistensi identitas sekolah.',
		'members'  => array(
			$member( 'Haniful Khalid, S.T.', 'Pengelola Website Sekolah' ),
			$member( 'Brilliantna Mumtazah, S.Pd.', 'Ketua Tim Konten Kreatif', array( 'aliases' => array( 'Brilianta Mumtazah, S.Pd.' ) ) ),
			$member( 'Garina Rahmi Rahmani, S.Pd.', 'Bidang Pengembangan' ),
			$member( 'Nurul Hidayah, S.Sos.', 'Bidang Editing dan Operating' ),
			$member( 'Cecilia Arisca Pratiwi, S.Sos.', 'Bidang Editing dan Operating' ),
		),
	),
	array(
		'title'    => 'Tim Bursa Kerja Khusus',
		'overview' => 'Mengelola informasi lowongan, pemetaan lulusan, hubungan perusahaan, bimbingan karier, penelusuran alumni, dan pelaporan penempatan.',
		'members'  => array(
			$member( 'Ahmad Sirojudin, S.Pd.I.', 'Ketua BKK', array( 'aliases' => array( 'Achmad Sirojudin, S.Pd.I.' ) ) ),
			$member( 'Novi Nurul Masrurotin, S.Pd.', 'Sekretaris' ),
			$member( 'Nasywa Fauzia Zahro, S.Pd.', 'Anggota' ),
		),
	),
	array(
		'title'    => 'Tim Penilai Kinerja Guru atau Rapor Guru',
		'overview' => 'Mengumpulkan bukti, menilai kinerja secara objektif, memberikan umpan balik, dan merumuskan tindak lanjut pengembangan guru.',
		'members'  => array(
			$member( 'Irkham Hendi, S.Pd.', 'Ketua Tim' ),
			$member( 'Sunarto Abdillah, S.Hi.', 'Bidang Pembinaan dan Pengembangan SDM' ),
			$member( 'Moh. Saean Marzuki, S.Pd.', 'Bidang Administrasi dan Ketertiban Pembelajaran' ),
			$member( 'Nur Ahmad Fatihin, S.Pd.', 'Bidang Ketenagaan dan Kedisiplinan' ),
			$member( 'Ahmad Sirojudin, S.Pd.I.', 'Bidang Ketenagaan dan Kedisiplinan', array( 'aliases' => array( 'Achmad Sirojudin, S.Pd.I.' ) ) ),
			$member( 'Brilliantna Mumtazah, S.Pd.', 'Bidang Pengelolaan Data', array( 'aliases' => array( 'Brilianta Mumtazah, S.Pd.' ) ) ),
			$member( 'Novi Wahyu Harfiana, S.Pd.I.', 'Bidang Kekeluargaan Lembaga dan Sosial' ),
			$member( 'Lilik Arifatu Zumaroh, S.Pd.', 'Bidang Kekeluargaan Lembaga dan Sosial' ),
			$member( 'Dimas Nur Alfiyasin', 'Bidang Teknologi Informasi' ),
		),
	),
	array(
		'title'    => 'Tim Pengelola Kinerja Guru di Ruang GTK dan Tim Supervisi Akademik',
		'overview' => 'Mengoordinasikan pengelolaan kinerja pada Ruang GTK, supervisi pembelajaran, refleksi, umpan balik, dan rencana tindak lanjut akademik.',
		'members'  => array(
			$member( 'Irkham Hendi, S.Pd.', 'Penanggung Jawab' ),
			$member( 'Harlinvia Maulitha Indahsari, S.Pd.', 'Ketua Tim' ),
			$member( 'Ida Rosyidah, S.Pd.', 'Sekretaris' ),
		),
	),
	array(
		'title'    => 'Pengelola Komunitas Belajar dan MGMPS',
		'overview' => 'Memfasilitasi berbagi praktik baik, pengembangan perangkat ajar, refleksi pembelajaran, dan kolaborasi guru mata pelajaran.',
		'members'  => array(
			$member( 'Chotim Alfa Ni’amah, S.Pd.', 'Koordinator' ),
			$member( 'Sirajudin Ahmad, S.Kom.', 'Anggota' ),
		),
	),
	array(
		'title'    => 'Pengelola Program Sikap dan Asri',
		'overview' => 'Mengembangkan budaya disiplin, kepedulian, kebersihan, keindahan, dan lingkungan sekolah yang aman serta nyaman.',
		'members'  => array(
			$member( 'Asmaul Khusna, S.Pd.', 'Koordinator', array( 'aliases' => array( 'Asmaul Husna, S.Pd.' ) ) ),
			$member( 'Ana Nur Lailatul Khoiriyah, S.Pd.', 'Anggota' ),
			$member( 'Pujiono', 'Anggota' ),
		),
	),
);
