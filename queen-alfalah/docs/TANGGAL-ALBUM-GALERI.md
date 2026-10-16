# Tanggal album galeri — 6 September 2026

## Status penerapan

Queen Al-Falah Core 1.14.1 berhasil diperbarui dari 1.14.0 dan terverifikasi aktif di website online pada 6 September 2026. Pemeriksaan admin terbaru menunjukkan 58 galeri terbit, 1 draf, dan 1 Trash. Kelima tanggal album sudah tersedia sebelum pembaruan ini; tidak ada impor tanggal maupun publikasi ulang pada pekerjaan pengurutan reverse. Tampilan tanggal pada tema juga sudah tersedia online.

Arsip publik halaman 1, 2, dan 6 serta 12 kartu galeri beranda telah diperiksa. Urutan awal: 138, 140, 678, 676, 671 (lima album bertanggal menurun), kemudian 688, 689, 672, 673, 674 (tanpa tanggal; publikasi terlama dahulu). Halaman terakhir tetap memuat delapan galeri. Cache halaman LiteSpeed disegarkan setelah pembaruan.

## Perilaku

- Galeri, filter sumber galeri, kategori galeri, dan galeri pada beranda diurutkan berdasarkan Tanggal Album dari terbaru ke terlama.
- Setelah seluruh album bertanggal, album dengan tanggal kosong/tidak valid diurutkan menurut tanggal publikasi paling lama ke paling baru. Jika waktu publikasinya sama, ID kecil tampil dahulu. Ini membalik urutan kelompok tanpa tanggal saja; tanggal publikasi tidak diubah.
- Sesama album bertanggal dengan tanggal album sama tetap memakai tanggal publikasi dan ID terbaru lebih dahulu sebagai pemecah urutan yang stabil.
- Kartu, detail, dan galeri beranda menampilkan tanggal album. Jika belum diketahui, ditampilkan “Tanggal kegiatan belum dicatat”.
- Tanggal publikasi WordPress, judul, caption, URL, video, dan foto tidak berubah. Draf tidak diterbitkan oleh alat tanggal.
- Tanggal unggahan Instagram tidak digunakan sebagai pengganti tanggal kegiatan, sesuai pilihan pengguna. Importer Instagram menyimpannya sebagai metadata sumber terpisah jika suatu saat dipakai lagi; sinkronisasi tidak diaktifkan oleh pembaruan ini.
- Metadata tanggal yang sudah ada tetapi tidak termasuk dalam lima entri terverifikasi tidak dihapus/ditimpa otomatis. Periksa lewat editor bila ada tanggal lama yang belum terverifikasi.

## Tanggal yang terbaca pada video

Pemeriksaan memakai empat cuplikan setiap video, bukan pemeriksaan seluruh frame atau transkripsi audio. Lima berikut juga diperiksa ulang secara visual; tanggal lain belum dikonfirmasi.

| Indeks video | Album | Tanggal | Bukti |
|---|---|---|---|
| 58 | Ucapan Maulid Nabi | 25 Agustus 2026 | Teks ucapan “25 Agustus 2026”. Ini tanggal ucapan/peringatan, bukan bukti tanggal rekaman latar. |
| 56 | Sosialisasi TKA–ANBK dan bimbingan karier | 24 Agustus 2026 | Overlay “Senin, 24.8.2026” pada 20,8 / 35,6 / 50,4 detik. |
| 43 | MPLS hari kedua putri | 15 Juli 2026 | Pembuka video “15 JULI 2026”, 3,3 detik. |
| 42 | Hari pertama MPLS dan deklarasi | 13 Juli 2026 | Poster deklarasi “13 JULI 2026”, 47,7 detik. |
| 37 | Workshop kompetensi guru | 2 Juli 2026 | Banner “Kamis, 2 Juli 2026”, 19,7 detik. |

53 dari58 video belum memperoleh tanggal kegiatan yang terkonfirmasi. Dua di antaranya memuat tanggal jadwal, tetapi tidak dimasukkan sebagai tanggal album:

- Indeks19: “22 April 2026 - Kuota Terpenuhi”, jadwal pendaftaran.
- Indeks35: “23 dan24 Juni2026”, pengumuman jadwal layanan administrasi.

## Cara menerapkan pada instalasi lain / pemulihan

Website sekolah sudah diperbarui. Langkah berikut merupakan panduan untuk instalasi lain; jangan mengulang impor tanggal pada website aktif tanpa kebutuhan.

1. Cadangkan website/database, lalu perbarui plugin melalui **Plugin → Tambah Plugin → Unggah Plugin** dengan `queen-alfalah-core-1.14.1.zip`; pilih mengganti versi plugin yang sudah ada. Jika Core1.14.0 dan tema1.7.2 sudah terpasang, cukup perbarui plugin untuk mendapatkan urutan reverse pada kelompok tanpa tanggal.
2. Perbarui tema melalui **Tampilan → Tema → Tambah Tema → Unggah Tema** dengan `queen-alfalah-1.7.2.zip`; pilih mengganti versi tema yang sudah ada.
3. Buka **Sekolah → Tanggal Album Galeri** (`/wp-admin/admin.php?page=qaf-gallery-dates`). Lima tanggal beserta buktinya sudah terisi. Periksa lalu klik **Terapkan Tanggal Album Terverifikasi**.
4. Pastikan laporan menunjukkan lima tanggal tersimpan. Jika galeri tidak ditemukan/duplikat/tidak dapat disunting, entri tersebut dilewati dan alasannya ditampilkan.
5. Jika ada cache website/CDN, bersihkan melalui alat cache yang biasa digunakan. Periksa `/galeri`, pergantian halaman, filter sumber, satu detail album, dan galeri beranda.

Untuk mengubah satu tanggal kapanpun: **Galeri → Sunting album → kolom Tanggal Album → Perbarui**. Kosongkan jika belum diketahui. Tanggal lama sebelum pembaruan massal pertama disimpan di metadata `_qaf_album_date_before_review`; alat hanya menjalankan pembaruan setelah cadangan dan bukti tersimpan.

## Verifikasi lokal

- SQL pengurutan diuji menggunakan SQLite: tanggal terbaru, tanggal sama, tahun kabisat, tanggal mustahil, metadata ganda, album tanpa tanggal, dan paginasi.
- Batas WordPress diuji dengan fixture: hanya metadata tanggal berubah, draf tetap draf, konten/media tidak berubah, sumber harus unik, Trash/akses ditolak tidak diubah, cadangan tidak ditimpa saat diulang, gagal menyimpan cadangan/bukti tidak mengubah tanggal, serta nonce/POST/akses administrator wajib.
- Tampilan tanggal dan label tanggal belum diketahui diuji pada helper yang dipakai beranda/kartu/detail.
- Hasil pengurutan pada database hosting telah diperiksa lewat arsip publik halaman 1, 2, dan 6 serta galeri beranda setelah pembaruan online. Keenam pengujian terkait pengurutan, pembaruan tanggal, tampilan, publikasi massal, sinkronisasi, dan urutan mitra lulus pada perubahan 1.14.1. Pemeriksaan sintaks modul/entry point dan pemeriksaan diff juga lulus.
