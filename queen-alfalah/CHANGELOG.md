# Changelog

Semua perubahan penting pada tema Queen Al-Falah dicatat di dokumen ini.

## 1.11.0 — 2026-10-16

- Menambahkan background video MP4/WebM hero yang mudah diganti melalui Customizer sebagai alternatif GIF berukuran besar.
- Mencegah gambar/GIF latar dimuat ketika video aktif, memakai preload metadata, dan menonaktifkan animasi untuk pengguna yang memilih pengurangan gerak.
- Menambahkan meta description fallback ketika plugin SEO tidak aktif.
- Meningkatkan kontras teks pengumuman, label Prestasi, penanda Jalur Karier, serta judul dan tautan footer berdasarkan audit Lighthouse Oktober.

## 1.10.1 — 2026-10-07

- Memindahkan bagian Lowongan Kerja Terbaru tepat setelah Jalur Pengembangan Karier.
- Mempertahankan semua data lowongan, filter kedaluwarsa, dan kontrol Customizer yang sudah tersedia.

## 1.10.0 — 2026-10-07

- Menampilkan hanya aplikasi berstatus Aktif yang memiliki URL HTTP/HTTPS lengkap pada Beranda dan Pusat Aplikasi.
- Menggunakan Status Aplikasi dan Atribut Urutan yang sudah dapat dikelola administrator, sehingga kartu dapat disembunyikan atau diurutkan tanpa menghapus data.
- Menambahkan blok Lowongan Kerja Terbaru di bagian bawah Beranda; lowongan lama tetap berada di arsip Bursa Kerja dan lowongan kedaluwarsa tidak dipromosikan di Beranda.
- Menambahkan pengaturan tampil/sembunyi, judul, deskripsi, dan jumlah 3–8 kartu lowongan melalui Tampilan → Sesuaikan → Beranda & Statistik.

## 1.9.2 — 2026-10-03

- Menstandarkan halaman detail Berita, Galeri, Prestasi, dan Karya Siswa dengan ringkasan pembuka, CTA kontekstual, dan tiga rekomendasi konten sejenis.
- Mempertahankan isi, fakta, tanggal, kredit, sumber, dan media lama; pola editorial baru otomatis berlaku pada postingan lama maupun yang akan datang.
- Menambahkan tata letak responsif dan aksesibel untuk panel pembuka, CTA, serta rekomendasi bacaan pada desktop, tablet, dan ponsel.
- Mencakup perbaikan poster video Galeri dari versi 1.9.1.

## 1.9.1 — 2026-10-03

- Menampilkan gambar unggulan sebagai poster pada pemutar video Galeri lokal sehingga sampul tetap jelas sebelum video diputar.
- Menerapkan perbaikan secara otomatis pada seluruh galeri lama dan baru tanpa mengubah video, caption, tanggal album, atau sumber media.

## 1.9.0 — 2026-09-23

- Menambahkan navigasi bawah bergaya aplikasi untuk ponsel dan tablet hingga lebar 1023 piksel dengan akses cepat ke Beranda, Berita, Aplikasi, PPDB, dan menu lengkap.
- Menambahkan penanda halaman aktif, target sentuh yang nyaman, dukungan safe area perangkat, serta posisi tombol WhatsApp dan kembali ke atas yang tidak tertutup navigasi.
- Memperluas drawer menu lengkap hingga tablet dan menyelaraskan status buka/tutup pada tombol navigasi bawah serta tombol penutup.
- Memperjelas hover, fokus keyboard, dan penanda halaman aktif pada navigasi desktop tanpa mengubah susunan menu.

## 1.7.4 — 2026-09-06

- Memastikan target tombol berbagi minimal 44 piksel dan label minimal 13 piksel, termasuk ketika ukuran huruf dasar tema lebih kecil. Disempurnakan setelah pengujian tampilan ponsel online.

## 1.7.3 — 2026-09-06

- Mengubah tautan berbagi teks menjadi panel berikon WhatsApp, Facebook, Telegram, dan X pada semua detail postingan publik serta tipe konten sekolah.
- Menambahkan salin tautan kanonik dengan fallback salin manual, serta berbagi native pada perangkat yang mendukung HTTPS/Web Share.
- Tombol ramah keyboard/ponsel, pengumuman status aksesibel, ikon lokal, tanpa SDK atau pelacak pihak ketiga.
- Tidak menampilkan tombol berbagi pada draf, privat, terjadwal, pratinjau, dan konten berpassword. Konten, media, urutan galeri, dan tombol akun resmi tidak berubah.

## 1.7.2 — 2026-09-06

- Menampilkan Tanggal Album pada kartu galeri, detail album, dan galeri beranda.
- Menandai tanggal yang belum diketahui tanpa menggantinya dengan tanggal publikasi WordPress.
- Memberi penjelasan urutan tanggal album terbaru–terlama bersama Core 1.14.0.

## 1.7.0 — 2026-09-05

### Ditambahkan

- Panel tombol akun resmi Instagram, Facebook, YouTube, dan TikTok pada setiap detail berita serta tipe konten publik sekolah.
- Satu sumber pengaturan sosial untuk panel artikel, footer, halaman Kontak, dan schema; kanal tanpa URL tidak ditampilkan.
- Tata letak tombol yang responsif, ramah keyboard, memiliki target sentuh memadai, dan menghormati preferensi pengurangan gerak.

## 1.6.3 — 2026-08-30

### Ditambahkan

- Bagian **Prestasi SMK Queen Al-Falah** pada beranda yang otomatis menampilkan tiga capaian/partisipasi terbaru dan tombol menuju arsip lengkap.
- Pemilihan kartu berdasarkan tanggal Prestasi terstruktur serta penyaring sumber agar beberapa pemenang dari satu lomba tidak memenuhi seluruh beranda.
- Empty state yang jujur ketika plugin pendamping belum aktif atau belum ada Prestasi yang diterbitkan.

## 1.6.2 — 2026-08-30

### Diubah

- Poster Prestasi tetap berada dalam kartu rasio 1:1 tetapi memakai ukuran gambar tanpa crop dan mode `contain`, sehingga teks, siswa, dan hasil pada poster vertikal maupun lebar tetap terlihat utuh.
- Efek zoom pada kartu Prestasi dinonaktifkan agar tepi dokumentasi tidak terpotong saat pointer diarahkan ke kartu.

## 1.6.1 — 2026-08-30

### Diubah

- Seluruh thumbnail kartu Berita, Program Keahlian, Guru/Tendik, Prestasi, Galeri, Mitra, Sarana Prasarana, dan arsip konten lain memakai rasio 1:1.
- Foto Guru/Tendik serta logo Program Keahlian dan Mitra memakai mode `contain` agar visual tampil utuh di dalam kotak.
- Foto berita, galeri, prestasi, dan konten fotografis tetap memenuhi kotak dengan mode `cover`; foto orang dan Prestasi diposisikan dari bagian atas untuk menjaga wajah tetap terlihat.
- Ukuran gambar WordPress baru memisahkan thumbnail persegi dan visual tanpa crop; media lama tetap memiliki fallback ke berkas sumber sampai thumbnail diregenerasi.

## 1.6.0 — 2026-08-30

### Ditambahkan

- Embed Google Maps berbasis latitude/longitude tervalidasi pada beranda dan halaman Kontak.
- Tautan **Buka di Google Maps** dan kartu tautan lama sebagai fallback bila koordinat tidak valid.
- Sebelas ilustrasi kategori WebP untuk sekolah, orang, Galeri, Prestasi, layanan, karier, serta lima kelompok Sarana Prasarana.
- Resolver visual terpusat yang memprioritaskan Gambar Unggulan administrator dan ilustrasi khusus entri.
- Label serta caption **Ilustrasi** agar visual sintetis tidak dianggap sebagai dokumentasi asli.
- Panduan visual fallback dan roadmap pengelolaan website September 2026–Agustus 2027.

### Keamanan dan privasi

- Sumber iframe peta hanya dibentuk dari koordinat numerik pada host Google yang tetap; URL peta bebas tidak pernah dipakai sebagai iframe.
- Peta memakai lazy loading dan memerlukan penjelasan koneksi pihak ketiga pada kebijakan privasi sekolah.
- Fallback visual tidak membuat attachment, mengubah database, atau menimpa media administrator.

## 1.5.0 — 2026-08-27

### Ditambahkan

- Caption gambar unggulan berita yang diambil dari field Caption Media Library.
- Kredit foto dan tautan ke unggahan Instagram sumber saat metadata tersedia.
- Gaya caption responsif yang menyatu dengan kartu gambar pada halaman detail.
- Pencegahan halaman melebar akibat panel navigasi seluler yang sedang tertutup.

## 1.4.0 — 2026-07-30

### Ditambahkan

- Filter sumber Galeri untuk media lokal, Instagram, TikTok, Facebook, dan YouTube.
- Tampilan media lokal dari Gambar Unggulan, blok konten, dan video Media Library.
- Tampilan posting sosial dari URL HTTPS kanonik tanpa menerima iframe, script, token, atau kata sandi.
- Pilihan **Klik untuk memuat** sebagai default yang lebih ramah privasi, **Muat otomatis**, dan **Tautan saja**.
- Tautan sumber sebagai fallback bila konten sosial privat, dihapus, dibatasi, atau gagal dimuat.

### Privasi dan integrasi

- Tidak ada sinkronisasi feed sosial otomatis.
- Permintaan ke penyedia sosial pada mode default baru dilakukan setelah tindakan pengunjung.
- Mode otomatis tetap memerlukan penilaian privasi, cookie, izin publikasi, hak cipta, dan perlindungan data siswa oleh pengelola.

## 1.3.0 — 2026-07-29

### Ditambahkan

- Arsip khusus PKL & Mitra Industri dengan penjelasan status verifikasi.
- Tampilan field terstruktur untuk Sarana Prasarana, Mitra, Ekstrakurikuler, dan Prestasi.
- Ilustrasi lokal untuk sebelas Ekstrakurikuler sebagai fallback gambar.
- Dukungan foto sumber Prestasi yang disinkronkan plugin ke slot gambar unggulan kosong.

## 1.2.0 — 2026-07-23

### Ditambahkan

- Detail Prestasi: penerima, penyelenggara, juara/penghargaan, bidang, ruang lingkup, tanggal, dan tautan sumber.
- Ringkasan Prestasi yang lebih informatif pada kartu arsip.

## 1.0.1 — 2026-07-13

### Ditambahkan

- Background gambar atau GIF khusus hero landing page.
- Upload dan penggantian background melalui Customizer tanpa mengubah kode.
- Pilihan mode cover, repeat, repeat-x, dan repeat-y.
- Pilihan posisi background serta pengaturan kegelapan overlay.
- GIF bergerak berulang otomatis dan disembunyikan bagi pengguna yang memilih pengurangan gerak.

## 1.0.0 — 2026-07-13

### Ditambahkan

- Rilis awal tema WordPress Queen Al-Falah.
- Arsitektur classic/hybrid yang siap digunakan bersama Gutenberg.
- Tata letak responsif dan pola aksesibilitas untuk navigasi serta konten.
- Plugin pendamping Queen Al-Falah Core dengan penyiapan demo sekali klik untuk membentuk struktur awal situs.
- Tipe konten Program Keahlian.
- Tipe konten Guru.
- Tipe konten Pengumuman.
- Tipe konten Agenda.
- Tipe konten Prestasi.
- Tipe konten Ekstrakurikuler.
- Tipe konten Layanan.
- Tipe konten Galeri.
- Tipe konten Mitra.
- Tipe konten Lowongan.
- Tipe konten Alumni.
- Tipe konten Sarana Prasarana.
- Pengaturan Customizer untuk kontak, hero, PPDB, statistik, dan media sosial.
- Data awal SMK QUEEN AL-FALAH: NPSN, tanggal berdiri, alamat, email, telepon, akreditasi, serta empat program keahlian.
- Program awal TJKT, MPLB, DKV, dan Layanan Kesehatan.
- Dukungan logo, menu, gambar unggulan, blok lebar, dan editor Gutenberg.
- Struktur beranda untuk profil sekolah, program, informasi, agenda, prestasi, mitra, alumni, galeri, dan kontak.
- Dokumentasi instalasi, konfigurasi, fitur, tata kelola konten, serta privasi foto/siswa.
- Implementasi tanpa dependensi wajib, page builder, framework eksternal, atau font CDN.

### Catatan rilis

- Konten demo harus ditinjau dan diganti sebelum situs produksi diterbitkan.
- Administrator wajib memeriksa data operasional, masa akreditasi, status program, PPDB, statistik, staf, mitra, lowongan, kontak, tautan layanan, dan izin media.
- Uji staging, cadangan, HTTPS, serta pembaruan WordPress/PHP tetap menjadi tanggung jawab pengelola situs.
