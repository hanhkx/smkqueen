=== Queen Al-Falah Core ===
Contributors: smkqueenalfalah
Requires at least: 6.2
Requires PHP: 7.4
Stable tag: 1.9.0
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
* Tautan Google Drive per divisi yang wajib login dan dapat diganti dari Pengaturan Sekolah.
* Struktur organisasi 2026/2027 dengan tupoksi dan foto tersinkron dari Guru & Tendik.
* Data prestasi terstruktur beserta penyelenggara, juara, bidang, tingkat, dan tautan sumber.
* Tujuh belas Prestasi dengan 16 foto sumber terverifikasi yang hanya mengisi slot gambar unggulan kosong; foto LBB putri menunggu dokumentasi yang tepat.
* Katalog berversi untuk enam Sarana Prasarana dan 14 profil awal PKL & Mitra Industri.
* Sebelas Ekstrakurikuler dengan manfaat, relevansi dunia kerja, dan ilustrasi fallback.
* Galeri lokal dan konten sosial Instagram, TikTok, Facebook, atau YouTube dengan URL kanonik, pemilih Media Library, serta kontrol klik/otomatis/tautan.
* Sinkronisasi Reel/video akun Instagram Profesional melalui API resmi dengan jadwal harian opsional, deduplikasi, status draf/terbit, poster lokal, dan pembaruan long-lived token.
* Delapan sampul berita Agustus 2026 dari Instagram resmi yang disalin ke Media Library dan hanya mengisi gambar unggulan yang masih kosong.
* Caption, alt text, kredit foto, dan URL Instagram sumber yang tetap dapat disunting oleh administrator.
* Pengaturan opsional Google Analytics 4 dan verifikasi Search Console; keduanya nonaktif sampai ID resmi diisi.
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

Importer demo membuat halaman inti, pengaturan halaman depan, empat program awal, tujuh kerangka ekstrakurikuler, enam layanan, draf informasi, dan empat menu. Katalog berversi kemudian melengkapi kerangka ekstrakurikuler yang masih asli. Lokasi menu yang sudah terisi tidak diganti.

= Apakah aman menjalankan importer kembali? =

Ya. Penanda privat dan slug stabil mencegah duplikat. Konten yang sudah ada tidak ditimpa dan item di Sampah tidak dipulihkan otomatis.

= Apakah importer menerbitkan berita dan jadwal contoh? =

Tidak. Berita, pengumuman, agenda, dan placeholder demo yang belum diverifikasi dibuat sebagai draf berlabel. Administrator harus memeriksa fakta, tanggal, nama, tautan, serta izin publikasi.

= Bagaimana katalog Sarana, Mitra, Ekstrakurikuler, dan Prestasi diperbarui? =

Katalog berversi menambahkan item yang belum ada dan tidak memulihkan item di Sampah. Field Ekstrakurikuler hanya dilengkapi bila masih kosong. Foto Prestasi hanya dipasang bila post belum memiliki gambar unggulan. Profil Mitra adalah data awal berbasis sumber publik; status kerja sama formal, periode, dan kuota tetap harus diverifikasi dengan dokumen sekolah.

= Bagaimana gambar dan caption berita Instagram diperbarui? =

Paket berita menggunakan salinan lokal slide pertama dari delapan unggahan Instagram resmi Agustus 2026. Gambar dimasukkan ke Media Library dan dijadikan gambar unggulan hanya ketika berita tujuan belum memiliki pilihan administrator. Alt text, caption, kredit, dan URL sumber dapat disunting melalui Media Library serta kotak Detail Terstruktur pada editor Berita.

= Bagaimana mengaktifkan Analytics dan Search Console? =

Buka Sekolah > Pengaturan > Analitik dan Verifikasi. Isi Measurement ID GA4 berformat `G-XXXXXXXXXX` dan token `google-site-verification` dari properti resmi sekolah. Field kosong tidak memuat script atau meta tag apa pun. Jangan isi Measurement ID bila Analytics sudah dipasang oleh plugin lain agar kunjungan tidak tercatat ganda.

= Apakah WordPress mengunggah file Pusat Media ke Google Drive? =

Tidak. Setelah login, pengguna hanya melihat tautan folder sesuai perannya. Semua operasi file berlangsung di Google Drive. Atur tiga URL di Sekolah > Pengaturan > Pusat Media, gunakan berbagi Drive Dibatasi, dan beri akses hanya kepada akun Google yang berwenang.

= Bagaimana menambahkan foto atau video ke Galeri? =

Untuk berkas milik sekolah, pilih sumber Lokal, pakai Gambar Utama sebagai sampul, lalu susun foto/video dengan blok Image, Gallery, atau Video. Satu video lokal juga dapat dipilih dari Media Library. Untuk konten sosial manual, pilih platform lalu tempel URL HTTPS kanonik satu postingan. Jangan memasukkan iframe mentah, token API, atau URL feed/profil ke field konten.

= Bagaimana menyinkronkan Reel/video akun Instagram? =

Gunakan akun Instagram Profesional dan Instagram API with Instagram Login. Buka Sekolah > Instagram Galeri, isi Instagram User ID serta long-lived access token dari aplikasi Meta resmi, pilih Draf atau Terbit otomatis, lalu jalankan sinkronisasi. Token disimpan privat di server dan tidak pernah dicetak kembali. Panduan lengkap tersedia di INSTAGRAM-GALLERY-SETUP.md.

= Apa perbedaan Klik untuk memuat, Muat otomatis, dan Tautan saja? =

Klik untuk memuat adalah default dan dirender oleh tema Queen Al-Falah 1.5.0 setelah pengunjung menekan tombol. Muat otomatis dapat menghubungi platform pihak ketiga segera ketika halaman dibuka. Tautan saja tidak membuat embed dan hanya membuka sumber resmi. Gunakan mode Lokal atau Tautan saja untuk kebutuhan privasi paling ketat.

= Izin apa yang diperlukan dan bagaimana privasi pengunjung dijaga? =

Plugin tidak menambah capability baru. Menyunting meta Galeri mengikuti izin `edit_post`, sedangkan unggah media mengikuti `upload_files`. Pastikan sekolah memiliki hak penggunaan dan izin publikasi media. Embed platform dapat mengirim data teknis pengunjung kepada pihak ketiga; mode Klik untuk memuat menundanya sampai interaksi, sementara Tautan saja tidak membuat embed.

= Apakah entri Galeri lama tetap kompatibel? =

Ya. Sumber kosong diperlakukan sebagai mode otomatis/kompatibilitas lama dan `_qaf_video_url` lama tetap dikenali. Filter `?sumber=local` juga menyertakan entri tanpa metadata sumber. URL lama yang tidak lagi sesuai allowlist tidak dihapus hanya karena post disimpan, tetapi harus diganti dengan URL HTTPS kanonik agar dapat disematkan.

= Mengapa pengumuman lama tidak muncul di arsip? =

Arsip publik menyembunyikan pengumuman yang field Berlaku Sampai-nya telah lewat. Item tetap terlihat di admin dan tidak dihapus.

= Apa yang terjadi ketika plugin dihapus? =

Post, meta, term, menu, media, dan pengaturan sekolah non-rahasia tetap disimpan. Token/status Instagram serta jadwal sinkronisasi dihapus agar plugin yang sudah tidak ada tidak meninggalkan akses eksternal aktif.

= Apakah plugin memproses pendaftaran atau lamaran? =

Tidak. Plugin hanya menyimpan dan menampilkan informasi atau tautan. Sistem pendaftaran, pembayaran, LMS, E-Rapor, formulir, email, dan pemrosesan dokumen harus dikonfigurasi terpisah.

= Data apa yang tidak boleh dipublikasikan? =

Hindari NIK, NISN, alamat rumah, nomor pribadi, data kesehatan, dokumen sensitif, serta foto tanpa dasar izin yang sesuai.

== Changelog ==

= 1.9.0 - 2026-08-29 =

* Menambahkan koneksi Instagram API resmi pada Sekolah > Instagram Galeri tanpa meminta atau menyimpan kata sandi.
* Menambahkan sinkronisasi manual/harian khusus Reel dan video, deduplikasi ID media/permalink, status hasil, serta pembaruan long-lived token.
* Menjaga konten lama, item di Sampah, dan suntingan administrator tetap utuh; item baru dapat dibuat sebagai Draf atau Terbit.
* Menyalin thumbnail yang valid ke Media Library pada slot gambar kosong dengan batas ukuran, MIME, dan host CDN yang ketat.
* Menambahkan ID sinkronisasi readonly, panduan konfigurasi, dan pemeriksaan kontrak URL/video.
* Menghapus kredensial Instagram saat uninstall tanpa menghapus konten Galeri atau data sekolah lain.

= 1.8.0 - 2026-08-27 =

* Menambahkan delapan sampul berita Agustus 2026 dari unggahan Instagram resmi sebagai aset lokal, tanpa hotlink CDN sementara.
* Menambahkan importer media berita non-destruktif untuk featured image, alt text, caption, kredit, dan URL sumber.
* Menambahkan field sumber Instagram dan kredit gambar pada editor Berita.
* Menambahkan pengaturan opsional Google Analytics 4 dan token verifikasi Search Console dengan validasi ketat.

= 1.7.1 - 2026-08-02 =

* Memperbarui alamat publik BeAD Group dan sumber resmi Kementerian Kesehatan untuk Puskesmas Mojo.
* Memperjelas kompetensi DKV yang relevan pada profil JTV Kediri tanpa menyatakan bidang tersebut sebagai layanan komersial yang belum terverifikasi.
* Menambahkan migrasi exact-match untuk koreksi data mitra agar nilai bawaan lama diperbarui tanpa menimpa suntingan administrator.

= 1.7.0 - 2026-07-30 =

* Menambahkan sumber Galeri Lokal, Instagram, TikTok, Facebook, dan YouTube beserta jenis media Foto, Video, atau gabungan.
* Menambahkan pemilih video Media Library, URL sosial HTTPS dengan allowlist host, perilaku embed Klik untuk memuat/Muat otomatis/Tautan saja, serta panduan editor.
* Menambahkan kolom admin Sumber, Jenis Media, Tanggal Album, dan URL Media serta filter arsip publik `?sumber=`.
* Mempertahankan kompatibilitas entri lama tanpa metadata sumber dan mencegah URL legacy terhapus saat post disimpan.
* Menegaskan bahwa plugin tidak menerima iframe mentah, token API, atau sinkronisasi feed; izin media dan izin publikasi tetap menjadi tanggung jawab administrator.

= 1.6.0 - 2026-07-29 =

* Menambahkan field dan enam entri awal Sarana Prasarana yang mudah dikelola.
* Menambahkan 14 profil awal PKL & Mitra Industri dengan keahlian, program terkait, kerja sama, sumber, dan status verifikasi.
* Menyederhanakan Pusat Media menjadi tautan Drive per divisi tanpa unggah-unduh di dalam WordPress atau kredensial Google API.
* Menambahkan 16 foto sumber terverifikasi untuk 17 Prestasi tanpa menimpa gambar unggulan yang sudah ada; foto LBB putri menunggu dokumentasi yang tepat.
* Menambahkan 11 Ekstrakurikuler dengan manfaat, relevansi dunia kerja, dan ilustrasi fallback.

= 1.5.0 - 2026-07-23 =

* Menambahkan metadata penyelenggara, juara/penghargaan, bidang, tautan sumber, dan ID sumber prestasi.
* Menambahkan kolom admin untuk memeriksa metadata prestasi.
* Menambahkan importer prestasi berversi dan idempoten yang tidak menimpa atau memulihkan konten.

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

= 1.9.0 =

Buka Sekolah > Instagram Galeri untuk menghubungkan akun Instagram Profesional melalui API resmi. Gunakan status Draf pada uji pertama dan jangan menyimpan token di field konten, ekspor, atau repository.

= 1.8.0 =

Masuk ke dashboard satu kali untuk menjalankan sinkronisasi gambar berita, lalu isi GA4/Search Console di Sekolah > Pengaturan hanya setelah ID resmi tersedia. Gunakan tema Queen Al-Falah 1.5.0 agar caption dan kredit tampil di halaman detail.

= 1.7.1 =

Koreksi profil mitra bawaan diterapkan hanya ketika nilai lama masih utuh. Suntingan administrator tidak ditimpa.

= 1.7.0 =

Periksa entri Galeri lama, pilih sumber dan jenis media bila diperlukan, lalu gunakan tema Queen Al-Falah 1.5.0 untuk tampilan klik-untuk-muat. URL lama tetap dipertahankan, tetapi hanya URL HTTPS kanonik pada platform yang didukung yang dapat disematkan.

= 1.6.0 =

Pusat Media kini memakai tautan Drive per divisi. Isi URL Waka, Guru, dan Tendik pada Pengaturan Sekolah, lalu pastikan setiap folder memakai berbagi Dibatasi.

= 1.0.0 =

Rilis awal. Cadangkan situs dan verifikasi semua data sekolah sebelum publikasi.
