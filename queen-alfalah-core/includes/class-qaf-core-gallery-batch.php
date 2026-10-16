<?php
/** Administrator-only, resumable publication of reviewed local gallery records. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class QAF_Core_Gallery_Batch {
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_qaf_gallery_batch', array( __CLASS__, 'process' ) );
	}
	public static function menu() {
		add_submenu_page( 'qaf-school', 'Publikasi Galeri Massal', 'Publikasi Galeri Massal', 'manage_options', 'qaf-gallery-batch', array( __CLASS__, 'render' ) );
	}
	public static function validate( $rows ) {
		if ( ! is_array( $rows ) || ! count( $rows ) || count( $rows ) > 58 ) { return 'Daftar harus berisi 1–58 entri.'; }
		$seen = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) { return 'Format entri tidak valid.'; }
			foreach ( array( 'shortcode', 'video', 'poster', 'title', 'caption', 'excerpt' ) as $key ) {
				if ( ! isset( $row[ $key ] ) || ! is_string( $row[ $key ] ) || '' === trim( $row[ $key ] ) ) { return 'Kolom wajib kosong: ' . $key; }
			}
			if ( ! preg_match( '/^[A-Za-z0-9_-]{8,20}$/D', $row['shortcode'] ) || isset( $seen[ $row['shortcode'] ] ) ) { return 'Shortcode tidak valid atau ganda.'; }
			$seen[ $row['shortcode'] ] = true;
			if ( ! preg_match( '/^smkqueenalfalah_official_[0-9]+_[0-9]+_5484283193(?:-1)?\.mp4$/D', $row['video'] ) ) { return 'Nama video bukan berkas akun resmi SMK.'; }
			if ( basename( $row['poster'] ) !== $row['poster'] || ! preg_match( '/^[a-zA-Z0-9_-]+\.jpg$/D', $row['poster'] ) ) { return 'Nama sampul tidak valid.'; }
			if ( strlen( $row['caption'] ) > 12000 || strlen( $row['title'] ) > 300 || strlen( $row['excerpt'] ) > 1000 ) { return 'Teks terlalu panjang.'; }
		}
		return '';
	}
	/** Exact basename and MIME, fail closed on ambiguous duplicate media. */
	public static function attachment( $name, $mime ) {
		$ids = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => '_wp_attached_file', 'value' => $name, 'compare' => 'LIKE' ) ) ) );
		$matches = array();
		foreach ( $ids as $id ) {
			if ( basename( get_post_meta( $id, '_wp_attached_file', true ) ) === $name && 0 === strpos( (string) get_post_mime_type( $id ), $mime ) && wp_get_attachment_url( $id ) ) { $matches[] = (int) $id; }
		}
		return 1 === count( $matches ) ? $matches[0] : 0;
	}
	public static function process() {
		if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'publish_posts' ) ) { wp_die( 'Akses ditolak.', '', array( 'response' => 403 ) ); }
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) { wp_die( 'POST diperlukan.', '', array( 'response' => 405 ) ); }
		check_admin_referer( 'qaf_gallery_batch' );
		$raw = wp_unslash( $_POST['manifest'] ?? '' );
		if ( ! is_string( $raw ) || strlen( $raw ) > 500000 ) { wp_die( 'Manifest terlalu besar.' ); }
		$rows = json_decode( $raw, true );
		$error = self::validate( $rows );
		if ( $error ) { wp_die( esc_html( $error ) ); }
		if ( ! add_option( 'qaf_gallery_batch_lock', time(), '', false ) ) { wp_die( 'Proses lain masih terkunci. Periksa hasil proses sebelumnya sebelum mencoba lagi.' ); }
		$log = array();
		try {
			foreach ( $rows as $row ) {
				$id = QAF_Core_Reel_Archive::find_existing( $row['shortcode'] );
				if ( $id && 'draft' !== get_post_status( $id ) ) { $log[] = $row['title'] . ': dilewati, status ' . get_post_status( $id ) . ' #' . $id; continue; }
				$video = self::attachment( $row['video'], 'video/' );
				$poster = self::attachment( $row['poster'], 'image/' );
				if ( ! $video || ! $poster ) { $log[] = $row['title'] . ': ditahan, video/sampul belum ditemukan secara unik.'; continue; }
				if ( $id && ! current_user_can( 'edit_post', $id ) ) { $log[] = $row['title'] . ': akses sunting ditolak.'; continue; }
				$url = 'https://www.instagram.com/smkqueenalfalah_official/reel/' . $row['shortcode'] . '/';
				$post = array( 'post_type' => 'qaf_gallery', 'post_status' => 'draft', 'post_title' => sanitize_text_field( $row['title'] ), 'post_excerpt' => sanitize_textarea_field( $row['excerpt'] ), 'post_content' => wpautop( esc_html( $row['caption'] ) ) . '<p>Sumber: <a href="' . esc_url( $url ) . '">Instagram resmi SMK Queen Al-Falah</a>. Dokumentasi arsip; informasi jadwal atau pendaftaran dalam video tidak otomatis berlaku saat ini.</p>' );
				if ( $id ) { $post['ID'] = $id; } else { $post['post_name'] = sanitize_title( $row['title'] ); }
				$result = wp_insert_post( wp_slash( $post ), true );
				if ( is_wp_error( $result ) ) { $log[] = $row['title'] . ': gagal menyimpan draf.'; continue; }
				$id = (int) $result;
				update_post_meta( $id, QAF_Core_Reel_Archive::SOURCE_META, $row['shortcode'] );
				update_post_meta( $id, '_qaf_video_url', $url );
				update_post_meta( $id, '_qaf_gallery_source', 'local' );
				update_post_meta( $id, '_qaf_gallery_media_type', 'video' );
				update_post_meta( $id, '_qaf_gallery_local_video_id', $video );
				set_post_thumbnail( $id, $poster );
				if ( ! get_post_meta( $poster, '_wp_attachment_image_alt', true ) ) { update_post_meta( $poster, '_wp_attachment_image_alt', sanitize_text_field( $row['title'] ) ); }
				if ( (int) get_post_thumbnail_id( $id ) !== $poster || (int) get_post_meta( $id, '_qaf_gallery_local_video_id', true ) !== $video ) { $log[] = $row['title'] . ': draf disimpan, verifikasi media gagal.'; continue; }
				$result = wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ), true );
				$log[] = $row['title'] . ( is_wp_error( $result ) || 'publish' !== get_post_status( $id ) ? ': gagal terbit #' : ': terbit #' ) . $id;
			}
		} finally { delete_option( 'qaf_gallery_batch_lock' ); }
		update_option( 'qaf_gallery_batch_report', array( 'time' => current_time( 'mysql' ), 'rows' => $log ), false );
		wp_safe_redirect( admin_url( 'admin.php?page=qaf-gallery-batch' ) );
		exit;
	}
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$report = get_option( 'qaf_gallery_batch_report', array() );
		?>
		<div class="wrap"><h1>Publikasi Galeri Massal</h1><p>Khusus manifest yang sudah ditinjau. Setiap entri wajib memiliki shortcode sumber, nama berkas video, nama berkas sampul, judul, caption, dan ringkasan. Galeri terbit, privat, terjadwal, dan Trash tidak ditimpa. Media hilang atau ambigu menahan publikasi.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="qaf_gallery_batch"><?php wp_nonce_field( 'qaf_gallery_batch' ); ?>
		<label for="qaf-manifest">Manifest JSON hasil peninjauan (maksimal 58 entri)</label><textarea id="qaf-manifest" name="manifest" rows="18" class="large-text code" required></textarea>
		<?php submit_button( 'Terbitkan Entri yang Lengkap' ); ?></form>
		<?php if ( ! empty( $report['rows'] ) ) : ?><h2>Hasil terakhir — <?php echo esc_html( $report['time'] ); ?></h2><ul><?php foreach ( $report['rows'] as $line ) : ?><li><?php echo esc_html( $line ); ?></li><?php endforeach; ?></ul><?php endif; ?>
		</div><?php
	}
}
