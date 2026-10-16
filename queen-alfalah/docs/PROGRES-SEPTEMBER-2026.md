# Progres September 2026

Pemeriksaan langsung: 5 September 2026.

## Selesai

- Tema 1.7.0 dan Core 1.12.0 berhasil terpasang online.
- Backup database, plugin, tema, dan must-use plugin berhasil pukul 06:38 waktu server. Tidak mencakup uploads; penyimpanan masih lokal hosting.
- Dua berita terbit dengan foto resmi, sumber, ringkasan dan tag: https://smkqueenalfalah.sch.id/archives/667 dan https://smkqueenalfalah.sch.id/archives/668.
- Tombol empat kanal sosial terverifikasi pada artikel baru; implementasi berlaku untuk postingan lama dan baru.
- Register 25 Reel resmi Juli–5 September tersedia pada Sekolah → Arsip Reel Manual. 20 draf dibuat, 0 galat, lima entri lama dilewati. Satu konten kolaborator milik SMA Queen dikecualikan.
- Pengujian PHP yang tersedia lulus, termasuk batas tanggal dan deteksi duplikasi arsip.

## Belum selesai

- MP4 belum berhasil diunduh maupun diunggah. Koneksi CDN Instagram melalui lingkungan kerja ditolak. Draf bukan bukti video sudah tersedia.
- Empat akun Waka masih siap dibuat; belum ada password dibagikan.
- Sinkronisasi GitHub, audit semua tautan, pengukuran analitik, dan uji pemulihan belum terverifikasi.

## Temuan Site Health

WordPress melaporkan satu masalah kritis: PHP hosting 7.4.33 usang dan tidak menerima pembaruan keamanan. Lima rekomendasi: hapus plugin nonaktif setelah ditinjau, sediakan tema default, lengkapi modul PHP yang direkomendasikan, aktifkan opcode cache, dan kaji persistent object cache.

## Tindak lanjut

1. Backup lengkap termasuk uploads dan staging; uji versi PHP yang masih didukung sebelum menggantinya melalui panel hosting.
2. Lengkapi 25 MP4 asli, cocokkan kode sumber, periksa suara/gambar, poster dan caption; unggah bertahap sesuai batas WordPress 64 MB yang terlihat.
3. Terbitkan hanya Galeri dengan video yang berhasil diputar. Kredit Instagram tetap dipertahankan.
4. Backup di lokasi terpisah serta uji pemulihan; jadwal otomatis belum diatur.
5. Lanjutkan audit tautan, visual, perangkat seluler dan baseline analitik.

Tidak ada penghapusan konten lama atau perubahan versi PHP hosting dalam pelaksanaan ini.

## Audit navigasi dan informasi — lanjutan 5 September

Delapan alamat unik dari navigasi utama/footer diperiksa melalui halaman yang dirender (bukan pemeriksaan HTTP menyeluruh). Kontak, Aplikasi, Program Keahlian, PPDB, Sarana Prasarana, serta tiga halaman detail layanan/fasilitas dapat dibuka. Tidak ditemukan halaman 404 pada sampel ini.

Temuan yang perlu ditindaklanjuti:

- Footer Sarana Prasarana mengarah ke `/sarana-prasarana/sarana-prasarana`, satu entri tanpa isi, bukan arsip `/sarana-prasarana`. Item menu teridentifikasi sebagai #574 pada Menu Footer; belum diubah.
- Kontak masih memuat instruksi editor “Periksa kembali jam layanan...” dan dua versi penulisan alamat. Pertahankan informasi kontak terpusat dan tinjau paragraf lama.
- PPDB masih memuat instruksi editor “Periksa tahun ajaran...” tanpa jadwal, kuota, biaya dan persyaratan terverifikasi. Data panitia diperlukan; jangan mengarang.
- Beranda menampilkan Akreditasi B di identitas/footer tetapi B+ pada statistik. Perlu keputusan berdasarkan sertifikat; belum diubah.
- Pusat Aplikasi menyatakan Ujian Online, E-Rapor dan Buku & E-Perpustakaan belum tersedia. Ini bukan tautan rusak; URL resmi dari penanggung jawab diperlukan.
- Arsip Program Keahlian dan Sarpras masih memakai prefiks Inggris “Archives:”. Perbaikan bahasa antarmuka dapat masuk paket berikutnya.
- Terdapat gambar dengan alt kosong di kartu arsip. Perlu pemeriksaan konteks aksesibilitas; alt kosong pada gambar dekoratif/tautan dengan nama yang sudah jelas tidak otomatis merupakan kesalahan.
- Agenda beranda belum berisi jadwal mendatang. Isi hanya berdasarkan kalender kegiatan sekolah yang disahkan.

Prioritas operasional: PHP hosting dan backup penuh terlebih dahulu; kemudian arah footer dan catatan editor; berikutnya verifikasi data akreditasi, PPDB, URL aplikasi, dan agenda bersama pemilik informasi.
