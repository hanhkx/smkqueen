# SMK Queen Al-Falah WordPress

Tema dan plugin pendamping WordPress untuk website SMK Queen Al-Falah. Project menyediakan landing page sekolah, profil, berita, pengumuman, agenda, program keahlian, PPDB, galeri, layanan sekolah, serta Pusat Aplikasi satu pintu.

## Komponen

- `queen-alfalah/` — tema WordPress Queen Al-Falah versi 1.1.1.
- `queen-alfalah-core/` — plugin pendamping versi 1.1.0 untuk tipe konten dan pengaturan sekolah.

## Fitur utama

- Landing page responsif dengan background gambar/GIF yang dapat diganti.
- Identitas, kontak, kepala sekolah, logo, warna, dan konten yang dapat dikelola dari dashboard.
- Berita, pengumuman, agenda, prestasi, guru/tendik, program keahlian, galeri, BKK, dan PPDB.
- Pusat Aplikasi `/aplikasi/` untuk Ujian Online, E-Rapor, E-Perpustakaan, SPMB, dan Gamifikasi Edu.
- Pusat Media privat `/pusat-media/` dengan akun WordPress per Waka/Guru/Tendik, folder Drive pribadi otomatis, serta unggah-unduh terotorisasi.
- Struktur Organisasi 2026/2027 lengkap dengan tupoksi dan foto yang tersinkron dari Guru & Tendik.
- Struktur ZIP tema dan plugin yang kompatibel dengan pemasang WordPress.
- Dukungan penggunaan offline melalui WordPress dan Laragon.

## Persyaratan

- WordPress 6.2 atau lebih baru.
- PHP 7.4 atau lebih baru.

## Instalasi

1. Buat ZIP folder `queen-alfalah`, kemudian unggah melalui **Tampilan → Tema → Tambah Tema**.
2. Buat ZIP folder `queen-alfalah-core`, kemudian unggah melalui **Plugin → Tambah Plugin**.
3. Aktifkan plugin dan tema.
4. Buka **Sekolah → Pengaturan** untuk melengkapi identitas sekolah.
5. Buka **Pengaturan → Permalink**, lalu simpan ulang permalink.

## Pengembangan lokal

Salin kedua folder ke instalasi WordPress lokal:

```text
wp-content/themes/queen-alfalah
wp-content/plugins/queen-alfalah-core
```

Aktifkan melalui dashboard WordPress. Jangan menyimpan `wp-config.php`, database, kata sandi, atau berkas unggahan pengguna di repository.

## Pusat Media privat

Plugin membuat peran **Waka Sekolah**, **Guru**, dan **Tenaga Kependidikan**, serta halaman `/pusat-media/`. Password memakai autentikasi WordPress dan selalu disimpan sebagai hash oleh WordPress. Akun portal diarahkan ke Pusat Media dan tidak diberi akses ke dashboard administrasi. Folder pribadi `Nama (@username)` dibuat otomatis di Google Drive ketika akun pertama kali membuka portal.

Folder induk Google Drive sekolah:

[SMK Queen Al-Falah - Pusat Media](https://drive.google.com/drive/folders/1N0w6Y9e2p5IYn_ipLLcR7hG2ApDT2KK9)

Untuk My Drive, koneksi unggah-unduh memakai OAuth akun pemilik folder. Simpan nilai berikut hanya di `wp-config.php`:

```php
define( 'QAF_GOOGLE_DRIVE_ROOT_FOLDER_ID', '1N0w6Y9e2p5IYn_ipLLcR7hG2ApDT2KK9' );
define( 'QAF_GOOGLE_DRIVE_OAUTH_CLIENT_ID', 'CLIENT_ID.apps.googleusercontent.com' );
define( 'QAF_GOOGLE_DRIVE_OAUTH_CLIENT_SECRET', 'CLIENT_SECRET' );
define( 'QAF_GOOGLE_DRIVE_OAUTH_REFRESH_TOKEN', 'REFRESH_TOKEN' );
```

Service account tetap didukung untuk Shared Drive melalui `QAF_GOOGLE_DRIVE_CREDENTIALS_PATH`. Jangan commit OAuth secret, refresh token, JSON, private key, username, atau password ke Git. Pengguna hanya dapat menelusuri folder pribadinya beserta subfolder dan file turunannya; unggahan dan unduhan diproksi WordPress setelah pemeriksaan login, capability, nonce, jenis/ukuran file, dan rantai folder.

Lihat panduan lengkap di [`queen-alfalah-core/GOOGLE-DRIVE-SETUP.md`](queen-alfalah-core/GOOGLE-DRIVE-SETUP.md).

## Sinkronisasi perkembangan project

Perubahan utama tetap dikerjakan pada folder `work/queen-alfalah` dan `work/queen-alfalah-core`. Jalankan perintah berikut dari repository untuk menyalin perubahan, membuat commit, dan push ke GitHub:

```powershell
.\sync-project.ps1 -Message "Jelaskan perubahan yang dibuat"
```

Skrip memverifikasi sumber dan target sebelum menyelaraskan folder. Database WordPress, `wp-config.php`, unggahan pengguna, arsip ZIP, serta berkas rahasia tidak ikut dikirim.

## Lisensi

GNU General Public License v2 atau versi yang lebih baru.
