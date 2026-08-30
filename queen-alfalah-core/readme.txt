=== Queen Al-Falah Core ===
Contributors: smkqueenalfalah
Requires at least: 6.2
Requires PHP: 7.4
Stable tag: 1.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: education, school, custom-post-type, gutenberg, rest-api

Model konten, data sekolah, dan penyiapan situs aman untuk tema WordPress Queen Al-Falah.

== Description ==

Queen Al-Falah Core menjaga data sekolah tetap terpisah dari tema. Plugin menyediakan:

* 12 tipe konten: program, guru/tendik, pengumuman, agenda, prestasi, ekstrakurikuler, layanan, galeri, mitra, lowongan, alumni, dan sarana.
* Taksonomi sekolah, REST meta terstruktur, serta meta box yang aman.
* Pengaturan identitas, visi-misi, kontak, lokasi, pendaftaran, dan media sosial.
* Kolom admin dan pengurutan meta.
* Agenda publik terurut berdasarkan tanggal mulai.
* Penyaringan pengumuman kedaluwarsa hanya pada arsip publik.
* Penyiapan demo satu klik yang idempoten dan tidak menimpa data pengguna.
* Pusat Media privat dengan akun Waka, Guru, dan Tenaga Kependidikan.
* Folder Google Drive pribadi yang dibuat otomatis per username.
* Unggah dan unduh terotorisasi langsung dari folder pribadi.
* Struktur organisasi 2026/2027 dengan tupoksi dan foto tersinkron dari Guru & Tendik.
* Retensi seluruh data saat plugin dihapus.

Tema Queen Al-Falah direkomendasikan, tetapi konten tetap disimpan menggunakan API WordPress standar.

== Installation ==

1. Unggah folder atau ZIP plugin melalui menu Plugin WordPress.
2. Aktifkan Queen Al-Falah Core.
3. Buka Sekolah > Pengaturan dan periksa data resmi.
4. Aktifkan tema Queen Al-Falah.
5. Opsional: buka Sekolah > Penyiapan Demo, baca dampaknya, lalu jalankan penyiapan.
6. Tinjau semua draf dan data awal sebelum publikasi.

Aktivasi dan deaktivasi menyegarkan rewrite rules. Importer tidak melakukan flush rewrite pada request biasa.

== Frequently Asked Questions ==

= Apa yang dibuat importer? =

Importer membuat halaman inti, pengaturan halaman depan, empat program awal, tujuh draf ekstrakurikuler, enam layanan, draf informasi, dan empat menu. Lokasi menu yang sudah terisi tidak diganti.

= Apakah aman menjalankan importer kembali? =

Ya. Penanda privat dan slug stabil mencegah duplikat. Konten yang sudah ada tidak ditimpa dan item di Sampah tidak dipulihkan otomatis.

= Apakah importer menerbitkan berita dan jadwal contoh? =

Tidak. Berita, pengumuman, agenda, dan kegiatan yang belum diverifikasi dibuat sebagai draf berlabel. Administrator harus memeriksa fakta, tanggal, nama, tautan, serta izin publikasi.

= Mengapa pengumuman lama tidak muncul di arsip? =

Arsip publik menyembunyikan pengumuman yang field Berlaku Sampai-nya telah lewat. Item tetap terlihat di admin dan tidak dihapus.

= Apa yang terjadi ketika plugin dihapus? =

Post, meta, term, menu, dan pengaturan tetap disimpan. Plugin tidak melakukan penghapusan destruktif saat uninstall.

= Apakah plugin memproses pendaftaran atau lamaran? =

Tidak. Plugin hanya menyimpan dan menampilkan informasi atau tautan. Sistem pendaftaran, pembayaran, LMS, E-Rapor, formulir, email, dan pemrosesan dokumen harus dikonfigurasi terpisah.

= Data apa yang tidak boleh dipublikasikan? =

Hindari NIK, NISN, alamat rumah, nomor pribadi, data kesehatan, dokumen sensitif, serta foto tanpa dasar izin yang sesuai.

== Changelog ==

= 1.4.0 - 2026-07-23 =

* Menambahkan halaman Struktur Organisasi berdasarkan SK tahun pelajaran 2026/2027.
* Menampilkan 19 bidang/tim, seluruh penugasan, dan tupoksi setiap jabatan.
* Mencocokkan foto dan tautan profil otomatis dari entri Guru & Tendik.
* Mempertahankan inisial aman ketika foto atau profil belum dipublikasikan.

= 1.3.0 - 2026-07-23 =

* Mengganti peran portal menjadi Waka, Guru, dan Tenaga Kependidikan.
* Membuat folder Drive pribadi otomatis berdasarkan username.
* Menambahkan unggahan Drive terotorisasi dengan batas ukuran dan validasi MIME.
* Menambahkan koneksi OAuth untuk My Drive dan tetap mendukung service account untuk Shared Drive.

= 1.2.0 - 2026-07-18 =

* Menambahkan Pusat Media privat berbasis login WordPress.
* Menambahkan peran sekolah, pemetaan folder Drive per akun, dan proxy unduhan terotorisasi.

= 1.1.0 - 2026-07-13 =

* Menambahkan Pusat Aplikasi satu pintu untuk Ujian, E-Rapor, E-Perpustakaan, SPMB, dan Gamifikasi Edu.
* Menambahkan status aplikasi dan alamat publik /aplikasi.

= 1.0.1 - 2026-07-13 =

* Menambahkan pengaturan nama, jabatan, dan pesan kepala sekolah pada menu Sekolah.

= 1.0.0 - 2026-07-13 =

* Rilis awal.
* Menambahkan CPT, taksonomi, REST meta, meta box, settings, dan kolom admin.
* Menambahkan filter arsip agenda dan pengumuman.
* Menambahkan importer demo nonce/capability-protected dan idempoten.
* Menambahkan kebijakan uninstall non-destruktif.

== Upgrade Notice ==

= 1.0.0 =

Rilis awal. Cadangkan situs dan verifikasi semua data sekolah sebelum publikasi.
