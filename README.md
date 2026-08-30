# SMK Queen Al-Falah WordPress

Tema dan plugin pendamping WordPress untuk website SMK Queen Al-Falah. Project menyediakan landing page sekolah, profil, berita, pengumuman, agenda, program keahlian, PPDB, galeri, layanan sekolah, serta Pusat Aplikasi satu pintu.

## Komponen

- `queen-alfalah/` — tema WordPress Queen Al-Falah versi 1.6.1.
- `queen-alfalah-core/` — plugin pendamping versi 1.9.1 untuk tipe konten, sinkronisasi Galeri Instagram, media berita, katalog gambar Mitra DUDI, analitik opsional, dan pengaturan sekolah.

## Fitur utama

- Landing page responsif dengan background gambar/GIF yang dapat diganti.
- Embed Google Maps berbasis koordinat tervalidasi pada beranda dan halaman Kontak, dengan tautan eksternal sebagai fallback.
- Identitas, kontak, kepala sekolah, logo, warna, dan konten yang dapat dikelola dari dashboard.
- Berita, pengumuman, agenda, prestasi, guru/tendik, program keahlian, galeri, BKK, dan PPDB.
- Galeri terpadu yang dapat memuat foto/video sekolah dari Media Library atau konten publik Instagram, TikTok, Facebook, dan YouTube, lengkap dengan penyaring sumber dan opsi privasi klik-untuk-muat.
- Sinkronisasi resmi Reel/video akun Instagram Profesional ke Galeri, dengan jadwal harian opsional, pencegahan duplikasi, status draf/terbit, poster lokal, dan pembaruan long-lived token.
- Pusat Aplikasi `/aplikasi/` untuk Ujian Online, E-Rapor, E-Perpustakaan, SPMB, dan Gamifikasi Edu.
- Pusat Media privat `/pusat-media/` dengan login WordPress dan satu tautan Google Drive yang dapat diganti untuk tiap divisi Waka/Guru/Tendik.
- Struktur Organisasi 2026/2027 lengkap dengan tupoksi dan foto yang tersinkron dari Guru & Tendik.
- Katalog Sarana Prasarana yang mudah dikelola dengan lokasi, fungsi, fitur, akses, pengelola, kapasitas, kondisi, dan tanggal pemeriksaan.
- Empat belas profil awal PKL & Mitra Industri beserta bidang keahlian, jurusan terkait, bentuk kerja sama, sumber, dan status verifikasi.
- Lima belas kartu logo/foto Mitra DUDI lokal yang tidak di-hotlink dan tidak menimpa gambar unggulan pilihan administrator; lima identitas yang belum terverifikasi diberi penanda sementara.
- Arsip Prestasi berisi 17 halaman capaian siswa dari unggahan Instagram resmi tahun 2023–2026; 16 foto sumber yang terverifikasi hanya mengisi slot gambar unggulan yang masih kosong. Satu foto LBB putri menunggu dokumentasi yang tepat agar tidak salah atribusi.
- Delapan berita terbaru Agustus 2026 dilengkapi sampul lokal dari unggahan Instagram resmi, caption, alt text, kredit, dan tautan sumber tanpa hotlink CDN sementara.
- Google Analytics 4 dan verifikasi Search Console dapat diaktifkan dari Pengaturan Sekolah setelah ID resmi tersedia; field kosong tidak memuat integrasi pihak ketiga.
- Sebelas halaman ekstrakurikuler dengan manfaat, relevansi dunia kerja, dan ilustrasi bawaan sebagai fallback gambar.
- Sebelas ilustrasi WebP kategori yang otomatis mengisi tampilan kosong secara non-destruktif dan selalu diberi label **Ilustrasi**.
- Thumbnail kartu 1:1 pada berita dan seluruh arsip konten; foto Guru/Tendik serta logo Program Keahlian dan Mitra ditampilkan utuh tanpa crop paksa.
- Struktur ZIP tema dan plugin yang kompatibel dengan pemasang WordPress.
- Dukungan penggunaan offline melalui WordPress dan Laragon.

Profil mitra merupakan data awal berbasis sumber publik. Status kerja sama formal, periode, kuota, dan penempatan wajib dicocokkan kembali dengan MoU atau arsip sekolah sebelum diumumkan sebagai kerja sama aktif.

## Persyaratan

- WordPress 6.2 atau lebih baru.
- PHP 7.4 atau lebih baru.

## Instalasi

1. Jalankan `powershell -ExecutionPolicy Bypass -File .\build-packages.ps1` dari PowerShell untuk membuat ZIP tema dan plugin yang kompatibel dengan WordPress.
2. Unggah `dist/queen-alfalah-1.6.1.zip` melalui **Tampilan → Tema → Tambah Tema**.
3. Unggah `dist/queen-alfalah-core-1.9.1.zip` melalui **Plugin → Tambah Plugin**.
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

Plugin membuat peran **Waka Sekolah**, **Guru**, dan **Tenaga Kependidikan**, serta halaman `/pusat-media/`. Password memakai autentikasi WordPress dan selalu disimpan sebagai hash oleh WordPress. Akun portal diarahkan ke Pusat Media dan tidak diberi akses ke dashboard administrasi.

Isi tiga alamat folder di **Sekolah → Pengaturan → Pusat Media**: Folder Google Drive Waka, Guru, dan Tendik. Pengguna portal hanya melihat tautan yang sesuai dengan perannya; administrator dapat melihat semua tautan untuk pemeriksaan. Tautan dapat diganti kapan saja saat folder penuh, dipindahkan, atau mengalami kendala.

WordPress tidak mengunggah, mengunduh, atau memproksi file. Semua pengelolaan dokumen berlangsung langsung di Google Drive, sehingga aturan berbagi Drive merupakan batas keamanan sebenarnya. Gunakan mode berbagi **Dibatasi** dan beri izin hanya kepada akun Google yang berwenang. Jangan mengandalkan kerahasiaan URL folder sebagai pengganti pengaturan izin Drive.

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
