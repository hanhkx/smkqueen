# Queen Al-Falah Core

Companion plugin resmi untuk tema **Queen Al-Falah**. Plugin memisahkan model konten dan data sekolah dari lapisan tampilan, sehingga program keahlian, pengumuman, agenda, dan data kelembagaan tetap tersedia ketika tema diganti.

Versi: **1.26.1**
WordPress minimum: **6.2**  
PHP minimum: **7.4**  
Lisensi: **GPL-2.0-or-later**

## Fitur

- Menu **Sekolah → Optimasi Gambar** membuat varian WebP terkompresi untuk JPEG/PNG beserta semua thumbnail, memakai WebP pada tampilan publik, dan mengoptimalkan unggahan baru otomatis. Mode hemat penyimpanan dapat mengganti metadata attachment lalu menghapus JPG/PNG hanya setelah seluruh varian berhasil dibuat; metadata lama dicadangkan.
- Menu **Sekolah → Audit Kelengkapan** memetakan kekurangan isi, gambar, metadata inti, penanggung jawab data, serta tanggal tinjau pada empat area prioritas Oktober tanpa mengubah konten otomatis.
- Menu **Sekolah → Standar Narasi** untuk menata seluruh Berita, Galeri, Prestasi, dan Karya Siswa terbit menjadi artikel terstruktur, kontekstual, dan tetap faktual; narasi lama dicadangkan dan dapat dipulihkan.
- Galeri dan beranda: album bertanggal terbaru ke terlama di atas, lalu album tanpa tanggal menurut publikasi paling lama ke paling baru.
- Menu **Sekolah → Tanggal Album Galeri** untuk menerapkan tanggal yang sudah diverifikasi dari video; hanya metadata tanggal yang diperbarui dan tanggal lama dicadangkan.
- Tanggal kegiatan tidak ditebak dari tanggal publikasi atau unggahan Instagram. Lihat `../queen-alfalah/docs/TANGGAL-ALBUM-GALERI.md` untuk penerapan dan bukti tanggal.
- 12 custom post type (CPT) sekolah dan 14 taksonomi publik.
- Meta terstruktur yang terdaftar pada Metadata API dan REST API.
- Meta box klasik/Gutenberg dengan sanitasi per tipe, nonce, dan pemeriksaan kemampuan pengguna.
- Pengaturan identitas, visi-misi, kontak, lokasi, pendaftaran, dan media sosial.
- Kolom admin dan pengurutan data terstruktur.
- Status aplikasi terlihat langsung di daftar admin. Editor dapat memilih Aktif, Pemeliharaan, atau Nonaktif; tema hanya menampilkan kartu Aktif dengan URL HTTP/HTTPS lengkap.
- Arsip agenda terurut berdasarkan tanggal mulai.
- Arsip pengumuman publik menyembunyikan item kedaluwarsa tanpa mengubah daftar admin.
- Importer demo satu klik yang aman, idempoten, dan tidak menimpa konten pengguna.
- Portal Pusat Media privat dengan folder Google Drive khusus per akun serta folder divisi sebagai cadangan.
- Tautan Google Drive terpisah untuk Waka, Guru, dan Tendik yang dapat diganti dari Pengaturan Sekolah.
- Dashboard Waka dengan capability khusus untuk Media Library dan Pengumuman, tanpa akses generik ke Berita, Halaman, pengguna, plugin, atau pengaturan.
- Bursa Kerja terstruktur dengan sepuluh lowongan terverifikasi lintas DKV, TJKT, MPLB, dan Layanan Kesehatan, dilengkapi logo, lokasi, gaji, pendidikan, platform sumber, tanggal verifikasi, dan tombol lamaran langsung.
- Penilaian Kinerja Guru yang mewajibkan nama dan kelas lengkap, memakai sepuluh pertanyaan pengalaman belajar dan tiga refleksi tertulis, serta mencegah pengisian ganda per guru/semester.
- Analisis lokal berbasis aturan menampilkan sepuluh grafik aspek, tema komentar, sebaran kelas, kesimpulan, dan tiga saran tindak lanjut tanpa mengirim data siswa ke layanan eksternal.
- Provisioner administrator untuk empat akun Waka dari struktur organisasi dengan username tetap, password sementara acak, deteksi akun lama, dan kewajiban ganti password pertama.
- Struktur organisasi tahun pelajaran 2026/2027 dengan tupoksi per jabatan dan foto dari direktori Guru & Tendik.
- Importer direktori Guru & Tendik yang menambahkan 13 personel internal yang belum tercatat tanpa mengubah profil lama, status, taksonomi, isi, atau foto pilihan administrator.
- Sebelas alias ejaan nama yang menyambungkan kembali profil/foto lama ke kartu Struktur Organisasi tanpa mengganti judul profil.
- Metadata prestasi lengkap untuk penyelenggara, juara/penghargaan, bidang, tingkat, dan tautan sumber.
- Importer prestasi berversi yang idempoten serta tidak menimpa suntingan administrator.
- Dua puluh sembilan halaman prestasi/partisipasi tahun 2023–2026 tanpa menimpa 17 entri lama. Dua puluh empat halaman menerima gambar sumber lokal pada slot kosong; lima halaman tanpa foto tepat memakai fallback berlabel ilustrasi.
- Enam data awal Sarana Prasarana dengan field lokasi, fungsi, fitur, akses, pengelola, kapasitas, kondisi, dan tanggal pemeriksaan.
- Empat belas profil awal PKL & Mitra Industri dengan sumber dan status verifikasi yang dapat ditinjau administrator.
- Lima belas kartu logo/foto profil Mitra DUDI berasio 3:2 dengan sumber, kredit, alt text, dukungan slug legacy, dan lima identitas sementara yang jelas ketika logo resmi belum terverifikasi.
- Sebelas data ekstrakurikuler dengan manfaat, relevansi dunia kerja, informasi keikutsertaan, dan ilustrasi fallback.
- Galeri lokal dan konten sosial Instagram, TikTok, Facebook, atau YouTube dengan jenis media, pemilih video, perilaku embed, dan filter sumber.
- Sinkronisasi Reel/video akun Instagram Profesional melalui API resmi, dengan jadwal harian opsional, deduplikasi ID media, status draf/terbit, poster lokal, status koneksi, dan pembaruan long-lived token.
- Delapan sampul berita Agustus 2026 dari akun Instagram resmi yang disalin ke Media Library tanpa hotlink CDN sementara.
- Sinkronisasi gambar Karya Siswa Batch 2 dari cover frame Instagram dan thumbnail kanal YouTube resmi, hanya untuk slot gambar unggulan yang masih kosong.
- Caption, alt text, kredit gambar, dan URL sumber yang dapat dikelola pada Berita dan Media Library.
- Integrasi opsional Google Analytics 4 dan verifikasi Search Console yang nonaktif sampai ID resmi disimpan.
- Data dipertahankan saat plugin dihapus.

## Instalasi

1. Unggah folder `queen-alfalah-core` ke `wp-content/plugins/` atau unggah ZIP melalui **Plugin > Tambah Plugin**.
2. Aktifkan **Queen Al-Falah Core**.
3. Aktifkan tema **Queen Al-Falah** atau child theme-nya.
4. Buka **Sekolah > Pengaturan** dan periksa seluruh identitas resmi.
5. Bila memerlukan kerangka awal, buka **Sekolah > Penyiapan Demo** lalu klik **Siapkan Situs Demo**.
6. Tinjau draf dan data awal sebelum situs dipublikasikan.
7. Simpan ulang **Pengaturan > Permalink** hanya bila rute masih menampilkan 404 setelah perubahan infrastruktur.

Aktivasi plugin mendaftarkan CPT/taksonomi, menambahkan default secara non-destruktif, dan menyegarkan rewrite rules. Deaktivasi melepas model konten untuk request berikutnya dan menyegarkan rewrite rules. Request biasa dan importer tidak melakukan flush rewrite.

## Model konten

| CPT | Slug publik | Kegunaan |
|---|---|---|
| `qaf_program` | `program-keahlian` | Program/kompetensi keahlian |
| `qaf_teacher` | `guru-tendik` | Guru dan tenaga kependidikan |
| `qaf_notice` | `pengumuman` | Pengumuman resmi |
| `qaf_agenda` | `agenda` | Agenda bertanggal |
| `qaf_achievement` | `prestasi` | Prestasi sekolah/warga sekolah |
| `qaf_extra` | `ekstrakurikuler` | Kegiatan ekstrakurikuler |
| `qaf_service` | `layanan` | Akses layanan digital |
| `qaf_gallery` | `galeri` | Album foto/video |
| `qaf_partner` | `mitra-industri` | Mitra sekolah/industri |
| `qaf_vacancy` | `lowongan-kerja` | Lowongan terverifikasi BKK |
| `qaf_alumni` | `alumni` | Kisah alumni dengan persetujuan |
| `qaf_facility` | `sarana-prasarana` | Sarana dan prasarana |

Semua CPT mendukung judul, editor, ringkasan, gambar unggulan, urutan halaman, revisi, custom fields, menu, arsip, dan REST API.

## Kontrak meta tema

Seluruh key menggunakan awalan privat `_qaf_` dan satu nilai per post:

- Berita: `_qaf_instagram_source_url`, `_qaf_image_credit`, `_qaf_news_source_id`.
- Program: `_qaf_program_code`, `_qaf_program_head`, `_qaf_program_gender`, `_qaf_competencies`, `_qaf_careers`.
- Guru/tendik: `_qaf_role`, `_qaf_subject`, `_qaf_order`, `_qaf_staff_source_id` (readonly untuk record baru dari struktur).
- Pengumuman: `_qaf_priority`, `_qaf_expiry`, `_qaf_file_url`.
- Agenda: `_qaf_start_date`, `_qaf_end_date`, `_qaf_location`.
- Prestasi: `_qaf_level`, `_qaf_achievement_date`, `_qaf_recipient`, `_qaf_organizer`, `_qaf_award`, `_qaf_field`, `_qaf_source_url`, `_qaf_achievement_source_id`.
- Ekstrakurikuler: `_qaf_schedule`, `_qaf_coach`, `_qaf_extra_location`, `_qaf_benefits`, `_qaf_career_relevance`, `_qaf_join_info`, `_qaf_extra_seed_key`.
- Layanan: `_qaf_external_url`, `_qaf_icon_name`, `_qaf_open_new`.
- Galeri: `_qaf_gallery_source`, `_qaf_gallery_media_type`, `_qaf_video_url`, `_qaf_gallery_local_video_id`, `_qaf_gallery_embed_behavior`, `_qaf_album_date`.
- Mitra: `_qaf_partner_url`, `_qaf_partner_sector`, `_qaf_partner_legal_name`, `_qaf_partner_location`, `_qaf_partner_programs`, `_qaf_partner_expertise`, `_qaf_partner_cooperation`, `_qaf_partner_source_url`, `_qaf_partner_verification`, `_qaf_partner_seed_key`.
- Lowongan: `_qaf_deadline`, `_qaf_company`, `_qaf_apply_url`.
- Alumni: `_qaf_graduation_year`, `_qaf_current_role`.
- Sarana: `_qaf_capacity`, `_qaf_facility_status`, `_qaf_facility_location`, `_qaf_facility_function`, `_qaf_facility_features`, `_qaf_facility_access`, `_qaf_facility_manager`, `_qaf_facility_last_check`, `_qaf_facility_seed_key`.

Tanggal memakai `YYYY-MM-DD`; waktu lokal memakai `YYYY-MM-DDTHH:MM`. URL umum dibatasi ke HTTP/HTTPS, sedangkan URL sosial Galeri wajib HTTPS dan host-nya harus berada dalam allowlist platform. Nilai pilihan divalidasi terhadap daftar yang disediakan plugin.

Contoh penggunaan dari tema:

```php
$start = get_post_meta( get_the_ID(), '_qaf_start_date', true );
$name  = function_exists( 'qaf_core_get_setting' )
	? qaf_core_get_setting( 'school_name', get_bloginfo( 'name' ) )
	: get_bloginfo( 'name' );
```

## Pengaturan sekolah

Semua pengaturan disimpan sebagai satu option `qaf_core_settings`. Default dipasang dengan `add_option`, sehingga aktivasi ulang tidak menimpa nilai yang sudah disunting. Kelompok data mencakup:

- nama resmi, nama singkat, moto, NPSN, tanggal berdiri, akreditasi, dan yayasan;
- visi dan misi;
- alamat, telepon, email, koordinat, dan tautan peta;
- URL pendaftaran;
- Facebook, Instagram, YouTube, dan TikTok;
- Measurement ID Google Analytics 4 dan token verifikasi Search Console (opsional);
- tautan folder Google Drive untuk divisi Waka, Guru, dan Tendik.

Tautan atau akun yang belum resmi sebaiknya dikosongkan. Hak akses halaman pengaturan dapat disesuaikan melalui filter `qaf_core_manage_settings_capability`; default-nya `manage_options`.

Analytics dan Search Console tidak aktif secara bawaan. Isi Measurement ID berformat `G-XXXXXXXXXX` dan hanya nilai `content` dari meta tag `google-site-verification`. Jangan isi Measurement ID apabila pelacakan yang sama sudah dipasang oleh plugin lain. Sebelum mengaktifkan Analytics, sesuaikan pemberitahuan privasi dan tata kelola data pengunjung dengan kebijakan sekolah.

## Importer demo

Importer tersedia di **Sekolah > Penyiapan Demo** dan hanya dapat dijalankan melalui request POST oleh pengguna dengan kemampuan pengelolaan plugin. Request dilindungi nonce. Importer:

1. membuat Beranda, Berita, Profil, Sambutan, Visi-Misi, Sejarah, Struktur Organisasi, Kesiswaan, Informasi, PPDB, BKK, dan Kontak;
2. mengatur Beranda statis dan halaman Berita;
3. membuat empat program yang namanya telah menjadi data awal tema: TJKT, MPLB, DKV, dan Layanan Kesehatan;
4. memastikan tujuh slug ekstrakurikuler lama tersedia; bila belum ada, importer demo membuat kerangka **draf** yang dapat dilengkapi katalog berversi;
5. menerbitkan enam entri akses layanan yang hanya mengarah ke halaman/arsip lokal atau URL pendaftaran yang dikonfigurasi;
6. membuat draf berita, pengumuman, dan agenda dengan label jelas;
7. membuat empat menu dan hanya mengisi lokasi tema yang masih kosong.

Idempotensi dijaga dengan meta privat `_qaf_demo_key` serta slug stabil. Import ulang memakai kembali item yang ditemukan, termasuk item pengguna dengan slug/objek yang sama. Konten yang sudah ada tidak diperbarui, status item di Sampah tidak dipulihkan, menu yang sudah ditetapkan tidak diganti, dan tidak ada data yang dihapus.

Importer demo tidak menerbitkan data operasional, lowongan, statistik, atau konten contoh bertanggal. Katalog mitra, sarana, ekstrakurikuler, dan prestasi memakai dataset berversi terpisah; administrator tetap wajib memeriksa nomenklatur, status kerja sama, jadwal, pembina, tautan, periode, izin foto, dan semua fakta bertanggal.

## Katalog konten berversi

Saat plugin diaktifkan atau administrator membuka dashboard setelah pembaruan, katalog berversi menambahkan item yang belum ada untuk Sarana Prasarana, PKL & Mitra Industri, dan Ekstrakurikuler. Prosesnya aman untuk dijalankan ulang: item di Sampah tidak dipulihkan, konten manual dengan slug sama tidak diambil alih, dan suntingan administrator tidak diganti.

- **Sarana Prasarana:** enam entri awal yang dapat dilengkapi dengan foto, kapasitas, kondisi, fitur, akses, pengelola, dan tanggal pemeriksaan terakhir.
- **PKL & Mitra Industri:** 14 profil awal berisi identitas, lokasi, bidang keahlian, program terkait, bentuk dukungan/kerja sama, sumber publik, dan status verifikasi. Profil publik membantu pengisian awal, tetapi tidak membuktikan MoU aktif, periode, kuota, atau penempatan; cocokkan kembali dengan dokumen sekolah.
- **Ekstrakurikuler:** 11 entri berisi manfaat kegiatan dan relevansinya bagi kompetensi kerja. Field yang sudah berisi data tidak ditimpa. Gambar unggulan buatan administrator selalu diprioritaskan, kemudian ilustrasi bawaan dipakai sebagai fallback.

Importer Prestasi menangani 29 halaman berdasarkan ID sumber stabil dari Instagram/YouTube resmi, situs sekolah, hasil OLPAIN, dan dokumen LKS. Dua belas record baru menambahkan prestasi individual serta partisipasi yang secara eksplisit tidak diklaim sebagai juara. Sebanyak 21 berkas gambar sumber dipakai oleh 24 halaman; gambar hanya dimasukkan ke Media Library dan dijadikan gambar unggulan ketika post belum memiliki pilihan administrator. Halaman tanpa foto yang tepat memakai fallback tema berlabel ilustrasi. Item di Sampah, status post, dan suntingan teks tetap dipertahankan. Kunci pembaruan atomik mencegah duplikasi ketika dua request admin terjadi bersamaan.

## Direktori Guru & Tendik non-destruktif

Saat aktivasi atau kunjungan administrator pertama setelah pembaruan, plugin membandingkan data struktur dengan seluruh profil Guru & Tendik pada semua status, termasuk Sampah. Pemeriksaan memakai ID sumber, nama kanonik, alias ejaan, slug aktif, dan slug lama. Profil yang sudah ada dilewati sepenuhnya: judul, isi, status, meta, taksonomi, gambar unggulan, dan keputusan menghapus ke Sampah tidak diubah.

Dataset 1.0.0 hanya menambahkan 13 personel internal yang belum ditemukan pada ekspor situs 26 Agustus 2026: 5 guru/pimpinan serta 8 tendik/pembantu pelaksana. Della dari Sanggar Tari dipertahankan sebagai pendamping eksternal pada struktur dan tidak dibuat sebagai profil Guru/Tendik. Profil baru memakai fallback ilustrasi orang sampai administrator memilih foto resmi. Sebelas variasi ejaan profil lama dipakai hanya untuk pencocokan foto dan tautan pada Struktur Organisasi; judul profil lama tidak diganti.

Importer Media Berita menangani dua belas artikel Agustus–September 2026 berdasarkan slug ekspor dan ID unggahan Instagram resmi. Slide pertama disimpan sebagai aset lokal, lalu dimasukkan ke Media Library bersama caption, alt text, kredit, serta URL sumber. Berita Maulid Nabi 1448 H dibuat hanya ketika sumber dan slug belum ada; artikel lain tetap berupa pembaruan media untuk berita yang sudah tersedia. Importer hanya mengisi slot gambar unggulan kosong, tidak memulihkan item di Sampah, serta tidak mengganti isi, gambar, atau metadata yang sudah disunting administrator. Gunakan tema Queen Al-Falah 1.5.0 atau lebih baru agar caption dan kredit tampil di halaman detail.

## Penggunaan Galeri

Setiap entri `qaf_gallery` dapat memakai media lokal atau satu URL konten sosial. Gambar Utama tetap menjadi sampul kartu/arsip.

### Media lokal

1. Pilih **Sumber Media: Lokal / Media Library**.
2. Pilih **Jenis Media: Foto, Video, atau Foto dan Video**.
3. Susun beberapa foto/video dengan blok **Image**, **Gallery**, dan **Video** pada editor konten.
4. Bila hanya membutuhkan satu video, gunakan field **Video Lokal** untuk memilih attachment video dari Media Library.

Pemilih hanya menerima attachment dengan MIME `video/*`; nilai nol atau pilihan yang tidak valid tidak disimpan. Fitur Galeri tidak memberi capability tambahan: pengguna tetap harus memiliki izin WordPress untuk menyunting post terkait, dan unggahan media mengikuti izin `upload_files`. Pastikan sekolah memiliki hak penggunaan serta persetujuan publikasi untuk setiap foto/video.

### Konten platform

Pilih Instagram, TikTok, Facebook, atau YouTube, kemudian masukkan URL HTTPS kanonik satu postingan/video pada field **URL Konten Sosial / Video**. Host yang diterima dibatasi secara tepat ke domain resmi yang didukung. Gunakan URL halaman konten, bukan:

- kode `<iframe>` atau HTML embed mentah;
- token API, client secret, cookie, atau kredensial;
- URL profil, hashtag, playlist, atau feed untuk sinkronisasi otomatis.

Entri manual tidak mengambil feed dan tidak menerima token pada field konten. Sinkronisasi Instagram otomatis dikonfigurasi terpisah melalui **Sekolah → Instagram Galeri**; tokennya disimpan sebagai option privat/non-autoload di server, tidak dicetak kembali, dan tidak dikirim ke renderer tema atau browser pengunjung. Renderer tetap wajib melakukan escaping/allowlist dan tidak boleh menampilkan HTML dari field secara langsung.

### Sinkronisasi Reel/video Instagram

Queen Al-Falah Core dapat membaca media akun Instagram Profesional melalui **Instagram API with Instagram Login**. Akun memerlukan aplikasi Meta bertipe Business, izin `instagram_business_basic`, Instagram User ID numerik, dan long-lived access token.

1. Buka **Sekolah → Instagram Galeri**.
2. Isi User ID dan long-lived token dari aplikasi Meta resmi sekolah.
3. Pilih status **Draf** (disarankan) atau **Terbit otomatis**.
4. Tentukan jumlah postingan terbaru yang diperiksa dan apakah thumbnail disalin ke Media Library.
5. Simpan, tekan **Sinkronkan Sekarang**, lalu aktifkan pemeriksaan harian setelah hasil uji benar.

Hanya media `VIDEO`, termasuk Reel, yang dibuat. ID media mencegah duplikasi; item di Sampah tidak dipulihkan; konten yang sudah ada tidak dihapus; judul, isi, status, perilaku embed, dan gambar pilihan administrator dipertahankan. Poster disalin secara terbatas dari host CDN Meta/Instagram ke Media Library agar kartu arsip tidak bergantung pada URL sementara. Panduan lengkap tersedia di [`INSTAGRAM-GALLERY-SETUP.md`](INSTAGRAM-GALLERY-SETUP.md).

### Perilaku embed

- **Klik untuk memuat** adalah default. Tema Queen Al-Falah 1.5.0 menampilkan pengganti dan baru memuat embed setelah tindakan pengunjung; ini mengurangi permintaan pihak ketiga sebelum interaksi.
- **Muat otomatis** meminta tema memuat embed ketika halaman dibuka. Gunakan hanya setelah menilai kebijakan cookie/privasi karena platform dapat menerima alamat IP, user-agent, referrer, cookie, atau data teknis pengunjung.
- **Tautan saja** tidak menyematkan konten dan hanya menyediakan tautan menuju platform. Ini pilihan yang paling konservatif bila embed tidak diperlukan.

Mode klik-untuk-muat mengurangi pemuatan awal pihak ketiga, tetapi bukan pengganti kebijakan privasi atau persetujuan yang diwajibkan oleh yurisdiksi dan kebijakan sekolah.

### Kompatibilitas dan filter

Entri lama tanpa `_qaf_gallery_source` tetap memakai nilai kosong **Otomatis / kompatibilitas lama**. Tema Queen Al-Falah 1.5.0 dapat mengenali URL platform yang didukung dari `_qaf_video_url`; URL legacy yang tidak lolos allowlist tidak terhapus hanya karena post disimpan, tetapi tidak boleh di-embed sebagai HTML. Perbarui URL tersebut menjadi URL HTTPS kanonik atau gunakan **Tautan saja**.

Arsip Galeri menerima parameter allowlist `?sumber=local`, `instagram`, `tiktok`, `facebook`, atau `youtube`. Filter lokal sengaja mencakup entri dengan sumber `local`, kosong, atau belum memiliki metadata agar konten lama tetap dapat ditemukan. Parameter lain diabaikan.

## Perilaku arsip

- Main query arsip `qaf_agenda` di front-end memakai `_qaf_start_date` urut naik.
- Main query arsip `qaf_notice` di front-end menampilkan item tanpa tanggal kedaluwarsa, tanggal kosong, atau tanggal kedaluwarsa yang masih hari ini/masa depan.
- Main query arsip `qaf_gallery` dapat disaring dengan parameter allowlist `?sumber=` tanpa menghapus `meta_query` lain yang sudah aktif.
- Admin, REST API, singular, pencarian, dan secondary query tidak diubah oleh aturan tersebut.

## Keamanan dan privasi

- Meta box menyimpan data hanya setelah nonce valid, bukan autosave/revisi, dan pengguna dapat `edit_post`.
- REST meta memerlukan kemampuan menyunting post terkait.
- Attachment video Galeri diperiksa sebagai attachment WordPress dan wajib memiliki MIME `video/*`.
- URL sosial Galeri hanya menerima HTTPS pada host Instagram, TikTok, Facebook, atau YouTube yang didukung; URL look-alike dan port nonstandar ditolak.
- Plugin tidak menyimpan iframe mentah. Token Instagram disimpan hanya pada option privat/non-autoload, tidak ditampilkan kembali, tidak dimasukkan ke REST settings, JavaScript, HTML publik, ZIP, atau repository.
- Request sinkronisasi admin memerlukan kemampuan pengaturan, metode POST, serta nonce; proses terjadwal memakai kunci sementara agar tidak berjalan tumpang tindih.
- API dibatasi ke `graph.instagram.com`, URL konten ke host Instagram yang tepat, dan thumbnail ke host CDN Meta/Instagram melalui pemeriksaan URL aman, MIME, serta batas 8 MB.
- Settings API melakukan sanitasi sesuai tipe data.
- Importer memerlukan kemampuan pengaturan, nonce, dan request POST.
- URL dibatasi ke protokol HTTP/HTTPS.
- Password dikelola oleh autentikasi WordPress; plugin tidak menyimpan password tambahan.
- Guru dan Tendik diarahkan ke Pusat Media serta dibatasi dari halaman administrasi WordPress; Waka diarahkan ke dashboard Pengumuman yang terbatas.
- Waka memiliki `upload_files` dan capability khusus `qaf_notice`, bukan capability generik Berita/Halaman. Mereka dapat mengelola Pengumuman bersama dan memilih audiens/jenis yang sudah tersedia, tetapi tidak dapat menghapus Pengumuman atau mengelola istilah taksonomi.
- Provisioner akun hanya menerima POST dari administrator dengan nonce yang valid. Daftar username bersifat tetap, akun yang sudah ada tidak diubah, email tidak dibuat-buat, dan password acak 32 karakter hanya ditampilkan pada respons tanpa transient/database tambahan.
- Pengguna portal menerima tautan Drive khusus akunnya. Jika belum diatur, mereka menerima satu tautan cadangan sesuai divisi; administrator dapat melihat status folder khusus pada daftar Pengguna.
- Pusat Media tidak memproses unggahan, unduhan, atau isi file. Keamanan dokumen ditentukan oleh aturan berbagi Google Drive.
- Media Library WordPress digunakan Waka hanya untuk lampiran Pengumuman yang boleh diakses publik. Dokumen internal/rahasia tetap harus berada di folder Drive berstatus **Dibatasi**.
- Jangan memasukkan NIK, NISN, alamat rumah, nomor pribadi, data kesehatan, atau foto tanpa dasar izin yang sesuai.

## Konfigurasi Pusat Media dan Google Drive

Pusat Media memakai tautan langsung dan tidak memerlukan Google Drive API, OAuth, service account, client secret, refresh token, maupun JSON key.

1. Sebaiknya gunakan Shared Drive Google Workspace sekolah, lalu buat satu folder khusus untuk setiap pengguna. Bila Shared Drive tidak tersedia, folder My Drive tetap dapat ditautkan tetapi kepemilikan dan kuota mengikuti kebijakan akun Google terkait.
2. Di Google Drive, pilih berbagi **Dibatasi** lalu tambahkan hanya akun Google pemilik folder tersebut. Tetapkan Viewer, Commenter, atau Editor sesuai kebutuhan.
3. Buka **Pengguna**, sunting akun terkait, lalu isi **Folder Google Drive akun**. Hanya URL HTTPS pada `drive.google.com` atau `docs.google.com` yang diterima.
4. Folder Waka, Guru, dan Tendik pada **Sekolah → Pengaturan → Pusat Media** tetap dapat diisi sebagai cadangan bagi akun yang belum memiliki folder khusus.
4. Untuk Waka, buka **Sekolah → Akun Waka**, periksa empat nama/username tetap, lalu tekan tombol pembuatan hanya jika data benar. Simpan password sementara yang tampil satu kali melalui saluran privat.
5. Untuk Guru/Tendik, buat akun melalui **Pengguna → Tambah Baru**, pilih peran Guru atau Tenaga Kependidikan, lalu isi Unit/Jabatan dan URL folder khususnya.
6. Uji login Guru/Tendik melalui `/pusat-media/`; mereka hanya boleh melihat folder khususnya atau satu folder divisi bila URL khusus kosong. Uji Waka melalui `/wp-login.php`; Waka wajib mengganti password sementara sebelum membuka Media dan Pengumuman.

Tautan dapat diganti kapan saja tanpa memindahkan data WordPress. Pembatasan peran pada portal hanya menentukan tautan yang ditampilkan; siapa yang dapat melihat, mengunggah, menyunting, atau mengunduh dokumen tetap ditentukan oleh izin folder di Google Drive. Jangan memakai opsi “Siapa saja yang memiliki link” untuk dokumen internal.

Panduan lengkap tersedia di [`GOOGLE-DRIVE-SETUP.md`](GOOGLE-DRIVE-SETUP.md).

## Struktur Organisasi

Halaman dengan slug `struktur-organisasi` dibuat atau dimigrasikan secara non-destruktif ketika administrator membuka dashboard setelah pembaruan plugin. Jika halaman Profil Sekolah sudah ada, halaman struktur ditempatkan sebagai turunannya. Halaman memuat shortcode `[qaf_organization]` dan menampilkan:

- 19 bidang/tim sesuai struktur tahun pelajaran 2026/2027;
- nama, jabatan, dan tupoksi setiap penugasan;
- foto serta tautan profil yang dicocokkan dari post type `qaf_teacher`;
- inisial sebagai fallback apabila foto/profil belum diterbitkan;
- indeks bagian dan layout responsif untuk desktop maupun perangkat seluler.

Pencocokan foto menggunakan nama tanpa gelar akademik dan beberapa variasi ejaan yang telah diketahui. Foto tetap dikelola satu kali melalui **Guru & Tendik → Gambar Utama**, sehingga perubahan foto otomatis muncul pada halaman struktur.

Tidak ada akun atau password yang dibuat otomatis saat plugin diaktifkan. Tool **Sekolah → Akun Waka** hanya berjalan setelah administrator menekan tombol terlindung nonce. Tool memakai empat username tetap, membuat password sementara unik untuk setiap akun yang benar-benar belum ada, dan tidak menyimpan password tersebut dalam source code.

## Penghapusan plugin

`uninstall.php` sengaja tidak menghapus post, term, menu, media, meta, atau pengaturan sekolah non-rahasia. Data institusional tetap tersedia setelah plugin dihapus. Khusus kredensial sinkronisasi Instagram, option token/status serta jadwalnya dihapus agar plugin yang sudah tidak ada tidak meninggalkan akses eksternal aktif. Jika plugin dipasang kembali, hubungkan ulang akun Instagram. Untuk menghapus konten permanen, ekspor cadangan lebih dahulu lalu hapus secara eksplisit melalui alat administrasi WordPress.

## Pemecahan masalah

**Menu CPT tidak terlihat**  
Pastikan plugin aktif. CPT ditempatkan di bawah menu admin **Sekolah**.

**Arsip menampilkan 404**  
Pastikan plugin aktif, lalu simpan ulang struktur permalink satu kali.

**Agenda tidak muncul di arsip**  
Pastikan statusnya Terbit dan field Mulai berisi format tanggal-waktu yang valid.

**Pengumuman hilang dari arsip publik**  
Periksa field Berlaku Sampai. Item kedaluwarsa tetap tersedia di admin dan URL singularnya, tetapi tidak dimasukkan ke arsip aktif.

**Importer tidak menetapkan menu**  
Aktifkan tema Queen Al-Falah dan jalankan importer lagi. Lokasi yang sudah berisi menu sengaja tidak ditimpa.

**Tautan Pusat Media tidak muncul**

Periksa peran akun, lalu isi **Folder Google Drive akun** pada profil pengguna. Jika dibiarkan kosong, isi URL divisi terkait pada **Sekolah → Pengaturan → Pusat Media**. Pastikan URL memakai HTTPS dan domain `drive.google.com` atau `docs.google.com`.

**Pengguna dapat/tidak dapat membuka file setelah masuk**

Periksa anggota serta level izin pada dialog berbagi folder Google Drive. Login WordPress hanya mengatur tautan yang terlihat dan tidak menggantikan izin Google Drive.

## Pengembangan

Plugin WordPress tidak memiliki dependency runtime atau library eksternal. Jalankan pemeriksaan sintaks untuk semua berkas PHP sebelum rilis:

```sh
find queen-alfalah-core -name '*.php' -exec php -l {} \;
```

Alat pengembangan opsional `tools/build-partner-images.mjs` memakai Node.js ≥20.9 dan Sharp yang versinya dikunci di `tools/package-lock.json`. Ikuti manifest sumber dan instruksi di [`tools/PARTNER-IMAGE-SOURCES.md`](../tools/PARTNER-IMAGE-SOURCES.md); dependency alat tidak dimasukkan ke ZIP plugin.

## Changelog

### 1.26.1 — 2026-10-16

- Menerbitkan segera Berita dan Galeri terverifikasi pada tanggal kalender yang sama meskipun jam server lebih awal daripada waktu unggahan sumber.
- Mempertahankan tanggal album, sumber, narasi, media, dan pencegahan duplikasi yang sudah tersimpan.

### 1.26.0 — 2026-10-16

- Menambahkan berita dan Galeri "Yuk Happy di Sekolah" dari Reel Instagram resmi 16 Oktober 2026 dengan thumbnail WebP sumber, narasi standar website, kredit, dan tanggal unggahan yang terverifikasi.
- Importer tetap non-destruktif dan mencegah duplikasi berdasarkan ID sumber, URL, serta slug.
- Audit Instagram/YouTube tidak menemukan Prestasi atau Karya Siswa baru yang terverifikasi setelah paket sebelumnya, sehingga arsip lama dipertahankan tanpa entri buatan.

### 1.25.1 — 2026-10-13

- Mengenali halaman Struktur Organisasi yang berada di bawah halaman induk Profil sehingga audit tidak memberi peringatan palsu.
- Menambahkan pencocokan judul tepat sebagai fallback terakhir untuk memanfaatkan draf lama ketika ID sumber, URL sumber, dan slug belum tersimpan.

### 1.25.0 — 2026-10-13

- Menambahkan berita apresiasi Timnas Indonesia dari unggahan Instagram resmi 5 Oktober 2026 beserta gambar WebP sumber, kredit, dan catatan editorial yang tidak mengklaim verifikasi hasil pertandingan.
- Menyelesaikan draf yang cocok berdasarkan ID sumber, URL Instagram, atau slug tanpa menduplikasi Berita dan tanpa menimpa narasi editor yang sudah terisi.
- Menambahkan audit kelengkapan untuk Program Keahlian, Guru/Tendik, Sarana Prasarana, Ekstrakurikuler, serta status halaman Struktur Organisasi.
- Menambahkan Penanggung Jawab Data dan Tanggal Tinjau Terakhir untuk mendukung validasi konten inti secara berkala.

### 1.24.0 — 2026-10-07

- Menambahkan berita dan Prestasi finalis National Speech Contest Bahasa Arab UNISMA atas nama Figur Bagus Fambagya dari sumber Instagram resmi.
- Melengkapi Prestasi Juara 3 English Olympiad Kabupaten Kediri atas nama Mikael Fadh tanpa menduplikasi berita yang sudah ada.
- Menambahkan video pembelajaran Design Brief DKV berbasis studi kasus Queen Brew dari kanal YouTube resmi ke Galeri.
- Menyertakan gambar/thumbnail sumber, tanggal album yang terverifikasi, dan importer non-destruktif yang aman dijalankan ulang.
- Mempertahankan lima Karya Siswa BSJC YouTube yang telah terbit tanpa membuat duplikat.

### 1.23.3 — 2026-10-07

- Menambahkan empat lowongan aktif terbaru dari sumber asli tanpa menghapus atau menimpa lowongan BKK yang telah ada.
- Menyertakan logo sumber, kualifikasi, tanggung jawab, lokasi, gaji bila tersedia, kanal lamaran, dan tanggal verifikasi untuk DKV, TJKT, MPLB, serta Layanan Kesehatan.
- Menampilkan status Pusat Aplikasi pada daftar admin dan panduan editor agar kartu aktif dapat dikelola tanpa menghapus entri.

### 1.22.1 — 2026-10-03

- Memperketat pencocokan singkatan dan prioritas konteks sehingga tema narasi tidak salah karena potongan kata atau frasa umum.
- Menjamin pembaruan aturan berikutnya selalu dibangun dari cadangan asli, bukan dari hasil standardisasi sebelumnya.

### 1.22.0 — 2026-10-03

- Menambahkan standardisasi narasi aman untuk empat koleksi editorial publik dengan pembuka per postingan, struktur subjudul per jenis konten, nilai pembelajaran berbasis topik, penutup, dan excerpt ringkas.
- Mempertahankan HTML, gambar, video, shortcode, embed, fakta, kredit, tanggal, serta tautan sumber lama di dalam narasi.
- Menambahkan cadangan asli satu kali, proses idempoten, kunci pemrosesan, laporan admin, pemeriksaan izin/nonce, serta pemulihan menyeluruh.

### 1.21.0 — 2026-09-17

- Menambahkan satu URL folder Google Drive khusus pada setiap akun Waka, Guru, atau Tendik tanpa menyimpan token maupun isi dokumen.
- Mempertahankan tiga folder divisi sebagai fallback agar konfigurasi lama tetap berjalan.
- Menambahkan status folder Pusat Media pada daftar Pengguna serta validasi URL HTTPS khusus host Google Drive resmi.
- Mendokumentasikan rekomendasi Shared Drive atau unggah langsung ke penyimpanan sekolah; penghapusan otomatis file sumber sengaja tidak diaktifkan.

### 1.20.0 — 2026-09-17

- Mengubah survei anonim menjadi evaluasi bertanggung jawab dengan nama dan kelas rinci yang hanya terlihat administrator.
- Memperluas empat aspek menjadi sepuluh aspek: kejelasan, pemahaman, relevansi, keterlibatan, umpan balik, keadilan, rasa aman, dukungan, motivasi, dan kedisiplinan.
- Mengganti jawaban kepuasan generik dengan pernyataan kontekstual yang dapat direnungkan siswa dari pengalaman nyata.
- Menambahkan refleksi kekuatan, kebutuhan perbaikan, pesan/harapan, sebaran kelas, tema, serta rekomendasi perbaikan otomatis.

### 1.19.0 — 2026-09-17

- Menambahkan enam lowongan kerja tanpa menimpa data BKK yang sudah ada.
- Menambahkan importer idempoten untuk logo perusahaan dan metadata sumber lowongan.
- Menampilkan lokasi, jenis kerja, pendidikan, gaji, tanggal verifikasi, kontak publik, sumber, serta tombol daftar pada halaman detail.

### 1.18.0 — 2026-09-16

- Menambahkan aplikasi Penilaian Kinerja Guru anonim untuk seluruh siswa dengan pilihan guru/mapel dari direktori sekolah, tingkat kelas, empat aspek skala 1–5, dan cerita evaluasi/motivasi opsional.
- Menyimpan respons tanpa nama, email, NIS, atau alamat IP; token acak lokal hanya digunakan untuk mencegah respons ganda kepada guru yang sama dalam satu semester.
- Menambahkan dashboard administrator berisi rata-rata per aspek, grafik, kategori kinerja, kesimpulan berbasis aturan, tema komentar, dan cerita siswa tanpa mengirim data ke layanan AI eksternal.
- Menambahkan kartu aktif Penilaian Kinerja Guru ke Pusat Aplikasi serta pengujian kontrak pertanyaan dan analisis.

### 1.17.0 — 2026-09-16

- Memasang foto sumber asli unggahan Instagram pada berita Kompetisi Pembawa Berita JTV Kediri ketika gambar unggulannya masih kosong.
- Menambahkan berita Peringatan Maulid Nabi Muhammad SAW 1448 H dari feed resmi 15 September 2026, lengkap dengan foto, kredit, tautan sumber, dan penjelasan tanggal unggahan.
- Mempertahankan konten, status, gambar, dan metadata yang sudah disunting administrator; item di Sampah tidak dipulihkan atau diduplikasi.

### 1.12.0 — 2026-09-05

- Memisahkan capability Pengumuman dari capability Post agar Waka dapat membuat, menyunting, dan menerbitkan `qaf_notice` tanpa memperoleh akses ke Berita/Halaman.
- Memberi Waka akses unggah Media Library dan penetapan jenis/audiens Pengumuman yang sudah ada, tanpa hak hapus, kelola pengguna, plugin, tema, atau pengaturan.
- Menjaga Guru, Tendik, serta role portal lama tetap front-end-only; Waka memakai dashboard Pengumuman terbatas.
- Menambahkan tool **Sekolah → Akun Waka** yang memproses empat nama dari struktur organisasi hanya setelah POST administrator dan nonce valid.
- Menggunakan username tetap, password sementara acak 32 karakter yang hanya tampil sekali, kewajiban mengganti password pertama, tanpa alamat email buatan, serta deteksi username/nama lama agar akun yang ada tidak ditimpa.
- Mempertahankan hak Pengumuman setara bagi role editorial bawaan setelah migrasi capability dan menambah pengujian provisioning idempoten serta batas izin.

### 1.11.0 — 2026-08-30

- Menambahkan 13 profil yang belum ada berdasarkan Struktur Kelembagaan 2026/2027: 5 guru/pimpinan dan 8 tendik/pembantu pelaksana.
- Menjaga 53 profil lama serta empat guru di luar daftar struktur tetap utuh tanpa perubahan judul, isi, status, meta, taksonomi, urutan, atau foto.
- Menambahkan pencocokan 11 variasi ejaan agar foto/profil lama kembali terhubung pada setiap penugasan di halaman Struktur Organisasi.
- Mengecualikan pendamping eksternal Sanggar Tari dari impor Guru & Tendik.
- Menambahkan dataset berversi, ID sumber readonly, pemeriksaan nama/alias/slug/Sampah, dan kunci pembaruan atomik agar proses idempoten.

### 1.10.0 — 2026-08-30

- Menambahkan 12 halaman Prestasi/Partisipasi dari Instagram resmi, YouTube resmi, situs sekolah, hasil OLPAIN, dan dokumen LKS tanpa mengubah 17 halaman lama.
- Memisahkan empat capaian DKV The Silent Heritage of District Paree menjadi halaman individual sesuai nama, bidang, dan peringkatnya.
- Menambahkan partisipasi Festival Anak Sholeh, LBB Kabupaten Kediri, PAIS Love Festival, OLPAIN, dan Semarak 17 Agustus dengan label faktual tanpa klaim juara.
- Menambahkan lima dokumentasi lokal; 24 dari 29 halaman kini memperoleh gambar sumber sementara halaman tanpa foto tepat tetap memakai fallback berlabel ilustrasi.
- Menggunakan alt text netral untuk poster yang dipakai bersama dan tidak mempublikasikan nilai maupun nomor ujian OLPAIN pada halaman sekolah.
- Menambahkan kunci pembaruan atomik, simulasi konkurensi/stale, dan pemeriksaan kontrak dataset untuk mencegah duplikasi serta menjaga item lama, Sampah, status, suntingan, dan gambar administrator.

### 1.9.1 — 2026-08-30

- Menambahkan sepuluh logo/foto profil yang bersumber dari kanal resmi mitra dan lima kartu identitas sementara untuk nama yang belum memiliki logo publik terverifikasi.
- Menambahkan importer logo Mitra DUDI berversi terpisah dengan kunci atomik antarsesi yang hanya mengisi featured image kosong, menggunakan satu attachment untuk entri canonical/legacy, serta tidak menyentuh item di Sampah.
- Menyimpan judul, caption, alt text, kredit, URL sumber, dan penanda berkas pada Media Library tanpa menimpa suntingan administrator; kegagalan metadata membatalkan attachment baru agar aman dicoba kembali.
- Menambahkan urutan arsip Mitra yang stabil berdasarkan urutan, judul, dan ID agar pagination tidak mengulang atau melewatkan kartu.
- Menambahkan manifest sumber, alat pembentuk aset yang dapat direproduksi, pemeriksaan kontrak 15 aset, dan pengujian importer untuk idempotensi, konkurensi, metadata, alias, retry, traversal, serta perlindungan gambar pilihan administrator.

### 1.9.0 — 2026-08-29

- Menambahkan halaman **Sekolah → Instagram Galeri** untuk koneksi API resmi tanpa menyimpan kata sandi Instagram.
- Menambahkan sinkronisasi manual dan harian khusus Reel/video dengan batas jumlah postingan, pagination berbasis cursor, status hasil, dan pesan kesalahan teredaksi.
- Menyimpan access token pada option privat/non-autoload, tidak pernah mencetak kembali token, serta mencoba memperbarui long-lived token yang masih valid secara berkala.
- Menghapus token/status Instagram dan jadwal sinkronisasi saat plugin dihapus, tanpa menghapus entri Galeri atau data institusional lain.
- Membuat entri Galeri secara idempoten berdasarkan ID media/permalink tanpa menimpa judul, isi, status, perilaku embed, gambar, atau item di Sampah.
- Menyalin thumbnail JPEG/PNG/WebP sampai 8 MB ke Media Library pada slot gambar kosong, dengan allowlist host CDN Meta/Instagram dan fallback placeholder/tautan.
- Menambahkan ID sinkronisasi readonly, panduan koneksi, dan pemeriksaan kontrak URL/video yang dapat dijalankan tanpa dependency eksternal.

### 1.8.0 — 2026-08-27

- Menambahkan delapan sampul berita Agustus 2026 sebagai aset lokal dari Instagram resmi sekolah.
- Menambahkan importer media berita non-destruktif untuk featured image, alt text, caption, kredit, dan URL sumber.
- Menambahkan meta terstruktur sumber Instagram dan kredit gambar pada editor Berita.
- Menambahkan pengaturan opsional Google Analytics 4 dan Search Console dengan validasi format.

### 1.7.1 — 2026-08-02

- Memperbarui alamat publik BeAD Group ke lokasi yang tercantum pada situs resminya.
- Mengganti sumber Puskesmas Mojo dengan daftar registrasi resmi Kementerian Kesehatan yang mengonfirmasi alamat dan status rawat inap.
- Memperjelas profil JTV Kediri agar kompetensi DKV diposisikan sebagai relevansi pembelajaran, bukan klaim layanan komersial.
- Menambahkan migrasi koreksi exact-match untuk entri mitra bawaan; nilai yang sudah disunting administrator tetap dipertahankan.

### 1.7.0 — 2026-07-30

- Menambahkan sumber Galeri Lokal, Instagram, TikTok, Facebook, dan YouTube serta pilihan jenis Foto, Video, atau gabungan.
- Menambahkan pemilih attachment video Media Library dengan pemeriksaan MIME dan schema REST integer.
- Membatasi URL sosial ke HTTPS pada allowlist host resmi dan menambahkan validasi ringan pada editor.
- Menambahkan perilaku **Klik untuk memuat** (default), **Muat otomatis**, dan **Tautan saja** untuk renderer tema Queen Al-Falah 1.4.0.
- Menambahkan panduan editor, kolom admin Sumber/Jenis Media/Tanggal Album/URL Media, dan filter arsip `?sumber=`.
- Mempertahankan mode otomatis untuk entri lama, termasuk sumber kosong dan `_qaf_video_url`, tanpa menghapus URL legacy hanya karena post disimpan.
- Mendokumentasikan batas izin, privasi embed, larangan iframe/token, dan tidak adanya sinkronisasi feed otomatis.

### 1.6.0 — 2026-07-29

- Menambahkan katalog Sarana Prasarana dengan enam entri awal dan field pengelolaan yang lebih lengkap.
- Menambahkan 14 profil awal PKL & Mitra Industri dengan keahlian, program terkait, bentuk kerja sama, sumber, serta status verifikasi.
- Menyederhanakan Pusat Media menjadi tautan Google Drive per divisi yang wajib login dan dapat diganti dari Pengaturan Sekolah.
- Menghapus kebutuhan OAuth/API serta alur unggah-unduh file di dalam WordPress; izin berbagi Drive menjadi batas keamanan dokumen.
- Menyertakan 16 foto sumber terverifikasi untuk 17 Prestasi dan mengisinya hanya pada slot gambar unggulan yang masih kosong; foto LBB putri menunggu dokumentasi yang tepat.
- Menambahkan 11 entri ekstrakurikuler dengan manfaat, relevansi dunia kerja, dan ilustrasi fallback.

### 1.5.0 — 2026-07-23

- Menambahkan metadata serta kolom admin prestasi untuk penyelenggara, juara/penghargaan, bidang, tingkat, penerima, dan sumber.
- Menambahkan berkas data prestasi berversi.
- Menambahkan importer idempoten yang hanya membuat ID sumber yang belum ada, termasuk pemeriksaan terhadap entri di Sampah.
- Mempertahankan semua suntingan dan status konten yang sudah ada.

### 1.4.0 — 2026-07-23

- Menambahkan Struktur Organisasi 2026/2027 berisi 19 bidang/tim dan seluruh penugasan.
- Menambahkan penjelasan tupoksi per jabatan.
- Menambahkan pencocokan foto dan tautan profil otomatis dari Guru & Tendik.
- Menambahkan migrasi aman dari konten placeholder serta tampilan responsif khusus.

### 1.3.0 — 2026-07-23

- Mengganti role portal menjadi Waka, Guru, dan Tenaga Kependidikan.
- Menambahkan provisioning folder Drive otomatis per username.
- Menambahkan unggahan terotorisasi dengan validasi MIME, ukuran, nonce, capability, dan batas folder.
- Menambahkan autentikasi OAuth untuk My Drive serta dukungan service account untuk Shared Drive.

### 1.2.0 — 2026-07-18

- Menambahkan Pusat Media privat berbasis akun WordPress.
- Menambahkan tiga peran sekolah dan pemetaan folder Drive per pengguna.
- Menambahkan klien Google Drive read-only berbasis service account dan proxy unduhan terotorisasi.

### 1.1.0 — 2026-07-13

- Menambahkan Pusat Aplikasi satu pintu untuk Ujian, E-Rapor, E-Perpustakaan, SPMB, dan Gamifikasi Edu.
- Menambahkan status aplikasi dan alamat publik `/aplikasi/`.

### 1.0.1 — 2026-07-13

- Menambahkan pengaturan nama, jabatan, dan pesan kepala sekolah melalui menu Pengaturan Sekolah.

### 1.0.0 — 2026-07-13

- Rilis awal model konten, meta, taksonomi, pengaturan, admin list, filter arsip, dan importer idempoten.
- Menambahkan dokumentasi keamanan, privasi, serta kebijakan retensi data.
