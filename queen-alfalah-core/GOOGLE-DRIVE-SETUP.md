# Penyiapan Google Drive untuk Pusat Media

Pusat Media memakai tiga tautan folder Google Drive yang dapat dikelola dari dashboard WordPress:

- Waka;
- Guru;
- Tenaga Kependidikan (Tendik).

Pengguna wajib login ke WordPress. Akun berperan Waka, Guru, atau Tendik hanya melihat tautan divisinya, sedangkan administrator dapat melihat semua tautan untuk pemeriksaan.

WordPress tidak mengunggah, mengunduh, memproksi, atau menyimpan isi file Drive. Plugin tidak memerlukan Google Drive API, OAuth, service account, client secret, refresh token, maupun JSON key.

## 1. Siapkan folder dan izin Drive

1. Buat satu folder untuk setiap divisi pada My Drive atau Shared Drive sekolah.
2. Buka dialog **Bagikan** pada setiap folder.
3. Pada **Akses umum**, pilih **Dibatasi**.
4. Tambahkan akun Google resmi personel yang berwenang.
5. Pilih level izin sesuai kebutuhan:
   - **Pelihat** untuk hanya membaca/mengunduh;
   - **Pemberi komentar** bila diperlukan;
   - **Editor** untuk mengunggah, menyunting, memindahkan, atau menghapus file.
6. Salin URL setiap folder.

Jangan memakai opsi **Siapa saja yang memiliki link** untuk dokumen internal. Login WordPress hanya menyembunyikan tautan dari divisi lain; izin berbagi Google Drive tetap menjadi batas keamanan sebenarnya.

## 2. Simpan tautan di WordPress

1. Masuk sebagai administrator WordPress.
2. Buka **Sekolah → Pengaturan**.
3. Cari kelompok **Pusat Media**.
4. Isi:
   - **Folder Google Drive Waka**;
   - **Folder Google Drive Guru**;
   - **Folder Google Drive Tendik**.
5. Simpan pengaturan.

Hanya URL HTTPS dari `drive.google.com` atau `docs.google.com` yang diterima. Kosongkan field yang belum siap; pengguna divisi tersebut akan melihat pemberitahuan agar menghubungi administrator.

## 3. Buat akun Pusat Media

1. Buka **Pengguna → Tambah Baru**.
2. Gunakan username unik dan password kuat untuk setiap personel.
3. Pilih salah satu peran:
   - Waka Sekolah;
   - Guru;
   - Tenaga Kependidikan.
4. Isi **Unit / Jabatan** bila diperlukan.
5. Kirim kredensial melalui saluran privat; jangan menyimpan password di Git atau dokumentasi publik.

Tidak perlu mengisi ID folder per pengguna. Satu tautan folder dipakai bersama oleh setiap divisi, sementara akses tiap orang dibatasi melalui daftar anggota folder di Google Drive.

## 4. Uji akses

1. Buka `/pusat-media/` dalam jendela privat.
2. Login memakai satu akun Waka, Guru, atau Tendik.
3. Pastikan hanya satu kartu folder sesuai divisi yang tampil.
4. Buka tautan dan pastikan Google meminta akun yang benar.
5. Uji level izin Drive, misalnya apakah akun Pelihat tidak dapat mengunggah dan akun Editor dapat mengelola file.
6. Ulangi pengujian untuk ketiga peran.

## Mengganti folder

Jika folder penuh, dipindahkan, atau bermasalah:

1. siapkan folder pengganti dan atur anggota/izinnya terlebih dahulu;
2. salin URL folder baru;
3. ganti URL divisi terkait di **Sekolah → Pengaturan → Pusat Media**;
4. uji dengan akun non-administrator.

Perubahan URL tidak menghapus akun, post, atau pengaturan WordPress lainnya. Pemindahan file dilakukan langsung di Google Drive.

## Migrasi dari konfigurasi lama

Versi saat ini tidak memakai konstanta `QAF_GOOGLE_DRIVE_*` di `wp-config.php`. Bila instalasi lama masih memiliki konfigurasi OAuth atau service account khusus Pusat Media, buat cadangan lalu hapus konstanta dan berkas kunci yang tidak lagi digunakan. Jangan pernah mengunggah client secret, refresh token, private key, atau JSON service account ke repository.

## Pemecahan masalah

**Tautan divisi tidak tampil**

Periksa peran WordPress akun tersebut dan pastikan URL divisinya tersimpan pada Pengaturan Sekolah.

**URL ditolak saat disimpan**

Gunakan URL lengkap berawalan `https://` dari `drive.google.com` atau `docs.google.com`.

**Muncul “Anda perlu akses” dari Google**

Tambahkan akun Google pengguna sebagai anggota folder atau minta pengguna beralih ke akun Google sekolah yang benar.

**Pengguna dapat mengubah atau menghapus file padahal seharusnya tidak**

Turunkan izin akun pada dialog berbagi Google Drive. Peran WordPress tidak mengatur operasi file di Google Drive.

**Tautan diketahui pengguna dari divisi lain**

Pastikan folder tetap berstatus **Dibatasi**. Kerahasiaan URL bukan mekanisme otorisasi.
