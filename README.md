# SMK Queen Al-Falah WordPress

Tema dan plugin pendamping WordPress untuk website SMK Queen Al-Falah. Project menyediakan landing page sekolah, profil, berita, pengumuman, agenda, program keahlian, PPDB, galeri, layanan sekolah, serta Pusat Aplikasi satu pintu.

## Komponen

- `queen-alfalah/` — tema WordPress Queen Al-Falah versi 1.11.0.
- `queen-alfalah-core/` — plugin pendamping versi 1.26.1 untuk tipe konten, pembaruan berita/prestasi/galeri dari kanal resmi, audit kelengkapan konten Oktober, standardisasi narasi yang dapat dipulihkan, optimasi gambar WebP, sinkronisasi Galeri Instagram, penilaian kinerja guru, media berita, Bursa Kerja terverifikasi, direktori Guru & Tendik, akses Waka, katalog gambar Mitra DUDI, analitik opsional, dan pengaturan sekolah.

## Fitur utama

- Landing page responsif dengan background gambar/GIF atau video MP4/WebM yang dapat diganti; mode video mencegah GIF besar ikut dimuat dan menghormati preferensi pengurangan gerak.
- Pusat Aplikasi hanya menampilkan layanan berstatus Aktif dengan URL valid; status, URL, ikon, tab baru, dan urutan dapat dikelola dari dashboard tanpa menghapus entri.
- Lowongan Kerja terbaru tampil langsung setelah Jalur Pengembangan Karier dan dapat dikustom melalui Customizer; seluruh lowongan lama tetap tersimpan pada Bursa Kerja.
- Halaman detail Berita, Galeri, Prestasi, dan Karya Siswa memiliki ringkasan pembuka, CTA kontekstual, serta rekomendasi konten sejenis yang otomatis berlaku untuk postingan lama dan baru tanpa menimpa narasi tersimpan.
- Navigasi bawah bergaya aplikasi pada ponsel dan tablet, dengan lima akses cepat, area aman perangkat, dan menu lengkap dalam drawer; navigasi desktop dipertahankan.
- Embed Google Maps berbasis koordinat tervalidasi pada beranda dan halaman Kontak, dengan tautan eksternal sebagai fallback.
- Identitas, kontak, kepala sekolah, logo, warna, dan konten yang dapat dikelola dari dashboard.
- Berita, pengumuman, agenda, prestasi, guru/tendik, program keahlian, galeri, BKK, dan PPDB.
- Galeri terpadu yang dapat memuat foto/video sekolah dari Media Library atau konten publik Instagram, TikTok, Facebook, dan YouTube, lengkap dengan penyaring sumber dan opsi privasi klik-untuk-muat.
- Gambar unggulan Galeri lokal otomatis menjadi poster pemutar video sehingga sampul terlihat sebelum video diputar.
- Sinkronisasi resmi Reel/video akun Instagram Profesional ke Galeri, dengan jadwal harian opsional, pencegahan duplikasi, status draf/terbit, poster lokal, dan pembaruan long-lived token.
- Pusat Aplikasi `/aplikasi/` untuk Ujian Online, E-Rapor, E-Perpustakaan, SPMB, dan Gamifikasi Edu.
- Penilaian Kinerja Guru di `/penilaian-kinerja-guru/`, dengan nama dan kelas lengkap, sepuluh aspek pengalaman belajar, tiga refleksi tertulis, pencegahan pengisian ganda per guru/semester, serta grafik dan ringkasan otomatis khusus administrator.
- Pusat Media privat `/pusat-media/` dengan login WordPress dan satu tautan Google Drive yang dapat diganti untuk tiap divisi Waka/Guru/Tendik.
- Hak akses Waka terpisah untuk mengunggah dokumen ke Media Library serta membuat, menyunting, dan menerbitkan Pengumuman tanpa akses ke Berita, Halaman, plugin, pengguna, atau pengaturan situs.
- Struktur Organisasi 2026/2027 lengkap dengan tupoksi dan foto yang tersinkron dari Guru & Tendik.
- Importer non-destruktif menambahkan 13 personel internal yang belum tercatat (5 guru/pimpinan dan 8 tendik/pembantu pelaksana), mempertahankan 53 profil lama, dan menghubungkan 11 variasi ejaan ke foto/profil yang sudah ada.
- Katalog Sarana Prasarana yang mudah dikelola dengan lokasi, fungsi, fitur, akses, pengelola, kapasitas, kondisi, dan tanggal pemeriksaan.
- Audit Kelengkapan administrator untuk Program Keahlian, Guru/Tendik, Sarana Prasarana, Ekstrakurikuler, dan status Struktur Organisasi, termasuk Penanggung Jawab Data serta Tanggal Tinjau Terakhir.
- Empat belas profil awal PKL & Mitra Industri beserta bidang keahlian, jurusan terkait, bentuk kerja sama, sumber, dan status verifikasi.
- Lima belas kartu logo/foto Mitra DUDI lokal yang tidak di-hotlink dan tidak menimpa gambar unggulan pilihan administrator; lima identitas yang belum terverifikasi diberi penanda sementara.
- Arsip Prestasi berisi 29 halaman capaian dan partisipasi tahun 2023–2026 tanpa menimpa 17 entri lama. Dua puluh empat halaman menerima gambar sumber lokal pada slot yang masih kosong; lima halaman memakai fallback berlabel ilustrasi agar tidak salah atribusi.
- Beranda menampilkan tiga Prestasi/Partisipasi terbaru dari sumber kegiatan yang berbeda dan menyediakan tombol menuju arsip Prestasi lengkap.
- Dua belas berita Agustus–September 2026 dilengkapi sampul lokal dari unggahan Instagram resmi, caption, alt text, kredit, dan tautan sumber tanpa hotlink CDN sementara; paket terbaru juga menambahkan berita Maulid Nabi 1448 H dan sampul asli berita Kompetisi Pembawa Berita JTV Kediri.
- Google Analytics 4 dan verifikasi Search Console dapat diaktifkan dari Pengaturan Sekolah setelah ID resmi tersedia; field kosong tidak memuat integrasi pihak ketiga.
- Sebelas halaman ekstrakurikuler dengan manfaat, relevansi dunia kerja, dan ilustrasi bawaan sebagai fallback gambar.
- Sebelas ilustrasi WebP kategori yang otomatis mengisi tampilan kosong secara non-destruktif dan selalu diberi label **Ilustrasi**.
- Thumbnail kartu 1:1 pada berita dan seluruh arsip konten; foto Guru/Tendik, logo Program Keahlian/Mitra, serta poster Prestasi ditampilkan utuh tanpa crop paksa.
- Struktur ZIP tema dan plugin yang kompatibel dengan pemasang WordPress.
- Dukungan penggunaan offline melalui WordPress dan Laragon.

Profil mitra merupakan data awal berbasis sumber publik. Status kerja sama formal, periode, kuota, dan penempatan wajib dicocokkan kembali dengan MoU atau arsip sekolah sebelum diumumkan sebagai kerja sama aktif.

## Persyaratan

- WordPress 6.2 atau lebih baru.
- PHP 7.4 atau lebih baru.

## Instalasi

1. Jalankan `powershell -ExecutionPolicy Bypass -File .\build-packages.ps1` dari PowerShell untuk membuat ZIP tema dan plugin yang kompatibel dengan WordPress.
2. Unggah `dist/queen-alfalah-1.11.0.zip` melalui **Tampilan → Tema → Tambah Tema**.
3. Unggah `dist/queen-alfalah-core-1.26.1.zip` melalui **Plugin → Tambah Plugin**.
4. Aktifkan plugin dan tema.
5. Buka **Sekolah → Pengaturan** untuk melengkapi identitas sekolah.
6. Buka **Pengaturan → Permalink**, lalu simpan ulang permalink.

Jangan membuat ulang paket plugin dengan pemampat yang menyimpan pemisah jalur Windows (`\`). WordPress memerlukan file utama pada jalur portabel `queen-alfalah-core/queen-alfalah-core.php`.

## Membangun paket WordPress

```powershell
powershell -ExecutionPolicy Bypass -File .\build-packages.ps1
```

Skrip membaca versi dari header tema/plugin, membuat arsip di folder `dist`, menggunakan pemisah jalur `/`, dan memverifikasi bahwa `style.css` serta file utama plugin berada pada lokasi yang dikenali WordPress.

Untuk membangun hanya salah satu komponen:

```powershell
powershell -ExecutionPolicy Bypass -File .\build-packages.ps1 -Component Plugin
powershell -ExecutionPolicy Bypass -File .\build-packages.ps1 -Component Theme
```

Jika WordPress menampilkan **Plugin file does not exist**, pastikan hasil ekstraksi tepat seperti berikut:

```text
wp-content/plugins/queen-alfalah-core/queen-alfalah-core.php
```

Hapus folder plugin yang kosong atau salah tingkat, lalu unggah kembali ZIP hasil skrip di atas. Data sekolah tidak dihapus saat folder kode plugin diganti.

## Pengembangan lokal

Salin kedua folder ke instalasi WordPress lokal:

```text
wp-content/themes/queen-alfalah
wp-content/plugins/queen-alfalah-core
```

Aktifkan melalui dashboard WordPress. Jangan menyimpan `wp-config.php`, database, kata sandi, atau berkas unggahan pengguna di repository.

## Pusat Media privat

Plugin membuat peran **Waka Sekolah**, **Guru**, dan **Tenaga Kependidikan**, serta halaman `/pusat-media/`. Password memakai autentikasi WordPress dan selalu disimpan sebagai hash oleh WordPress. Guru dan Tendik diarahkan ke Pusat Media tanpa dashboard. Waka memperoleh dashboard terbatas untuk Media Library dan Pengumuman saja.

Administrator dapat membuka **Sekolah → Akun Waka** untuk melihat empat username tetap dari struktur organisasi dan membuat hanya akun yang belum ada. Password acak 32 karakter ditampilkan satu kali tanpa disimpan pada Git, URL, atau laporan; Waka wajib menggantinya saat pertama login. Alat tidak mengarang alamat email dan tidak mengubah akun yang username atau nama personelnya sudah terdeteksi.

Isi tiga alamat folder di **Sekolah → Pengaturan → Pusat Media**: Folder Google Drive Waka, Guru, dan Tendik. Pengguna portal hanya melihat tautan yang sesuai dengan perannya; administrator dapat melihat semua tautan untuk pemeriksaan. Tautan dapat diganti kapan saja saat folder penuh, dipindahkan, atau mengalami kendala.

Pusat Media tidak mengunggah, mengunduh, atau memproksi file Drive. Dokumen internal tetap dikelola langsung di Google Drive, sehingga aturan berbagi Drive merupakan batas keamanan sebenarnya. Gunakan mode berbagi **Dibatasi** dan beri izin hanya kepada akun Google yang berwenang. Media Library hanya untuk lampiran Pengumuman yang memang boleh dipublikasikan; jangan unggah dokumen rahasia ke sana.

Lihat panduan lengkap di [`queen-alfalah-core/GOOGLE-DRIVE-SETUP.md`](queen-alfalah-core/GOOGLE-DRIVE-SETUP.md).

## Peta, visual fallback, dan roadmap

- Atur koordinat serta tautan luar peta melalui **Sekolah → Pengaturan → Kontak dan Lokasi**. Tema tidak memakai URL bebas sebagai sumber iframe.
- Gambar Unggulan administrator selalu mengalahkan ilustrasi fallback. Panduan: [`queen-alfalah/docs/VISUAL-FALLBACKS.md`](queen-alfalah/docs/VISUAL-FALLBACKS.md).
- Roadmap fungsi, kaji ulang, benchmark, dan KPI 2026/2027: [`queen-alfalah/docs/ROADMAP-2026-2027.md`](queen-alfalah/docs/ROADMAP-2026-2027.md).

## Galeri lokal dan sosial media

Buat atau sunting entri melalui **Galeri → Tambah Baru**. Gunakan **Gambar Unggulan** sebagai sampul, lalu pilih salah satu mode berikut:

- **Lokal / Media Library** — unggah foto melalui blok Gambar/Galeri dan pilih video lokal pada panel Detail Terstruktur atau melalui blok Video.
- **Instagram, TikTok, Facebook, atau YouTube** — tempel URL HTTPS kanonik satu postingan/video publik. Jangan menempel kode iframe.

Pilihan **Klik untuk memuat** menjadi bawaan agar koneksi ke platform pihak ketiga baru dilakukan setelah tindakan pengunjung. Pilihan **Muat otomatis** dan **Tautan saja** juga tersedia.

Untuk Instagram, Queen Al-Falah Core 1.9.0 menambahkan sinkronisasi resmi khusus Reel/video dari akun Profesional. Buka **Sekolah → Instagram Galeri**, masukkan Instagram User ID dan long-lived access token dari aplikasi Meta resmi sekolah, pilih **Draf** atau **Terbit otomatis**, lalu jalankan sinkronisasi manual atau harian. Token hanya disimpan pada option privat server dan tidak ikut GitHub/ZIP. Lihat [`queen-alfalah-core/INSTAGRAM-GALLERY-SETUP.md`](queen-alfalah-core/INSTAGRAM-GALLERY-SETUP.md).

## Sinkronisasi perkembangan project

Perubahan dikerjakan langsung pada folder `queen-alfalah` dan `queen-alfalah-core` di repository. Jalankan perintah berikut untuk memvalidasi paket, membuat commit, dan mendorong branch aktif ke `hanhkx/smkqueen`:

```powershell
.\sync-project.ps1 -Message "Jelaskan perubahan yang dibuat"
```

Skrip memverifikasi struktur project dan remote GitHub sebelum commit/push. Database WordPress, `wp-config.php`, unggahan pengguna, arsip ZIP, serta berkas rahasia tidak ikut dikirim.

## Lisensi

GNU General Public License v2 atau versi yang lebih baru.
