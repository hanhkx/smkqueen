<?php
/** Student work catalogue setup; never creates people or publishes content automatically. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class QAF_Core_Student_Works {
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'upgrade' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_qaf_import_student_works', array( __CLASS__, 'import' ) );
		add_action( 'admin_post_qaf_sync_student_work_images', array( __CLASS__, 'sync_images' ) );
	}
	public static function items() {
		return array(
			'DXbMBD9ibLT' => array( 'Kreasi Video Promosi SPMB 2026', 'video-pendek', 'Dokumentasi kreasi video yang menampilkan siswa membawakan ajakan mengenal SMK Queen Al-Falah di lingkungan sekolah. Kegiatan ini memperlihatkan latihan komunikasi dan penyampaian pesan melalui media video. Informasi pendaftaran dalam video merupakan arsip, bukan pengumuman pendaftaran terkini.' ),
			'DX9Nr4cp3_I' => array( 'Kreasi Video Bahasa Inggris dan Bisnis', 'video-pendek', 'Siswa SMK Queen Al-Falah menyampaikan pesan bertema bahasa Inggris dan karakter bisnis melalui tampilan kreatif. Dokumentasi ini menjadi bagian dari ruang apresiasi komunikasi, keberanian tampil, dan eksplorasi konten siswa.' ),
			'DZYw36_prkM' => array( 'Ensiklopedia Bertanam: Proyek Belajar Siswa', 'video-pendek', 'Dokumentasi proyek bertanam memperlihatkan interaksi siswa dan guru, pertanyaan tentang media tanam, persiapan wadah, serta praktik semi hidroponik. Melalui dokumentasi ini, pengunjung dapat melihat proses belajar dan pengamatan siswa SMK Queen Al-Falah.' ),
			'DZZ13ljpY8p' => array( 'Di Balik Layar Konten Bertanam', 'portofolio', 'Portofolio proses ini menampilkan persiapan alat, interaksi siswa dan guru, serta kegiatan pengambilan gambar untuk konten bertanam. Proses di balik layar memperlihatkan bahwa penyajian konten membutuhkan persiapan dan kerja bersama.' ),
			'DZbb71zJM3q' => array( 'Karya Kreatif dan Perawatan Kelas Putri', 'portofolio', 'Dokumentasi kegiatan kokurikuler putri SMK Queen Al-Falah meliputi pengarahan, perawatan kelas, dan pembuatan karya dari bahan sederhana. Portofolio ini mengabadikan proses kreatif sekaligus kepedulian siswa terhadap lingkungan belajar.' ),
			'DZew43CJmPV' => array( 'Kreasi Dekorasi dan Penataan Kelas Putri', 'portofolio', 'Siswa putri SMK Queen Al-Falah menyusun karya dan merangkai hiasan untuk penataan kelas. Dokumentasi juga menampilkan peninjauan kelas oleh tim penilai. Tidak ada peringkat atau juara yang ditetapkan dalam keterangan katalog ini.' ),
			'DZhooa-pagY' => array( 'Penataan Kelas Putra: Afirmasi dan Budaya Indonesia', 'portofolio', 'Portofolio penataan kelas putra memperlihatkan pengecatan, perapian, serta dekorasi bertema afirmasi dan budaya Indonesia. Dokumentasi ini menyajikan proses kerja dan hasil kreativitas siswa dalam membangun suasana belajar.' ),
		);
	}
	/**
	 * Source-owned covers for the second curated student-work batch.
	 *
	 * Instagram cover frames are bundled because their CDN URLs expire. YouTube
	 * thumbnails use stable official URLs and are copied into Media Library when
	 * the administrator starts the sync. Existing featured images always win.
	 *
	 * @return array<int,array<string,string>>
	 */
	public static function image_items() {
		return array(
			array( 'title' => 'Iklan Parfume Channel — UKK Amalia Fauziah Rahma', 'source' => 'https://www.instagram.com/cmnggx_/reel/DW-osPqCsNT/', 'file' => 'amalia-parfume-channel.jpg', 'alt' => 'Cuplikan iklan Parfume Channel karya Amalia Fauziah Rahma', 'caption' => 'Cuplikan dari unggahan Instagram sumber karya Amalia Fauziah Rahma.' ),
			array( 'title' => 'Video Ujian Kompetensi DKV — Faza Khilyatul Maghfiroh', 'source' => 'https://www.instagram.com/xfilyaaasp_/reel/DW-hvMfle_f/', 'file' => 'faza-ujian-kompetensi-dkv.jpg', 'alt' => 'Cuplikan video Ujian Kompetensi DKV karya Faza Khilyatul Maghfiroh', 'caption' => 'Cuplikan dari unggahan Instagram sumber karya Faza Khilyatul Maghfiroh.' ),
			array( 'title' => 'Iklan Cimory — Portofolio Wildan Fawaz An-Naqy', 'source' => 'https://www.instagram.com/mas_wil7122/reel/DW5Z5BJAH2j/', 'file' => 'wildan-iklan-cimory.jpg', 'alt' => 'Cuplikan iklan Cimory karya Wildan Fawaz An-Naqy', 'caption' => 'Cuplikan dari unggahan Instagram sumber karya Wildan Fawaz An-Naqy.' ),
			array( 'title' => 'Portofolio Video DKV — Nauval Agil Paromi', 'source' => 'https://www.instagram.com/n.a.p_gil/reel/DWxvy09CilS/', 'file' => 'nauval-portofolio-video-dkv.jpg', 'alt' => 'Cuplikan portofolio video DKV karya Nauval Agil Paromi', 'caption' => 'Cuplikan dari unggahan Instagram sumber karya Nauval Agil Paromi.' ),
			array( 'title' => 'Iklan Joyday Ice Cream — Portofolio Atoya Danis', 'source' => 'https://www.instagram.com/ag4m16/reel/DUAFpezjMUd/', 'file' => 'atoya-iklan-joyday.jpg', 'alt' => 'Cuplikan iklan Joyday Ice Cream karya Atoya Danis', 'caption' => 'Cuplikan dari unggahan Instagram sumber karya Atoya Danis.' ),
			array( 'title' => 'Iklan Kratingdaeng Red Bull — Portofolio Nauval Agil Paromi', 'source' => 'https://www.instagram.com/n.a.p_gil/reel/DUAFZ64Cs0q/', 'file' => 'nauval-iklan-kratingdaeng.jpg', 'alt' => 'Cuplikan iklan Kratingdaeng karya Nauval Agil Paromi', 'caption' => 'Cuplikan dari unggahan Instagram sumber karya Nauval Agil Paromi.' ),
			array( 'title' => 'Edu Vlog BSJC3 — Muhammad Wildan Eka P.', 'source' => 'https://www.youtube.com/watch?v=u_LzvRDOW5s', 'remote' => 'https://i.ytimg.com/vi/u_LzvRDOW5s/hqdefault.jpg', 'alt' => 'Thumbnail Edu Vlog BSJC3 Muhammad Wildan Eka P.', 'caption' => 'Thumbnail video dari kanal YouTube resmi SMK Queen Al-Falah.' ),
			array( 'title' => 'Storytelling BSJC3 — Zidan Wildan Al Firdaus', 'source' => 'https://www.youtube.com/watch?v=QWhqX1VBDMM', 'remote' => 'https://i.ytimg.com/vi/QWhqX1VBDMM/hqdefault.jpg', 'alt' => 'Thumbnail Storytelling BSJC3 Zidan Wildan Al Firdaus', 'caption' => 'Thumbnail video dari kanal YouTube resmi SMK Queen Al-Falah.' ),
			array( 'title' => 'News Anchor BSJC3 — Shanum Aila Meka', 'source' => 'https://www.youtube.com/watch?v=1i_AI7wJuho', 'remote' => 'https://i.ytimg.com/vi/1i_AI7wJuho/hqdefault.jpg', 'alt' => 'Thumbnail News Anchor BSJC3 Shanum Aila Meka', 'caption' => 'Thumbnail video dari kanal YouTube resmi SMK Queen Al-Falah.' ),
			array( 'title' => 'English Speech BSJC3 — Thalita Zakia Ahmad', 'source' => 'https://www.youtube.com/watch?v=K-TUEWeiRIU', 'remote' => 'https://i.ytimg.com/vi/K-TUEWeiRIU/hqdefault.jpg', 'alt' => 'Thumbnail English Speech BSJC3 Thalita Zakia Ahmad', 'caption' => 'Thumbnail video dari kanal YouTube resmi SMK Queen Al-Falah.' ),
			array( 'title' => 'English Speech BSJC3 — Muhammad Affan Fachri', 'source' => 'https://www.youtube.com/watch?v=CyTTIMUShmo', 'remote' => 'https://i.ytimg.com/vi/CyTTIMUShmo/hqdefault.jpg', 'alt' => 'Thumbnail English Speech BSJC3 Muhammad Affan Fachri', 'caption' => 'Thumbnail video dari kanal YouTube resmi SMK Queen Al-Falah.' ),
			array( 'title' => 'Pendidikan di Ruang Lapar — Short Movie VTSM', 'source' => 'https://www.youtube.com/watch?v=O9OF6vn6yc4', 'remote' => 'https://i.ytimg.com/vi/O9OF6vn6yc4/maxresdefault.jpg', 'alt' => 'Thumbnail film pendek Pendidikan di Ruang Lapar', 'caption' => 'Thumbnail video dari kanal YouTube resmi SMK Queen Al-Falah.' ),
			array( 'title' => '404 Protocol — Juara 1 Video Edukasi Commday 2025', 'source' => 'https://www.youtube.com/watch?v=FB1ids0UJko', 'remote' => 'https://i.ytimg.com/vi/FB1ids0UJko/maxresdefault.jpg', 'alt' => 'Thumbnail video edukasi 404 Protocol', 'caption' => 'Thumbnail video dari kanal YouTube resmi SMK Queen Al-Falah.' ),
			array( 'title' => 'Public Speaking Tingkat Nasional — Zahra Faiqotun Nashir', 'source' => 'https://www.youtube.com/watch?v=uNw1oEzT-BI', 'remote' => 'https://i.ytimg.com/vi/uNw1oEzT-BI/maxresdefault.jpg', 'alt' => 'Thumbnail Public Speaking Zahra Faiqotun Nashir', 'caption' => 'Thumbnail video dari kanal YouTube resmi SMK Queen Al-Falah.' ),
			array( 'title' => 'Public Speaking Tingkat Nasional — Salma Faiqotun Nisa', 'source' => 'https://www.youtube.com/watch?v=1GBRY4BU2dg', 'remote' => 'https://i.ytimg.com/vi/1GBRY4BU2dg/maxresdefault.jpg', 'alt' => 'Thumbnail Public Speaking Salma Faiqotun Nisa', 'caption' => 'Thumbnail video dari kanal YouTube resmi SMK Queen Al-Falah.' ),
			array( 'title' => 'Kembali — Film Pendek Dies Natalis Bhakta 2025', 'source' => 'https://www.youtube.com/watch?v=Cq50Ejxizu8', 'remote' => 'https://i.ytimg.com/vi/Cq50Ejxizu8/hqdefault.jpg', 'alt' => 'Thumbnail film pendek Kembali', 'caption' => 'Thumbnail video dari kanal YouTube resmi SMK Queen Al-Falah.' ),
			array( 'title' => 'Goresan Tinta — Juara 2 Feskom14 KPI IAIN Kediri', 'source' => 'https://www.youtube.com/watch?v=mjbCzJQ5TUI', 'remote' => 'https://i.ytimg.com/vi/mjbCzJQ5TUI/maxresdefault.jpg', 'alt' => 'Thumbnail film pendek Goresan Tinta', 'caption' => 'Thumbnail video dari kanal YouTube resmi SMK Queen Al-Falah.' ),
			array( 'title' => 'Profil SMK Queen Al-Falah — Video Edukasi Karya Siswa', 'source' => 'https://www.youtube.com/watch?v=dP3do-bprgs', 'remote' => 'https://i.ytimg.com/vi/dP3do-bprgs/hqdefault.jpg', 'alt' => 'Thumbnail video profil SMK Queen Al-Falah karya siswa', 'caption' => 'Thumbnail video dari kanal YouTube resmi SMK Queen Al-Falah.' ),
		);
	}
	public static function menu() {
		add_submenu_page( 'qaf-school', 'Penyiapan Karya Siswa', 'Penyiapan Karya Siswa', 'manage_options', 'qaf-student-works', array( __CLASS__, 'render' ) );
	}
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		echo '<div class="wrap"><h1>Penyiapan Karya Siswa</h1><p>Tambahkan tujuh dokumentasi karya/proyek dari galeri resmi yang telah terbit. Video dan sampul digunakan kembali tanpa unggah ulang. Entri yang sudah ada, termasuk Sampah, tidak ditimpa. Nama pembuat dan tanggal yang belum terkonfirmasi tetap kosong.</p><ul>';
		foreach ( self::items() as $item ) { echo '<li>' . esc_html( $item[0] ) . '</li>'; }
		echo '</ul><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="qaf_import_student_works">';
		wp_nonce_field( 'qaf_import_student_works' );
		submit_button( 'Tambahkan dan Terbitkan Karya yang Lengkap' );
		echo '</form>';
		foreach ( (array) get_option( 'qaf_student_works_report', array() ) as $line ) { echo '<p>' . esc_html( $line ) . '</p>'; }
		echo '<hr><h2>Gambar sumber Karya Siswa</h2><p>Pasang cover frame asli Instagram dan thumbnail video dari kanal YouTube resmi pada 18 karya Batch 2. Gambar disalin ke Media Library; pilihan gambar unggulan yang sudah ada tidak ditimpa.</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="qaf_sync_student_work_images">';
		wp_nonce_field( 'qaf_sync_student_work_images' );
		submit_button( 'Pasang 18 gambar dari sumber', 'primary', 'submit', false );
		echo '</form>';
		foreach ( (array) get_option( 'qaf_student_work_image_report', array() ) as $line ) { echo '<p>' . esc_html( $line ) . '</p>'; }
		echo '</div>';
	}
	/**
	 * Copy source-owned covers to Media Library and fill empty featured images.
	 *
	 * @return void
	 */
	public static function sync_images() {
		if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_others_qaf_works' ) ) { wp_die( 'Akses ditolak.', '', array( 'response' => 403 ) ); }
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) { wp_die( 'POST diperlukan.', '', array( 'response' => 405 ) ); }
		check_admin_referer( 'qaf_sync_student_work_images' );
		if ( ! add_option( 'qaf_student_work_image_lock', time(), '', false ) ) { wp_die( 'Sinkronisasi gambar sebelumnya masih terkunci; periksa hasil sebelum mengulang.' ); }

		$report = array();
		try {
			foreach ( self::image_items() as $item ) {
				$post_id = self::find_work_by_source( $item['source'] );
				if ( ! $post_id ) { $report[] = $item['title'] . ': halaman karya sumber tidak ditemukan.'; continue; }
				if ( has_post_thumbnail( $post_id ) ) { $report[] = $item['title'] . ': dilewati; gambar unggulan sudah ada.'; continue; }
				$attachment_id = self::get_or_import_work_image( $item, $post_id );
				if ( is_wp_error( $attachment_id ) ) { $report[] = $item['title'] . ': ' . $attachment_id->get_error_message(); continue; }
				if ( ! set_post_thumbnail( $post_id, $attachment_id ) ) { $report[] = $item['title'] . ': gambar berhasil disalin tetapi gagal ditetapkan.'; continue; }
				$report[] = $item['title'] . ': gambar sumber terpasang.';
			}
		} finally {
			delete_option( 'qaf_student_work_image_lock' );
		}

		update_option( 'qaf_student_work_image_report', $report, false );
		wp_safe_redirect( admin_url( 'admin.php?page=qaf-student-works' ) );
		exit;
	}
	/**
	 * Find the one work carrying the exact curated source URL.
	 *
	 * @param string $source Source URL.
	 * @return int
	 */
	private static function find_work_by_source( $source ) {
		$matches = get_posts( array( 'post_type' => 'qaf_work', 'post_status' => array_values( get_post_stati() ), 'posts_per_page' => 2, 'fields' => 'ids', 'no_found_rows' => true, 'meta_key' => '_qaf_work_source_url', 'meta_value' => esc_url_raw( $source ) ) );
		return 1 === count( $matches ) ? (int) $matches[0] : 0;
	}
	/**
	 * Reuse or import one bundled/remote source image.
	 *
	 * @param array<string,string> $item    Image definition.
	 * @param int                  $post_id Parent work.
	 * @return int|WP_Error
	 */
	private static function get_or_import_work_image( $item, $post_id ) {
		$matches = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => true, 'meta_key' => '_qaf_work_image_source', 'meta_value' => esc_url_raw( $item['source'] ) ) );
		if ( $matches ) { return (int) $matches[0]; }

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		$temp_file = '';
		$file_name = '';
		if ( ! empty( $item['file'] ) ) {
			$file_name = sanitize_file_name( $item['file'] );
			$base_dir = realpath( QAF_CORE_PATH . 'assets/images/student-works' );
			$file = $base_dir ? realpath( $base_dir . DIRECTORY_SEPARATOR . $file_name ) : false;
			$base = $base_dir ? rtrim( wp_normalize_path( $base_dir ), '/' ) . '/' : '';
			if ( false === $file || 0 !== strpos( wp_normalize_path( $file ), $base ) || ! is_file( $file ) || ! is_readable( $file ) ) { return new WP_Error( 'qaf_work_image_missing', 'Berkas gambar Instagram tidak tersedia.' ); }
			$temp_file = wp_tempnam( $file_name );
			if ( ! $temp_file || ! copy( $file, $temp_file ) ) { return new WP_Error( 'qaf_work_image_copy_failed', 'Gambar Instagram tidak dapat disalin.' ); }
		} else {
			$remote = esc_url_raw( isset( $item['remote'] ) ? $item['remote'] : '' );
			$host = strtolower( (string) wp_parse_url( $remote, PHP_URL_HOST ) );
			if ( 'i.ytimg.com' !== $host ) { return new WP_Error( 'qaf_work_image_remote_invalid', 'Alamat thumbnail YouTube tidak valid.' ); }
			$file_name = sanitize_file_name( sanitize_title( $item['title'] ) . '.jpg' );
			$temp_file = download_url( $remote, 30 );
			if ( is_wp_error( $temp_file ) ) { return $temp_file; }
		}

		$attachment_id = media_handle_sideload( array( 'name' => $file_name, 'tmp_name' => $temp_file ), $post_id, $item['title'] );
		if ( is_wp_error( $attachment_id ) ) {
			if ( $temp_file && file_exists( $temp_file ) ) { wp_delete_file( $temp_file ); }
			return $attachment_id;
		}
		update_post_meta( $attachment_id, '_qaf_work_image_source', esc_url_raw( $item['source'] ) );
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $item['alt'] ) );
		wp_update_post( array( 'ID' => $attachment_id, 'post_excerpt' => sanitize_text_field( $item['caption'] ) ) );
		return (int) $attachment_id;
	}
	public static function import() {
		if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'publish_qaf_works' ) ) { wp_die( 'Akses ditolak.', '', array( 'response' => 403 ) ); }
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) { wp_die( 'POST diperlukan.', '', array( 'response' => 405 ) ); }
		check_admin_referer( 'qaf_import_student_works' );
		if ( ! add_option( 'qaf_student_works_lock', time(), '', false ) ) { wp_die( 'Proses sebelumnya masih terkunci; periksa hasilnya sebelum mengulang.' ); }
		$report = array();
		try {
			foreach ( self::items() as $code => $item ) {
				$existing = get_posts( array( 'post_type' => 'qaf_work', 'post_status' => array_values( get_post_stati() ), 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_qaf_work_import_source', 'meta_value' => $code ) );
				if ( $existing ) { $report[] = $item[0] . ': dilewati; entri sudah ada #' . $existing[0]; continue; }
				$galleries = get_posts( array( 'post_type' => 'qaf_gallery', 'post_status' => 'publish', 'posts_per_page' => 2, 'fields' => 'ids', 'meta_key' => '_qaf_manual_reel_shortcode', 'meta_value' => $code ) );
				if ( 1 !== count( $galleries ) ) { $report[] = $item[0] . ': ditahan; galeri sumber tidak unik atau belum terbit.'; continue; }
				$gallery = (int) $galleries[0];
				$video = (int) get_post_meta( $gallery, '_qaf_gallery_local_video_id', true );
				$poster = (int) get_post_thumbnail_id( $gallery );
				$term = get_term_by( 'slug', $item[1], 'qaf_work_type' );
				$url = wp_get_attachment_url( $video );
				if ( ! $term || ! $url || ! $poster || ! wp_attachment_is_image( $poster ) || 0 !== strpos( (string) get_post_mime_type( $video ), 'video/' ) ) { $report[] = $item[0] . ': ditahan; kategori/video/sampul belum lengkap.'; continue; }
				$source = 'https://www.instagram.com/reel/' . $code . '/';
				$content = '<p>' . esc_html( $item[2] ) . '</p>';
				$content .= "\n" . '[video src="' . esc_url( $url ) . '" preload="metadata"][/video]' . "\n";
				$content .= '<p>Dokumentasi karya/proyek siswa dari akun resmi SMK Queen Al-Falah. Kredit pembuat individual dan tanggal karya belum terkonfirmasi; katalog ini tidak menetapkan siapa penulis, editor, atau sutradara video.</p><p><a href="' . esc_url( get_permalink( $gallery ) ) . '">Lihat dokumentasi di galeri kegiatan</a> · <a href="' . esc_url( $source ) . '">Unggahan sumber Instagram</a></p>';
				$id = wp_insert_post( wp_slash( array( 'post_type' => 'qaf_work', 'post_status' => 'draft', 'post_author' => get_current_user_id(), 'post_title' => $item[0], 'post_content' => $content, 'post_excerpt' => $item[2], 'meta_input' => array( '_qaf_work_import_source' => $code, '_qaf_work_source_url' => $source ) ) ), true );
				if ( is_wp_error( $id ) ) { $report[] = $item[0] . ': gagal menyimpan.'; continue; }
				$terms = wp_set_object_terms( $id, array( (int) $term->term_id ), 'qaf_work_type' );
				set_post_thumbnail( $id, $poster );
				if ( is_wp_error( $terms ) || $poster !== (int) get_post_thumbnail_id( $id ) || $code !== get_post_meta( $id, '_qaf_work_import_source', true ) ) { $report[] = $item[0] . ': draf #' . $id . ', perlu pemeriksaan.'; continue; }
				$result = wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ), true );
				$report[] = $item[0] . ( is_wp_error( $result ) || 'publish' !== get_post_status( $id ) ? ': belum terbit #' : ': terbit #' ) . $id;
			}
		} finally { delete_option( 'qaf_student_works_lock' ); }
		update_option( 'qaf_student_works_report', $report, false );
		wp_safe_redirect( admin_url( 'admin.php?page=qaf-student-works' ) );
		exit;
	}
	public static function upgrade() {
		if ( ! current_user_can( 'manage_options' ) || '2' === get_option( 'qaf_student_works_schema' ) ) { return; }
		$role = get_role( 'administrator' );
		$type = get_post_type_object( 'qaf_work' );
		if ( ! $role || ! $type || ! taxonomy_exists( 'qaf_work_type' ) ) { return; }
		foreach ( (array) $type->cap as $cap ) {
			if ( ! in_array( $cap, array( 'edit_qaf_work', 'read_qaf_work', 'delete_qaf_work' ), true ) ) { $role->add_cap( $cap ); }
		}
		foreach ( array( 'manage', 'edit', 'delete', 'assign' ) as $verb ) { $role->add_cap( $verb . '_qaf_work_terms' ); }
		foreach ( array( 'esai' => 'Esai', 'artikel' => 'Artikel', 'video-pendek' => 'Video Pendek / Film', 'portofolio' => 'Portofolio' ) as $slug => $name ) {
			if ( ! term_exists( $slug, 'qaf_work_type' ) ) {
				$result = wp_insert_term( $name, 'qaf_work_type', array( 'slug' => $slug ) );
				if ( is_wp_error( $result ) ) { return; }
			}
		}
		self::install_primary_menu_item();
		flush_rewrite_rules( false );
		update_option( 'qaf_student_works_schema', '2', false );
	}
	private static function install_primary_menu_item() {
		$locations = get_nav_menu_locations();
		$menu_id = isset( $locations['primary'] ) ? (int) $locations['primary'] : 0;
		if ( ! $menu_id ) { return; }
		$archive = get_post_type_archive_link( 'qaf_work' );
		if ( ! $archive ) { return; }
		$parent_id = 0;
		foreach ( wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) ) as $menu_item ) {
			if ( untrailingslashit( $menu_item->url ) === untrailingslashit( $archive ) ) { return; }
			if ( 'Kesiswaan' === trim( wp_strip_all_tags( $menu_item->title ) ) ) { $parent_id = (int) $menu_item->ID; }
		}
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title' => 'Karya Siswa', 'menu-item-url' => $archive,
			'menu-item-status' => 'publish', 'menu-item-parent-id' => $parent_id,
		) );
	}
}
