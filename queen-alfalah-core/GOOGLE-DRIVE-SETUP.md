# Penyiapan Google Drive untuk Pusat Media

Pusat Media mendukung satu tautan folder Google Drive khusus untuk setiap akun WordPress:

- Waka;
- Guru;
- Tenaga Kependidikan (Tendik).

Pengguna wajib login ke WordPress dan hanya melihat folder yang ditetapkan pada profilnya. Tiga tautan lama untuk Waka, Guru, dan Tendik tetap berfungsi sebagai cadangan apabila URL folder khusus pengguna belum diisi. Waka juga memiliki dashboard terbatas untuk Media Library dan Pengumuman. Administrator dapat melihat status folder khusus pada daftar Pengguna tanpa mengekspos URL-nya di tabel.

WordPress tidak mengunggah, mengunduh, memproksi, atau menyimpan isi file Drive. Plugin tidak memerlukan Google Drive API, OAuth, service account, client secret, refresh token, maupun JSON key. Media Library yang tersedia bagi Waka adalah tempat terpisah untuk lampiran Pengumuman publik; jangan mengunggah dokumen internal atau rahasia ke Media Library.

## 1. Siapkan folder dan izin Drive

1. Rekomendasi utama: buat folder induk **Pusat Media SMK Queen** pada Shared Drive Google Workspace sekolah, lalu satu subfolder untuk setiap pengguna. File dalam Shared Drive menjadi milik organisasi, sehingga tidak memerlukan proses salin lalu hapus dari akun pribadi.
2. Jika Shared Drive tidak tersedia, buat folder tujuan pada My Drive sekolah. Perhatikan bahwa kepemilikan dan pemakaian kuota file dapat mengikuti akun pengunggah atau kebijakan Google Workspace yang berlaku.
3. Buka dialog **Bagikan** pada setiap folder.
4. Pada **Akses umum**, pilih **Dibatasi**.
5. Tambahkan akun Google resmi pemilik folder tersebut.
6. Pilih level izin sesuai kebutuhan:
   - **Pelihat** untuk hanya membaca/mengunduh;
   - **Pemberi komentar** bila diperlukan;
   - **Editor** untuk mengunggah, menyunting, memindahkan, atau menghapus file.
7. Salin URL setiap folder.

Jangan memakai opsi **Siapa saja yang memiliki link** untuk dokumen internal. Login WordPress hanya menyembunyikan tautan dari divisi lain; izin berbagi Google Drive tetap menjadi batas keamanan sebenarnya.

## 2. Simpan folder khusus per akun di WordPress

1. Masuk sebagai administrator WordPress.
2. Buka **Pengguna**, lalu sunting akun Waka, Guru, atau Tendik.
3. Pada bagian **Akses Pusat Media**, isi **Folder Google Drive akun**.
4. Simpan pengguna dan ulangi untuk setiap akun.
5. Gunakan kolom **Folder Pusat Media** pada daftar Pengguna untuk melihat akun yang masih memakai folder divisi.

Sebagai cadangan, buka **Sekolah → Pengaturan → Pusat Media** lalu isi folder umum Waka, Guru, dan Tendik. Fallback ini menjaga akun lama tetap dapat bekerja selama penyiapan folder individual dilakukan bertahap.

Hanya URL HTTPS dari `drive.google.com` atau `docs.google.com` yang diterima. Kosongkan field yang belum siap; pengguna divisi tersebut akan melihat pemberitahuan agar menghubungi administrator.

## 3. Buat akun Pusat Media

### Waka

1. Masuk sebagai administrator lalu buka **Sekolah → Akun Waka**.
2. Cocokkan empat nama, jabatan, dan username tetap dengan struktur organisasi terbaru.
3. Tekan **Buat Akun yang Belum Ada**. Proses tidak berjalan otomatis ketika plugin diaktifkan.
4. Simpan setiap password sementara 32 karakter yang ditampilkan satu kali dan kirim hanya kepada pemilik akun melalui saluran privat.
5. Saat pertama masuk, setiap Waka wajib mengganti password sebelum dapat membuka **Media** dan **Pengumuman**.

Tool tidak meminta atau mengarang email, tidak mengganti password/peran akun yang sudah ada, dan melewati username atau nama personel lama yang terdeteksi. Waka dapat mengunggah dokumen ke Media Library serta membuat, menyunting, dan menerbitkan Pengumuman, tetapi tidak memperoleh akses administrator ke Berita, Halaman, plugin, tema, pengguna, atau pengaturan.

### Guru dan Tendik

1. Buka **Pengguna → Tambah Baru**.
2. Gunakan username unik dan password kuat untuk setiap personel.
3. Pilih salah satu peran:
   - Guru;
   - Tenaga Kependidikan.
4. Isi **Unit / Jabatan** dan **Folder Google Drive akun**.
5. Kirim kredensial melalui saluran privat; jangan menyimpan password di Git atau dokumentasi publik.

Tidak ada ID, token, atau kunci API yang disimpan. WordPress hanya menyimpan URL HTTPS folder yang dapat diganti kapan saja.

## 4. Uji akses

1. Buka `/pusat-media/` dalam jendela privat.
2. Login memakai satu akun Guru atau Tendik dan pastikan hanya satu kartu folder khusus akun tersebut yang tampil.
3. Login memakai akun Waka. Selesaikan penggantian password pertama, periksa akses **Media** dan **Pengumuman**, lalu buka `/pusat-media/` untuk memastikan hanya folder akun tersebut yang tampil.
4. Buka tautan dan pastikan Google meminta akun yang benar.
5. Uji level izin Drive, misalnya apakah akun Pelihat tidak dapat mengunggah dan akun Editor dapat mengelola file.
6. Ulangi pengujian untuk ketiga peran.

## Mengganti folder

Jika folder penuh, dipindahkan, atau bermasalah:

1. siapkan folder pengganti dan atur anggota/izinnya terlebih dahulu;
2. salin URL folder baru;
3. ganti URL pada profil **Pengguna** terkait; jika yang diganti adalah fallback, ubah URL divisi di **Sekolah → Pengaturan → Pusat Media**;
4. uji dengan akun non-administrator.

Perubahan URL tidak menghapus akun, post, atau pengaturan WordPress lainnya. Pemindahan file dilakukan langsung di Google Drive.

## Otomasi unggah dari akun Google pribadi

Jangan memakai alur yang langsung menyalin lalu menghapus file asli tanpa pemeriksaan. Penghapusan membutuhkan izin Google yang lebih luas dan dapat menyebabkan kehilangan data apabila salinan gagal, kuota habis, koneksi terputus, atau folder tujuan salah.

Urutan yang aman adalah:

1. pengguna memilih folder khususnya dari Pusat Media;
2. file diunggah langsung ke folder sekolah atau Shared Drive, bukan ke penyimpanan WordPress;
3. sistem memverifikasi nama, ukuran, jenis, serta keberadaan file tujuan;
4. hasil salin ditandai berhasil;
5. file sumber tetap dipertahankan, atau pengguna memilih sendiri **Pindahkan ke Sampah** setelah verifikasi.

Jika sekolah memakai Google Workspace, gunakan Shared Drive agar file tujuan dimiliki organisasi dan tidak perlu salin-hapus. Jika sekolah hanya memakai akun Gmail/My Drive, otomasi unggah langsung memerlukan proyek Google terpisah—misalnya Apps Script yang dijalankan sebagai akun sekolah atau integrasi Drive API—beserta persetujuan OAuth, kebijakan privasi, pembatasan tipe/ukuran file, log audit, dan penanganan kegagalan. Google Drive for desktop hanya menyinkronkan folder akun yang terhubung; aplikasi tersebut bukan mesin transfer kepemilikan lintas akun.

Plugin versi ini sengaja tidak meminta akses Google, tidak membaca isi Drive, dan tidak menghapus file pengguna. Perluasan ke integrasi API harus dikerjakan sebagai modul terpisah setelah jenis akun tujuan, daftar akun Google pengguna, kuota, retensi, dan kewenangan penghapusan disahkan sekolah.

## Migrasi dari konfigurasi lama

Versi saat ini tidak memakai konstanta `QAF_GOOGLE_DRIVE_*` di `wp-config.php`. Bila instalasi lama masih memiliki konfigurasi OAuth atau service account khusus Pusat Media, buat cadangan lalu hapus konstanta dan berkas kunci yang tidak lagi digunakan. Jangan pernah mengunggah client secret, refresh token, private key, atau JSON service account ke repository.

## Pemecahan masalah

**Tautan divisi tidak tampil**

Periksa peran WordPress akun tersebut dan pastikan URL khususnya tersimpan pada profil Pengguna. Jika kosong, pastikan URL divisinya tersimpan pada Pengaturan Sekolah.

**URL ditolak saat disimpan**

Gunakan URL lengkap berawalan `https://` dari `drive.google.com` atau `docs.google.com`.

**Muncul “Anda perlu akses” dari Google**

Tambahkan akun Google pengguna sebagai anggota folder atau minta pengguna beralih ke akun Google sekolah yang benar.

**Pengguna dapat mengubah atau menghapus file padahal seharusnya tidak**

Turunkan izin akun pada dialog berbagi Google Drive. Peran WordPress tidak mengatur operasi file di Google Drive.

**Tautan diketahui pengguna dari divisi lain**

Pastikan folder tetap berstatus **Dibatasi**. Kerahasiaan URL bukan mekanisme otorisasi.
