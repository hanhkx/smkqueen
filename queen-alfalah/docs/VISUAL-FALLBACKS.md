# Ilustrasi Fallback dan Foto Asli

Tema 1.6.0 menyediakan ilustrasi kategori untuk mencegah kartu atau halaman tampil kosong ketika administrator belum mengunggah Gambar Unggulan.

## Urutan prioritas

Tema selalu memakai urutan berikut:

1. Gambar Unggulan yang dipilih administrator.
2. Ilustrasi khusus entri yang disediakan plugin, misalnya Ekstrakurikuler.
3. Ilustrasi kategori yang dipilih otomatis dari jenis konten atau slug.
4. Placeholder SVG lama bila aset kategori tidak tersedia.

Ilustrasi kategori hanya bekerja pada lapisan tampilan. Tema tidak membuat attachment Media Library, tidak mengubah database, dan tidak menimpa Gambar Unggulan.

## Kategori visual

| Kategori | Digunakan untuk |
|---|---|
| Sekolah | Profil, sejarah, serta halaman umum |
| Orang | Guru/Tendik, alumni, sambutan, dan struktur |
| Galeri | Album atau video tanpa sampul |
| Prestasi | Prestasi tanpa foto sumber yang tepat |
| Layanan | Pusat Aplikasi, informasi, kesiswaan, PPDB/SPMB, pengumuman, dan agenda |
| Karier | BKK, PKL, mitra, lowongan, dan alumni |
| Sarpras umum | Halaman Sarana Prasarana dan fasilitas yang belum mempunyai kelompok khusus |
| Laboratorium digital | DKV, TJKT, komputer, serat optik, podcast, foto, drone, broadcasting, dan editing |
| Kesehatan | Ruang praktik Layanan Kesehatan, alat kesehatan, dan UKS |
| Kantor dan kelas | MPLB, peralatan kantor, administrasi, dan ruang kelas |
| Fasilitas kampus | Gedung, auditorium, perpustakaan/literasi, kantin, dan fasilitas umum |

Semua aset kategori berbentuk WebP 1200 × 800 piksel dan diberi label **Ilustrasi** pada tampilan publik.

Untuk pemelihara kode, aset pengganti dapat dioptimalkan secara konsisten dari folder `tools/` dengan `npm ci`, lalu `npm run optimize:fallback -- <sumber-gambar> <tujuan.webp>`. Perintah memakai versi `sharp` yang dikunci pada `package-lock.json`; gambar sumber tetap harus diperiksa izin dan ketepatan kategorinya sebelum digunakan.

## Mengganti ilustrasi dengan dokumentasi asli

1. Buka entri yang akan disunting di Dasbor WordPress.
2. Pilih panel **Gambar Unggulan**.
3. Unggah atau pilih foto dokumentasi sekolah yang benar.
4. Isi alt text faktual, caption, kredit, dan sumber/izin bila diperlukan.
5. Periksa pratinjau, lalu perbarui entri.

Setelah Gambar Unggulan tersimpan, ilustrasi otomatis tidak ditampilkan lagi. Menghapus Gambar Unggulan akan mengaktifkan kembali fallback kategori.

## Aturan integritas visual

- Ilustrasi bukan bukti bahwa ruang, orang, kegiatan, atau penghargaan benar-benar tampak seperti gambar tersebut.
- Jangan menghapus label **Ilustrasi** atau mengubah caption sehingga terlihat sebagai dokumentasi asli.
- Prestasi, berita kegiatan, profil individu, dan mitra harus memakai dokumentasi autentik ketika tersedia dan izin publikasinya sudah diperiksa.
- Jangan memakai foto kelompok lain untuk mengisi prestasi yang berbeda. Gunakan ilustrasi sementara agar tidak salah atribusi.
- Hindari data pribadi, wajah tanpa izin, lokasi sensitif, kartu identitas, lembar nilai, dan layar yang memperlihatkan akun/kata sandi.
