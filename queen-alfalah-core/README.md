# Queen Al-Falah Core

Companion plugin resmi untuk tema **Queen Al-Falah**. Plugin memisahkan model konten dan data sekolah dari lapisan tampilan, sehingga program keahlian, pengumuman, agenda, dan data kelembagaan tetap tersedia ketika tema diganti.

Versi: **1.9.0**
WordPress minimum: **6.2**  
PHP minimum: **7.4**  
Lisensi: **GPL-2.0-or-later**

## Fitur

- 12 custom post type (CPT) sekolah dan 14 taksonomi publik.
- Meta terstruktur yang terdaftar pada Metadata API dan REST API.
- Meta box klasik/Gutenberg dengan sanitasi per tipe, nonce, dan pemeriksaan kemampuan pengguna.
- Pengaturan identitas, visi-misi, kontak, lokasi, pendaftaran, dan media sosial.
- Kolom admin dan pengurutan data terstruktur.
- Arsip agenda terurut berdasarkan tanggal mulai.
- Arsip pengumuman publik menyembunyikan item kedaluwarsa tanpa mengubah daftar admin.
- Importer demo satu klik yang aman, idempoten, dan tidak menimpa konten pengguna.
- Portal Pusat Media privat dengan peran Waka Sekolah, Guru, dan Tenaga Kependidikan.
- Tautan Google Drive terpisah untuk Waka, Guru, dan Tendik yang dapat diganti dari Pengaturan Sekolah.
- Struktur organisasi tahun pelajaran 2026/2027 dengan tupoksi per jabatan dan foto dari direktori Guru & Tendik.
- Metadata prestasi lengkap untuk penyelenggara, juara/penghargaan, bidang, tingkat, dan tautan sumber.
- Importer prestasi berversi yang idempoten serta tidak menimpa suntingan administrator.
- Tujuh belas halaman prestasi siswa dari unggahan Instagram resmi tahun 2023–2026 beserta 16 foto sumber terverifikasi yang disinkronkan ke slot gambar unggulan kosong. Foto khusus regu LBB putri menunggu dokumentasi sekolah yang tepat.
- Enam data awal Sarana Prasarana dengan field lokasi, fungsi, fitur, akses, pengelola, kapasitas, kondisi, dan tanggal pemeriksaan.
- Empat belas profil awal PKL & Mitra Industri dengan sumber dan status verifikasi yang dapat ditinjau administrator.
- Sebelas data ekstrakurikuler dengan manfaat, relevansi dunia kerja, informasi keikutsertaan, dan ilustrasi fallback.
- Galeri lokal dan konten sosial Instagram, TikTok, Facebook, atau YouTube dengan jenis media, pemilih video, perilaku embed, dan filter sumber.
- Sinkronisasi Reel/video akun Instagram Profesional melalui API resmi, dengan jadwal harian opsional, deduplikasi ID media, status draf/terbit, poster lokal, status koneksi, dan pembaruan long-lived token.
- Delapan sampul berita Agustus 2026 dari akun Instagram resmi yang disalin ke Media Library tanpa hotlink CDN sementara.
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
- Guru/tendik: `_qaf_role`, `_qaf_subject`, `_qaf_order`.
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

Importer Prestasi menangani 17 halaman berdasarkan ID sumber Instagram resmi. Enam belas foto lokal yang terverifikasi dimasukkan ke Media Library dan dijadikan gambar unggulan hanya ketika post tersebut belum memiliki gambar unggulan. Unggahan LBB Kecamatan Mojo menyebut prestasi putra dan putri, tetapi poster yang tersedia hanya menampilkan regu putra; karena itu halaman putri sengaja tidak diberi poster putra. Gambar yang sudah dipilih administrator, status post, dan suntingan teks tetap dipertahankan.

Importer Media Berita menangani delapan artikel Agustus 2026 berdasarkan slug ekspor dan ID unggahan Instagram resmi. Slide pertama disimpan sebagai aset lokal, lalu dimasukkan ke Media Library bersama caption, alt text, kredit, serta URL sumber. Importer hanya mengisi slot gambar unggulan kosong; gambar dan metadata yang sudah disunting administrator tidak diganti. Gunakan tema Queen Al-Falah 1.5.0 agar caption dan kredit tampil di halaman detail.

## Penggunaan Galeri

Setiap entri `qaf_gallery` dapat memakai media lokal atau satu URL konten sosial. Gambar Utama tetap menjadi sampul kartu/arsip.

### Media lokal

1. Pilih **Sumber Media: Lokal / Media Library**.
2. Pilih **Jenis Media: Foto, Video, atau Foto dan Video**.
3. Susun beberapa foto/video dengan blok **Image**, **Gallery**, dan **Video** pada editor konten.
4. Bila hanya membutuhkan satu video, gunakan field **Video Lokal** untuk memilih attachment video dari Media Library.

Pemilih hanya menerima attachment dengan MIME `video/*`; nilai nol atau pilihan yang tidak valid tidak disimpan. Plugin tidak menambah capability baru: pengguna tetap harus memiliki izin WordPress untuk menyunting post terkait, dan unggahan media mengikuti izin `upload_files`. Pastikan sekolah memiliki hak penggunaan serta persetujuan publikasi untuk setiap foto/video.

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
- Akun khusus portal diarahkan ke Pusat Media dan dibatasi dari halaman administrasi WordPress.
- Pengguna portal hanya menerima tautan Drive yang sesuai dengan divisi pada peran WordPress-nya; administrator dapat melihat semua tautan untuk pemeriksaan.
- Pusat Media tidak memproses unggahan, unduhan, atau isi file. Keamanan dokumen ditentukan oleh aturan berbagi Google Drive.
- Jangan memasukkan NIK, NISN, alamat rumah, nomor pribadi, data kesehatan, atau foto tanpa dasar izin yang sesuai.

## Konfigurasi Pusat Media dan Google Drive

Pusat Media memakai tautan langsung dan tidak memerlukan Google Drive API, OAuth, service account, client secret, refresh token, maupun JSON key.

1. Siapkan satu folder Drive untuk Waka, satu untuk Guru, dan satu untuk Tendik.
2. Di Google Drive, pilih berbagi **Dibatasi** lalu tambahkan hanya akun Google yang berwenang. Tetapkan Viewer, Commenter, atau Editor sesuai kebutuhan.
3. Buka **Sekolah → Pengaturan → Pusat Media** dan isi ketiga URL folder. Hanya URL HTTPS pada `drive.google.com` atau `docs.google.com` yang diterima.
4. Buat akun WordPress personel melalui **Pengguna → Tambah Baru**, pilih peran Waka Sekolah, Guru, atau Tenaga Kependidikan, lalu isi Unit/Jabatan.
5. Uji login melalui `/pusat-media/`. Pengguna non-administrator hanya boleh melihat satu tautan sesuai divisinya.

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

Tidak ada akun atau password contoh yang dibuat otomatis. Administrator harus membuat username unik dan password kuat untuk setiap personel agar kredensial tidak dipakai bersama.

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

Periksa peran akun, lalu isi URL divisi terkait pada **Sekolah → Pengaturan → Pusat Media**. Pastikan URL memakai HTTPS dan domain `drive.google.com` atau `docs.google.com`.

**Pengguna dapat/tidak dapat membuka file setelah masuk**

Periksa anggota serta level izin pada dialog berbagi folder Google Drive. Login WordPress hanya mengatur tautan yang terlihat dan tidak menggantikan izin Google Drive.

## Pengembangan

Tidak ada dependency build atau library eksternal. Jalankan pemeriksaan sintaks untuk semua berkas PHP sebelum rilis:

```sh
find queen-alfalah-core -name '*.php' -exec php -l {} \;
```

## Changelog

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
