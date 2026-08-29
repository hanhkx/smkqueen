# Sinkronisasi Galeri Instagram

Queen Al-Falah Core 1.9.0 dapat mengambil Reel dan video akun Instagram resmi ke tipe konten **Galeri**. Integrasi memakai Instagram API resmi, bukan scraping, cookie, atau kata sandi.

## Yang diperlukan

- akun Instagram sekolah bertipe **Professional**: Business atau Creator;
- aplikasi Meta bertipe **Business** yang dikelola sekolah;
- produk **Instagram API with Instagram Login**;
- izin baca `instagram_business_basic`;
- Instagram User ID numerik;
- long-lived access token milik akun resmi.

Dokumentasi resmi Meta:

- [Instagram API with Instagram Login — Getting Started](https://developers.facebook.com/documentation/instagram-platform/instagram-api-with-instagram-login/get-started)
- [Business Login dan access token](https://developers.facebook.com/documentation/instagram-platform/instagram-api-with-instagram-login/business-login)
- [Media milik pengguna Instagram](https://developers.facebook.com/documentation/instagram-platform/instagram-graph-api/reference/ig-user/media)

Tampilan dan nama menu pada dashboard Meta dapat berubah. Gunakan petunjuk terbaru pada tautan resmi di atas dan simpan kepemilikan aplikasi pada akun institusi, bukan akun pribadi yang akan segera tidak aktif.

## Menghubungkan ke WordPress

1. Cadangkan database dan folder `wp-content/uploads`.
2. Perbarui serta aktifkan **Queen Al-Falah Core 1.9.0**.
3. Di dashboard WordPress, buka **Sekolah → Instagram Galeri**.
4. Isi **Instagram User ID** numerik dan **long-lived access token**.
5. Pilih status konten baru:
   - **Draf** disarankan untuk pemeriksaan judul, caption, izin, dan poster sebelum terbit;
   - **Terbit otomatis** hanya jika alur publikasi Instagram sekolah sudah melalui pemeriksaan yang sama dengan website.
6. Aktifkan penyalinan thumbnail bila ingin kartu arsip memakai poster lokal.
7. Aktifkan jadwal harian, simpan, lalu tekan **Sinkronkan Sekarang** untuk uji pertama.
8. Buka **Galeri** dan periksa entri yang baru dibuat sebelum diterbitkan.

Token tidak pernah dicetak kembali pada halaman admin, tidak dikirim ke browser pengunjung, tidak dimasukkan ke JavaScript, dan tidak boleh disimpan di GitHub. Mengosongkan kolom token mempertahankan koneksi lama; gunakan pilihan **Putuskan koneksi** bila token memang harus dihapus.

## Cara kerja

- plugin memeriksa sejumlah postingan terbaru yang dapat diatur antara 1–100;
- hanya media `VIDEO`, termasuk Reel, yang dibuat; foto biasa dilewati;
- URL item baru disimpan dalam format kanonik `https://www.instagram.com/reel/SHORTCODE/`, `/p/SHORTCODE/`, atau `/tv/SHORTCODE/`; variasi URL manual lama tetap dikenali tanpa ditimpa;
- ID media mencegah duplikasi;
- item baru memakai mode embed **Klik untuk memuat**;
- judul, isi, status, perilaku embed, dan Gambar Utama yang sudah disunting administrator tidak ditimpa;
- item di Sampah tidak dipulihkan;
- konten WordPress tidak dihapus ketika postingan sumber hilang atau token terputus;
- thumbnail yang tersedia disalin secara terbatas ke Media Library, bukan di-hotlink dari CDN sementara;
- long-lived token yang masih valid dicoba diperbarui secara berkala setelah melewati masa minimum pembaruan Meta.

WordPress Cron dipicu oleh kunjungan. Pada situs dengan trafik sangat rendah, jadwal dapat terlambat; tombol **Sinkronkan Sekarang** selalu tersedia sebagai jalur manual.

## Privasi dan izin publikasi

Halaman detail menggunakan mode **Klik untuk memuat** secara bawaan. Koneksi dari browser pengunjung ke Instagram baru terjadi setelah tombol ditampilkan dan dipilih. Tautan sumber selalu tersedia bila embed privat, dihapus, dibatasi, atau dimatikan pemilik akun.

Poster yang disalin menjadi berkas lokal dan caption yang dimasukkan ke WordPress harus tetap mengikuti izin publikasi sekolah, khususnya bila memuat siswa. Jangan menyinkronkan akun pribadi, data privat, Stories, atau konten yang tidak memiliki dasar publikasi pada website.

## Pemecahan masalah

### Token kedaluwarsa atau dicabut

Buat long-lived token baru melalui aplikasi Meta resmi, buka **Sekolah → Instagram Galeri**, tempel token baru, simpan, lalu sinkronkan kembali. Entri Galeri lama tetap tersedia.

### Izin `instagram_business_basic` belum tersedia

Periksa produk Instagram API, role/tester aplikasi, akun profesional yang terhubung, serta status izin pada dashboard Meta. Gunakan User ID dari akun yang memberikan token tersebut.

### Sinkronisasi berhasil tetapi tidak ada entri baru

Periksa apakah postingan terbaru benar-benar bertipe video/Reel, naikkan jumlah postingan yang diperiksa, dan pastikan item tersebut belum ada di Galeri atau Sampah.

### Poster tidak muncul

Pastikan opsi penyalinan thumbnail aktif, WordPress dapat menulis ke `wp-content/uploads`, formatnya JPEG/PNG/WebP, ukurannya di bawah 8 MB, dan hosting mengizinkan koneksi HTTPS keluar ke CDN Meta/Instagram. Entri tetap berfungsi melalui placeholder dan tautan sumber bila poster gagal disalin.

### Embed tidak tampil

Postingan harus publik dan pemilik akun harus mengizinkan embed. Konten privat, dihapus, dibatasi usia/wilayah, atau dengan embed yang dinonaktifkan akan memakai tautan sumber sebagai fallback.
