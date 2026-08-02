# SMK Queen Al-Falah WordPress

Tema dan plugin pendamping WordPress untuk website SMK Queen Al-Falah. Project menyediakan landing page sekolah, profil, berita, pengumuman, agenda, program keahlian, PPDB, galeri, layanan sekolah, serta Pusat Aplikasi satu pintu.

## Komponen

- `queen-alfalah/` — tema WordPress Queen Al-Falah versi 1.4.0.
- `queen-alfalah-core/` — plugin pendamping versi 1.7.1 untuk tipe konten dan pengaturan sekolah.

## Fitur utama

- Landing page responsif dengan background gambar/GIF yang dapat diganti.
- Identitas, kontak, kepala sekolah, logo, warna, dan konten yang dapat dikelola dari dashboard.
- Berita, pengumuman, agenda, prestasi, guru/tendik, program keahlian, galeri, BKK, dan PPDB.
- Galeri terpadu yang dapat memuat foto/video sekolah dari Media Library atau satu konten publik Instagram, TikTok, Facebook, dan YouTube per entri, lengkap dengan penyaring sumber dan opsi privasi klik-untuk-muat.
- Pusat Aplikasi `/aplikasi/` untuk Ujian Online, E-Rapor, E-Perpustakaan, SPMB, dan Gamifikasi Edu.
- Pusat Media privat `/pusat-media/` dengan login WordPress dan satu tautan Google Drive yang dapat diganti untuk tiap divisi Waka/Guru/Tendik.
- Struktur Organisasi 2026/2027 lengkap dengan tupoksi dan foto yang tersinkron dari Guru & Tendik.
- Katalog Sarana Prasarana yang mudah dikelola dengan lokasi, fungsi, fitur, akses, pengelola, kapasitas, kondisi, dan tanggal pemeriksaan.
- Empat belas profil awal PKL & Mitra Industri beserta bidang keahlian, jurusan terkait, bentuk kerja sama, sumber, dan status verifikasi.
- Arsip Prestasi berisi 17 halaman capaian siswa dari unggahan Instagram resmi tahun 2023–2026; 16 foto sumber yang terverifikasi hanya mengisi slot gambar unggulan yang masih kosong. Satu foto LBB putri menunggu dokumentasi yang tepat agar tidak salah atribusi.
- Sebelas halaman ekstrakurikuler dengan manfaat, relevansi dunia kerja, dan ilustrasi bawaan sebagai fallback gambar.
- Struktur ZIP tema dan plugin yang kompatibel dengan pemasang WordPress.
- Dukungan penggunaan offline melalui WordPress dan Laragon.

Profil mitra merupakan data awal berbasis sumber publik. Status kerja sama formal, periode, kuota, dan penempatan wajib dicocokkan kembali dengan MoU atau arsip sekolah sebelum diumumkan sebagai kerja sama aktif.

## Persyaratan

- WordPress 6.2 atau lebih baru.
- PHP 7.4 atau lebih baru.

## Instalasi

1. Jalankan `powershell -ExecutionPolicy Bypass -File .\build-packages.ps1` dari PowerShell untuk membuat ZIP tema dan plugin yang kompatibel dengan WordPress.
2. Unggah `dist/queen-alfalah-1.4.0.zip` melalui **Tampilan → Tema → Tambah Tema**.
3. Unggah `dist/queen-alfalah-core-1.7.1.zip` melalui **Plugin → Tambah Plugin**.
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

## Galeri lokal dan sosial media

Buat atau sunting entri melalui **Galeri → Tambah Baru**. Gunakan **Gambar Unggulan** sebagai sampul, lalu pilih salah satu mode berikut:

- **Lokal / Media Library** — unggah foto melalui blok Gambar/Galeri dan pilih video lokal pada panel Detail Terstruktur atau melalui blok Video.
- **Instagram, TikTok, Facebook, atau YouTube** — tempel URL HTTPS kanonik satu postingan/video publik. Jangan menempel kode iframe.

Pilihan **Klik untuk memuat** menjadi bawaan agar koneksi ke platform pihak ketiga baru dilakukan setelah persetujuan pengunjung. Pilihan **Muat otomatis** dan **Tautan saja** juga tersedia. Integrasi ini tidak membutuhkan token API dan tidak menyinkronkan seluruh feed akun secara otomatis; setiap konten sosial dibuat sebagai satu entri Galeri agar judul, sampul, kredit, dan izin publikasinya tetap dapat dikendalikan sekolah.

## Sinkronisasi perkembangan project

Perubahan dikerjakan langsung pada folder `queen-alfalah` dan `queen-alfalah-core` di repository. Jalankan perintah berikut untuk memvalidasi paket, membuat commit, dan mendorong branch aktif ke `hanhkx/smkqueen`:

```powershell
.\sync-project.ps1 -Message "Jelaskan perubahan yang dibuat"
```

Skrip memverifikasi struktur project dan remote GitHub sebelum commit/push. Database WordPress, `wp-config.php`, unggahan pengguna, arsip ZIP, serta berkas rahasia tidak ikut dikirim.

## Lisensi

GNU General Public License v2 atau versi yang lebih baru.
