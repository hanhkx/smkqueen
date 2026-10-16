# Progres Program Kerja Website — Oktober 2026

Tanggal pemeriksaan: 16 Oktober 2026  
Dasar: Program Kerja Pengelolaan Website SMK Queen Al-Falah 2026/2027 dan tindak lanjut jurnal Agustus–September.

## Prioritas Oktober

1. Kelengkapan layanan dan halaman inti: Program Keahlian, Guru/Tendik, Sarana Prasarana, Ekstrakurikuler, serta Struktur Organisasi.
2. Audit aksesibilitas dan performa tahap I.
3. Pemutakhiran Berita, Prestasi, Karya Siswa, dan Galeri dari kanal resmi tanpa duplikasi.
4. Tata kelola konten: penanggung jawab data, tanggal tinjau, sumber, kredit, alt text, dan media WebP.

## Hasil pemeriksaan awal

| Area | Terbit | Kekurangan awal yang terdeteksi |
|---|---:|---|
| Program Keahlian | 4 | Isi, ringkasan, dan gambar utama telah tersedia; penanggung jawab data dan tanggal tinjau perlu dicatat. |
| Guru & Tendik | 66 | 17 profil belum memiliki foto utama; penanggung jawab data dan tanggal tinjau perlu dicatat. |
| Sarana Prasarana | 21 | 21 item belum memiliki foto utama; 13 item belum memiliki ringkasan/narasi; metadata kondisi perlu ditinjau. |
| Ekstrakurikuler | 11 | Narasi dan ringkasan tersedia; gambar masih mengandalkan ilustrasi fallback dan data tinjau perlu dicatat. |
| Struktur Organisasi | 1 halaman | Halaman tersedia; perlu validasi berkala terhadap SK dan perubahan personel. |

Data di atas adalah baseline untuk memprioritaskan pekerjaan editor, bukan alasan untuk mengarang informasi atau memakai gambar yang tidak sesuai.

## Implementasi paket 1.25.0

- Menambahkan menu administrator **Sekolah → Audit Kelengkapan** yang bersifat baca-saja.
- Audit memeriksa gambar utama, ringkasan, narasi, field wajib per jenis konten, penanggung jawab data, dan tanggal tinjau.
- Menambahkan field **Penanggung Jawab Data** dan **Tanggal Tinjau Terakhir** pada Program Keahlian, Guru/Tendik, Sarana Prasarana, dan Ekstrakurikuler.
- Menambahkan berita apresiasi Timnas Indonesia dari unggahan resmi Instagram 5 Oktober 2026 dengan sampul WebP sumber dan narasi standar website.
- Importer memanfaatkan draf yang cocok berdasarkan sumber/slug sehingga tidak membuat duplikasi dan tidak mengganti narasi editor yang sudah terisi.
- Audit kanal Instagram dan YouTube resmi tidak menemukan sumber baru lain setelah paket 1.24.0 yang layak dijadikan Prestasi, Karya Siswa, atau Galeri; entri yang sudah terbit dipertahankan.

## Pembaruan 16 Oktober 2026

- Menerapkan **Queen Al-Falah Core 1.26.1** pada situs produksi. Paket menambahkan satu Berita dan satu Galeri dari Reel Instagram resmi **“Yuk Happy di Sekolah”**, unggahan 16 Oktober 2026, serta mencegah konten hari yang sama tertahan sebagai terjadwal akibat perbedaan jam server.
- Thumbnail WebP diambil dari cuplikan video sumber, bukan gambar generatif. Paket menyertakan alt text, caption, kredit, URL sumber, tanggal unggahan, dan pencegahan duplikasi berdasarkan ID sumber, URL, serta slug.
- Pemeriksaan feed Instagram dan kanal YouTube tidak menemukan Prestasi atau Karya Siswa baru setelah paket sebelumnya yang dapat diverifikasi. Tidak dibuat entri tambahan agar arsip tetap faktual.
- Menerapkan **Tema 1.11.0** dengan pilihan video MP4/WebM untuk background hero melalui Customizer. MP4 produksi berukuran sekitar 0,94 MB telah dipilih; saat video aktif, GIF besar tidak ikut dimuat. Animasi juga dinonaktifkan bagi pengguna yang memilih pengurangan gerak.
- Menambahkan meta description fallback serta peningkatan kontras untuk strip pengumuman, label Prestasi, Jalur Pengembangan Karier, dan footer.
- Seluruh pemeriksaan sintaks PHP, 39 kontrak PHP, uji interaksi JavaScript, dan validasi struktur ZIP WordPress dinyatakan lulus.
- Cache LiteSpeed dibersihkan setelah pembaruan. Pemeriksaan anonim pada URL beranda normal memverifikasi Tema 1.11.0, video hero MP4, meta description, Berita baru, serta Galeri baru telah tampil tanpa parameter cache-busting.

## Tindak lanjut editor Oktober

1. Buka **Sekolah → Audit Kelengkapan**.
2. Prioritaskan 13 Sarana Prasarana tanpa narasi, lalu lengkapi foto asli sekolah setelah izin publikasi diperiksa.
3. Lengkapi 17 foto Guru/Tendik hanya dari arsip resmi dan dengan persetujuan publikasi.
4. Isi Penanggung Jawab Data serta Tanggal Tinjau untuk setiap item yang telah diverifikasi.
5. Validasi Struktur Organisasi terhadap SK terbaru, lalu catat perubahan personel bila ada.
6. Jalankan pengukuran PageSpeed/Lighthouse pada jaringan stabil; target laboratorium program kerja adalah skor aksesibilitas minimal 90, performa seluler minimal 75, dan desktop minimal 90.
7. Pertahankan ritme minimal delapan publikasi per bulan, sekurangnya empat publikasi substantif, dengan SLA publikasi maksimal tiga hari setelah bahan terverifikasi tersedia.

## Catatan aksesibilitas dan performa tahap I

- Tema telah memiliki gaya `:focus-visible`, dukungan `prefers-reduced-motion`, gambar lazy-loading, dan navigasi responsif.
- PageSpeed API mengembalikan batas kuota, sehingga baseline diambil langsung memakai Lighthouse 12.8.2 pada 16 Oktober 2026.

| Mode | Performa | Aksesibilitas | Praktik Terbaik | SEO | FCP | LCP | TBT | CLS |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| Desktop | 100 | 96 | 100 | 92 | 0,4 dtk | 0,4 dtk | 0 md | 0 |
| Seluler | 57 | 96 | 100 | 92 | 1,4 dtk | 34,6 dtk | 330 md | 0 |

- Hambatan utama seluler adalah GIF background hero sekitar **6,7 MB**. Salinan lokal telah dikonversi menjadi MP4 sekitar **0,94 MB** (lebih kecil sekitar 86%) untuk dipilih dari Customizer setelah tema terpasang.
- Temuan kontras dan meta description telah diperbaiki pada Tema 1.11.0.
- Gambar baru dalam paket ini berformat WebP, memiliki alt text, kredit, dan tautan sumber.

### Hasil sesudah implementasi produksi

Pengukuran ulang memakai Lighthouse 12.8.2 setelah Tema 1.11.0 dan Plugin 1.26.1 aktif, MP4 hero dipilih, serta cache LiteSpeed dibersihkan.

| Mode | Performa | Aksesibilitas | Praktik Terbaik | SEO | FCP | LCP | TBT | CLS | Transfer |
|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| Desktop | 89 | 96 | 100 | 100 | 0,4 dtk | 0,4 dtk | 280 md | 0 | 1.118 KiB |
| Seluler | 87 | 96 | 100 | 100 | 1,2 dtk | 1,7 dtk | 410 md | 0,092 | 1.105 KiB |

- Skor performa seluler meningkat dari **57 menjadi 87**; LCP seluler membaik dari **34,6 detik menjadi 1,7 detik**.
- SEO meningkat dari **92 menjadi 100** pada desktop dan seluler.
- Target seluler minimal 75 telah terlampaui. Skor desktop 89 berada satu poin di bawah target laboratorium 90; tindak lanjut berikutnya adalah meninjau tugas main-thread dan stabilitas pengukuran tanpa mengurangi fungsi halaman.

## Langkah penerapan ke situs produksi

1. Cadangkan basis data dan `wp-content` bila cadangan harian hosting belum tersedia.
2. Unggah dan ganti plugin dengan `queen-alfalah-core-1.26.1.zip`, lalu masuk ke dashboard sebagai administrator satu kali agar importer menambahkan konten yang belum ada.
3. Unggah dan ganti tema dengan `queen-alfalah-1.11.0.zip`.
4. Unggah `hero-background-optimized.mp4`, lalu pilih melalui **Tampilan → Sesuaikan → Hero/Beranda → Video background landing page** dan simpan perubahan.
5. Verifikasi Berita dan Galeri baru, thumbnail, sumber, tanggal, navigasi seluler, meta description, serta preferensi pengurangan gerak.
6. Bersihkan cache WordPress/CDN dan ulangi Lighthouse untuk mencatat hasil sesudah implementasi.
