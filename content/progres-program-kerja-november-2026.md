# Progres Program Kerja Website — November 2026

Tanggal pemeriksaan: 14 November 2026  
Dasar: Program Kerja Pengelolaan Website SMK Queen Al-Falah 2026/2027 dan tindak lanjut jurnal Agustus–Oktober.

## Prioritas November

1. Menuntaskan halaman Kontak dan Bantuan: peta embed, identitas kontak yang konsisten, kanal per unit, FAQ, serta formulir bantuan minim data.
2. Memutakhirkan Berita, Prestasi, Karya Siswa, dan Galeri dari kanal resmi tanpa menghapus atau menimpa konten lama.
3. Menjaga kualitas editorial: judul informatif, ringkasan, subjudul, alt text, kredit foto, tanggal unggahan, URL sumber, dan penandaan partisipasi secara jujur.
4. Menggunakan media WebP persegi dari foto sumber agar kartu berita, prestasi, karya, dan galeri tetap jelas pada tampilan responsif.

## Status pengerjaan

- [x] Audit halaman Kontak publik dan konsistensi data layanan.
- [x] Direktori enam unit layanan dengan fallback ke kanal pusat sekolah.
- [x] FAQ kontak dan formulir bantuan yang tidak menyimpan isi pesan di basis data WordPress.
- [x] Perlindungan formulir: nonce, honeypot, batas waktu, pembatasan pengiriman tersamar, validasi panjang, dan peringatan data pribadi.
- [x] Audit feed Instagram resmi sampai unggahan 10 November 2026.
- [x] Audit kanal YouTube resmi; belum ada unggahan yang lebih baru daripada materi yang telah diarsipkan website.
- [x] Penyiapan 10 Berita, 5 Prestasi/Partisipasi, 1 Karya Siswa, dan 10 Galeri baru.
- [x] Penyiapan thumbnail WebP persegi dari foto sumber resmi.
- [x] Deduplikasi berdasarkan ID sumber, URL kanonik, dan slug, termasuk semua status serta Sampah.
- [ ] Penerapan paket 1.27.0 dan Tema 1.12.0 ke website produksi setelah konfirmasi administrator.
- [ ] Pemeriksaan visual publik, pengiriman email bantuan, dan pembersihan cache setelah penerapan.

## Pembaruan konten resmi

| Tanggal sumber | Kanal | Topik | Tujuan publikasi |
|---|---|---|---|
| 28 Oktober 2026 | Instagram | Pemilu Raya OSIS 2026/2027 | Berita dan Galeri |
| 28 Oktober 2026 | Instagram | Olimpiade FTKI 2026 | Berita, Prestasi/Partisipasi, dan Galeri |
| 28 Oktober 2026 | Instagram | Digitalisasi pembelajaran | Berita dan Galeri |
| 30 Oktober 2026 | Instagram | Kombel dan MGMPS | Berita dan Galeri |
| 1 dan 7 November 2026 | Instagram | Pemeriksaan kesehatan bersama Puskesmas Mojo | Satu Berita dan satu Galeri; dua unggahan digabung agar tidak duplikatif |
| 6 November 2026 | Instagram | TKA kelas XII, 2–5 November 2026 | Berita dan Galeri |
| 7 November 2026 | Instagram | Juara Harapan 2 dan Best Innovation Award GIC Batch II | Berita, Prestasi, dan Galeri |
| 7 November 2026 | Instagram | Canvas Totebag Painting School Contest XVIII | Berita, Prestasi/Partisipasi, Karya Siswa, dan Galeri |
| 9 November 2026 | Instagram | 16 besar Badminton Cup HUT Golkar ke-62 | Berita, Prestasi, dan Galeri |
| 10 November 2026 | Instagram | Finalis Kompetensi Akademik 2026 | Berita, Prestasi, dan Galeri |

Kegiatan FTKI dan totebag painting tetap ditulis sebagai **partisipasi** karena sumber resmi tidak menyatakan perolehan juara. Ruang lingkup kompetisi juga tidak ditebak ketika tidak disebutkan oleh sumber.

## Implementasi Kontak dan Bantuan

- Enam unit: Informasi Umum & Tata Usaha, SPMB, Kurikulum & Pembelajaran, Kesiswaan & BK, Sarana Prasarana, serta Hubin/PKL/BKK.
- Email dan WhatsApp setiap unit dapat diubah melalui **Sekolah → Pengaturan**. Field yang kosong otomatis memakai kanal resmi pusat.
- Formulir meminta hanya unit tujuan, email balasan, subjek, pesan, dan nama opsional.
- Pesan diteruskan memakai email WordPress dan tidak disimpan sebagai posting, komentar, atau data khusus.
- Pengunjung diperingatkan agar tidak mengirim NIK, NISN, kata sandi, data kesehatan, scan ijazah, atau dokumen pribadi.
- Pengingat editor lama pada halaman Kontak hanya dibersihkan bila teks placeholder yang tepat masih ditemukan; isi yang telah disunting administrator tidak ditimpa secara umum.

## Tindak lanjut bulan berjalan

1. Setelah paket diterapkan, uji satu pengiriman formulir menggunakan alamat email non-pribadi dan pastikan email masuk ke tujuan yang benar.
2. Isi kanal khusus unit hanya setelah nomor/email resmi disetujui; hingga saat itu gunakan fallback pusat.
3. Periksa 10 Berita, 5 Prestasi/Partisipasi, 1 Karya Siswa, dan 10 Galeri pada tampilan ponsel serta desktop.
4. Bersihkan cache WordPress/CDN, lalu verifikasi tanggal, gambar, kredit, tombol sumber, dan urutan konten terbaru.
5. Lanjutkan audit Sarana Prasarana dan foto Guru/Tendik yang belum lengkap dari prioritas Oktober, menggunakan hanya arsip resmi dengan izin publikasi.

## Catatan kanal YouTube

Video terbaru yang terdeteksi pada kanal resmi masih berasal dari September 2026 dan topiknya telah terwakili dalam arsip website. Tidak dibuat entri baru agar website tidak memuat duplikasi. Audit kanal tetap dicatat sebagai selesai dan dapat diulang pada pembaruan berikutnya.
