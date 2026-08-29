# Queen Al-Falah

Queen Al-Falah adalah tema WordPress classic/hybrid untuk situs resmi SMK QUEEN AL-FALAH. Tema ini siap digunakan bersama Gutenberg, responsif, aksesibel, dan tidak bergantung pada page builder, framework CSS/JavaScript, atau font eksternal. Paket lengkap menyertakan plugin pendamping **Queen Al-Falah Core** untuk model konten sekolah dan penyiapan demo sekali klik; pemisahan ini menjaga konten tetap tersedia ketika tema diganti.

## Data sekolah yang disertakan

Tema menyertakan data awal berikut:

| Informasi | Nilai awal |
|---|---|
| Nama | SMK QUEEN AL-FALAH |
| NPSN | 20574699 |
| Tanggal berdiri | 21 Februari 2011 |
| Alamat | Jl. Raya Kebanan–Ploso, Ds. Ploso, Kec. Mojo, Kab. Kediri |
| Email | smkqueenalfalah@yahoo.com |
| Telepon | 03544520550 |
| Akreditasi | B |
| Program keahlian | TJKT, MPLB, DKV, Layanan Kesehatan |

Data tersebut merupakan titik awal, bukan pengganti pemeriksaan administrasi. Sebelum publikasi, administrator wajib mencocokkan data dengan dokumen sekolah terbaru dan memeriksa seluruh informasi operasional, termasuk kepala sekolah, visi-misi, statistik, PPDB, jadwal, guru, mitra, lowongan, media sosial, serta masa berlaku akreditasi.

## Kebutuhan sistem

- WordPress 6.2 atau lebih baru.
- PHP 7.4 atau lebih baru.
- HTTPS sangat disarankan.
- Hak administrator WordPress untuk aktivasi dan pengaturan awal.
- Cadangan basis data dan berkas sebelum menjalankan setup pada situs yang sudah berisi.

Lakukan uji staging sebelum pembaruan WordPress besar, PHP, plugin, tema, atau server produksi.

## Instalasi dari ZIP

1. Masuk ke Dasbor WordPress.
2. Buka **Tampilan > Tema > Tambah Tema > Unggah Tema**.
3. Pilih berkas **queen-alfalah-1.5.0.zip**.
4. Klik **Pasang Sekarang**, lalu **Aktifkan**.
5. Buka **Plugin > Tambah Plugin > Unggah Plugin**, unggah **queen-alfalah-core-1.8.0.zip**, lalu aktifkan.
6. Lanjutkan ke penyiapan awal di bawah.

## Instalasi melalui folder

1. Salin folder **queen-alfalah** ke direktori **wp-content/themes/**.
2. Masuk ke Dasbor WordPress.
3. Buka **Tampilan > Tema**.
4. Aktifkan **Queen Al-Falah**.

Jangan mengubah berkas tema langsung pada situs produksi. Gunakan child theme bila diperlukan penyesuaian kode agar perubahan tidak hilang saat pembaruan.

## Penyiapan cepat sekali klik

Penyiapan demo ditujukan untuk instalasi baru atau lingkungan staging.

1. Buat cadangan jika situs sudah memiliki konten.
2. Pastikan tema dan plugin **Queen Al-Falah Core** sudah aktif.
3. Buka **Sekolah > Penyiapan Demo**.
4. Baca ringkasan perubahan yang ditampilkan.
5. Jalankan **Siapkan Situs Demo** satu kali.
6. Periksa halaman, menu, beranda, dan konten contoh yang dibuat.
7. Ganti gambar serta tulisan contoh.
8. Periksa semua fakta sebelum membuka situs kepada publik.

Konten demo bukan data operasional final. Jangan menjalankan setup berulang tanpa meninjau konten yang sudah ada.

## Penyiapan manual

### 1. Identitas situs

Buka **Tampilan > Sesuaikan > Identitas Situs**:

- unggah logo sekolah;
- isi judul **SMK QUEEN AL-FALAH**;
- isi slogan resmi yang sudah disahkan;
- unggah ikon situs berbentuk persegi, minimal 512 × 512 piksel.

### 2. Halaman depan

1. Buat halaman **Beranda** dan **Berita** bila belum tersedia.
2. Buka **Pengaturan > Membaca**.
3. Pilih **Sebuah halaman statis**.
4. Atur **Beranda** sebagai halaman depan.
5. Atur **Berita** sebagai halaman pos.

### 3. Permalink

Buka **Pengaturan > Permalink**, pilih struktur yang mudah dibaca—misalnya **Nama tulisan**—lalu simpan. Menyimpan ulang permalink juga membantu menyegarkan rute tipe konten khusus setelah tema diaktifkan.

### 4. Menu

Buka **Tampilan > Menu**, buat menu yang diperlukan, lalu tetapkan pada lokasi yang sesuai:

- **Menu Utama** untuk navigasi utama;
- **Menu Atas** untuk tautan utilitas singkat;
- **Menu Layanan** untuk akses ke layanan sekolah;
- **Menu Footer** untuk navigasi bagian bawah.

Struktur yang disarankan tersedia di docs/CONTENT-GUIDE.md.

Pastikan:

- tidak ada menu kosong atau duplikat;
- tautan eksternal diberi label yang jelas;
- item penting seperti PPDB, Kompetensi Keahlian, dan Kontak mudah ditemukan;
- menu tetap dapat digunakan dengan keyboard dan pada layar kecil.

### 5. Widget

Tema menyediakan **Sidebar Artikel** serta tiga area **Footer Kolom 1–3**. Buka **Tampilan > Widget** untuk mengisinya. Gunakan widget seperlunya; footer yang terlalu padat menyulitkan pengunjung ponsel.

### 6. Customizer

Buka **Tampilan > Sesuaikan** dan tinjau:

- **Identitas & Kontak Sekolah**: fallback tampilan jika plugin pendamping belum aktif;
- **Hero Beranda**: judul, ringkasan, gambar, CTA, serta background JPG/PNG/WebP/GIF yang mudah diganti;
- **Beranda & Statistik**: empat nilai/label, tanggal pembaruan, serta tombol WhatsApp;
- **Warna & Header**: palet utama dan header sticky;
- **Media Sosial**: URL akun resmi sekolah.

Saat plugin pendamping aktif, ubah data portabel seperti nama legal, NPSN, alamat, telepon, email, visi, dan URL pendaftaran melalui **Sekolah > Pengaturan**. Customizer tetap menangani tampilan, hero, gambar, warna, dan statistik beranda.

### Background gambar/GIF landing page

1. Buka **Tampilan > Sesuaikan > Hero Beranda**.
2. Pilih **Background landing page (gambar/GIF)** lalu unggah dari Media Library.
3. Pilih mode **Penuhi area (cover)** untuk foto besar, atau salah satu mode **Ulangi** untuk tekstur/GIF kecil.
4. Atur posisi background dan kegelapan overlay sampai teks mudah dibaca.
5. Klik **Terbitkan**. Untuk mengganti background, pilih media lain; untuk menghapusnya, klik **Hapus**.

GIF akan bergerak dan mengulang otomatis. Gunakan animasi yang halus dan berukuran kecil agar halaman tetap nyaman serta cepat dimuat.

Kosongkan tautan yang belum resmi. Jangan memasang nomor pribadi staf sebagai kontak publik tanpa persetujuan.

## Tipe konten

Plugin pendamping menyediakan:

1. **Program Keahlian** — profil program, kompetensi, fasilitas, peluang kerja, sertifikasi, dan proyek siswa.
2. **Guru** — nama, jabatan, bidang, foto, dan profil singkat.
3. **Pengumuman** — informasi mendesak atau administratif.
4. **Agenda** — kegiatan dengan tanggal, waktu, tempat, dan penyelenggara.
5. **Prestasi** — capaian siswa, guru, atau sekolah.
6. **Ekstrakurikuler** — profil, jadwal, pembina, program, dan prestasi.
7. **Layanan** — tautan menuju layanan sekolah seperti E-Learning, E-Rapor, perpustakaan, atau pengaduan.
8. **Galeri** — dokumentasi foto/video lokal atau posting sosial terpilih yang sudah mendapat izin.
9. **Mitra** — perguruan tinggi, dunia usaha, dunia industri, dan lembaga pendukung.
10. **Lowongan** — peluang kerja yang sudah diverifikasi BKK.
11. **Alumni** — kisah alumni dan hasil tracer yang aman dipublikasikan.
12. **Sarana Prasarana** — profil fasilitas, kapasitas, status, foto, dan informasi penggunaan.

Isikan gambar unggulan, ringkasan, dan data penting pada setiap entri. Caption gambar unggulan dapat disunting dari Media Library dan akan tampil bersama kredit/sumber berita bila plugin pendamping menyediakannya. Gunakan judul yang deskriptif; jangan memasukkan tanggal ke judul bila tanggal sudah memiliki kolom tersendiri.

## Galeri lokal dan sosial

Setiap entri Galeri dapat memakai salah satu sumber berikut:

- **Lokal**: gunakan Gambar Unggulan sebagai sampul kartu/detail, susun foto melalui blok Gallery/Image, lalu tambahkan video melalui blok Video atau pilih satu berkas video dari Media Library pada kolom Galeri.
- **Instagram, TikTok, Facebook, atau YouTube**: pilih platform dan masukkan URL HTTPS kanonik menuju satu postingan/video publik. Jangan menempelkan kode iframe, script, token, kata sandi, atau HTML embed.

Untuk sumber sosial tersedia tiga perilaku:

- **Klik untuk memuat (default)**: permintaan ke penyedia baru dilakukan setelah pengunjung menekan tombol.
- **Muat otomatis**: embed dimuat saat halaman dibuka dan dapat membuat koneksi atau cookie pihak ketiga; gunakan hanya setelah penilaian privasi dan kebijakan cookie.
- **Tautan saja**: tidak memuat embed pihak ketiga dan hanya membuka sumber asli.

Tema tidak menyinkronkan feed sosial secara otomatis. Konten sosial harus tetap publik dan mengizinkan embed; postingan privat, dihapus, dibatasi wilayah/usia, atau diblokir penyedia mungkin tidak dapat tampil. Tautan ke sumber asli tetap disediakan sebagai fallback.

Sebelum menerbitkan, pastikan sekolah memiliki izin publikasi dan hak pakai, cantumkan kredit/keterangan yang diperlukan, isi alt text untuk media lokal, hapus metadata lokasi yang tidak dibutuhkan, dan lindungi identitas siswa sesuai kebijakan sekolah.

## Program keahlian awal

Empat nama program yang disertakan:

- **TJKT — Teknik Jaringan Komputer dan Telekomunikasi**
- **MPLB — Manajemen Perkantoran dan Layanan Bisnis**
- **DKV — Desain Komunikasi Visual**
- **Layanan Kesehatan**

Administrator perlu memeriksa nomenklatur resmi, konsentrasi, status penerimaan siswa, kepala program, fasilitas, sertifikasi, kurikulum, dan peluang lulusan sebelum menerbitkan halaman program.

## Susunan beranda yang disarankan

1. Pengumuman prioritas.
2. Hero dan CTA PPDB.
3. Akses cepat ke layanan penting.
4. Sambutan kepala sekolah.
5. Program keahlian.
6. Pengumuman dan agenda terbaru.
7. Berita sekolah.
8. Teaching Factory atau proyek siswa.
9. Statistik terverifikasi.
10. Prestasi.
11. Mitra industri.
12. Fasilitas.
13. Ekstrakurikuler.
14. Alumni.
15. Galeri.
16. Peta dan kontak.

Jangan memenuhi beranda dengan seluruh isi. Tampilkan ringkasan dan arahkan pengunjung ke arsip atau halaman detail.

## Gambar dan media

- Gunakan gambar unggulan dengan rasio konsisten, idealnya 16:9 untuk kartu berita.
- Gunakan foto minimal 1600 × 900 piksel untuk hero.
- Gunakan WebP bila memungkinkan dan kompres sebelum unggah.
- Isi teks alternatif berdasarkan tujuan gambar, bukan nama berkas.
- Hapus metadata lokasi/EXIF yang tidak diperlukan.
- Jangan mengunggah foto anak, dokumen, atau identitas pribadi tanpa dasar izin yang sesuai.
- Gunakan logo mitra hanya selama kerja sama dan izin penggunaan masih berlaku.

Panduan editorial dan privasi lebih lengkap tersedia di docs/CONTENT-GUIDE.md.

## PPDB

Area PPDB pada tema menyajikan informasi dan CTA. Sebelum mengaktifkannya:

- pastikan tahun ajaran benar;
- periksa jadwal buka/tutup;
- periksa jalur, kuota, syarat, biaya, dan narahubung;
- pastikan URL pendaftaran menggunakan HTTPS dan benar-benar dikelola pihak berwenang;
- beri tanda jelas bila pendaftaran belum dibuka atau sudah ditutup;
- jangan menjanjikan penerimaan otomatis.

Tema tidak menggantikan sistem pendaftaran, verifikasi dokumen, pembayaran, atau penyimpanan data calon siswa.

## Lowongan dan BKK

Setiap lowongan harus diperiksa sumbernya, perusahaan, lokasi, persyaratan, tanggal kedaluwarsa, dan kanal lamaran. Hapus atau arsipkan lowongan kadaluarsa. Jangan meminta pelamar mengirim dokumen sensitif melalui komentar WordPress atau nomor pribadi.

## Aksesibilitas dan kualitas

Sebelum peluncuran:

- uji navigasi dengan keyboard;
- pastikan fokus terlihat;
- pertahankan hierarki judul yang logis;
- periksa kontras teks dan tombol;
- jangan mengandalkan warna saja untuk menyampaikan status;
- tambahkan transkrip atau ringkasan pada video;
- hindari teks penting yang tertanam di dalam gambar;
- uji pada ponsel dengan koneksi lambat.

## Dependensi paket

Tema tetap dapat menampilkan halaman dan berita WordPress tanpa plugin pendamping. Namun, untuk seluruh tipe konten sekolah, meta terstruktur, pengaturan sekolah portabel, serta setup demo, aktifkan plugin **Queen Al-Falah Core** yang disertakan. Paket tidak memerlukan:

- page builder;
- framework CSS atau JavaScript eksternal;
- font dari CDN;
- plugin CPT pihak ketiga;
- plugin slider;
- akun layanan pihak ketiga.

Layanan seperti formulir, SMTP, keamanan, cadangan, cache, LMS, E-Rapor, analitik, dan pendaftaran daring dapat ditambahkan oleh pengelola sesuai kebijakan sekolah. Catat dasar pemrosesan data dan kebijakan privasinya.

## Checklist sebelum terbit

- [ ] Data identitas sekolah cocok dengan dokumen resmi terbaru.
- [ ] Nama kepala sekolah, struktur organisasi, dan guru sudah disetujui.
- [ ] Visi, misi, slogan, dan sambutan bukan teks contoh.
- [ ] Nomenklatur empat program keahlian sudah diperiksa.
- [ ] Akreditasi mencantumkan status dan masa berlaku yang tepat.
- [ ] Jadwal, syarat, biaya, kuota, dan tautan PPDB sudah diperiksa.
- [ ] Statistik memiliki sumber, periode, dan tanggal pembaruan.
- [ ] Nomor telepon, email, WhatsApp, peta, dan akun sosial sudah diuji.
- [ ] Lowongan belum kedaluwarsa dan sumbernya terverifikasi.
- [ ] Logo mitra dan klaim kerja sama masih sah.
- [ ] Semua foto mempunyai hak pakai dan izin publikasi.
- [ ] Tidak ada NIK, NISN, NIP penuh, alamat pribadi, atau data kesehatan.
- [ ] Tautan, pencarian, menu ponsel, dan formulir eksternal sudah diuji.
- [ ] Cadangan, HTTPS, pembaruan, dan akun administrator sudah diamankan.

## Pemecahan masalah

### Arsip tipe konten menampilkan 404

Buka **Pengaturan > Permalink**, lalu klik **Simpan Perubahan** tanpa harus mengganti struktur.

### Beranda tidak memakai tata letak sekolah

Pastikan halaman depan statis sudah dipilih di **Pengaturan > Membaca** dan halaman yang benar ditetapkan sebagai Beranda.

### Menu tidak tampil

Pastikan menu sudah dibuat dan ditetapkan ke lokasi navigasi utama pada **Tampilan > Menu**.

### Gambar terlalu berat

Ubah ke WebP, kompres, dan unggah ukuran yang sesuai kebutuhan tampilan. Hindari memakai foto kamera beresolusi penuh tanpa optimasi.

### Email dari situs tidak terkirim

Tema tidak menyediakan layanan pengiriman email. Periksa konfigurasi server atau gunakan solusi SMTP yang disetujui pengelola situs.

## Dokumentasi lain

- docs/CONTENT-GUIDE.md — struktur halaman, alur editorial, data yang perlu diverifikasi, dan panduan privasi.
- docs/FEATURES.md — inventaris fitur dan batas tanggung jawab tema.
- docs/SOURCES.md — sumber referensi struktur dan data awal.
- CHANGELOG.md — riwayat versi.

## Lisensi

Queen Al-Falah didistribusikan dengan GNU General Public License v2 atau versi yang lebih baru.
