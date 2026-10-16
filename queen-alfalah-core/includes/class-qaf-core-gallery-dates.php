<?php
/** Verified album dates, separate from WordPress publication dates. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class QAF_Core_Gallery_Dates {
	public static function init() {
		add_filter( 'posts_orderby', array( __CLASS__, 'order_by_album_date' ), 30, 2 );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_qaf_gallery_dates', array( __CLASS__, 'process' ) );
	}

	public static function valid_date( $date ) {
		if ( ! is_string( $date ) || ! preg_match( '/^[1-9][0-9]{3}-[0-9]{2}-[0-9]{2}$/D', $date ) ) { return false; }
		return checkdate( (int) substr( $date, 5, 2 ), (int) substr( $date, 8, 2 ), (int) substr( $date, 0, 4 ) );
	}

	/** No inner join: undated albums stay visible and repeated meta cannot duplicate cards. */
	public static function order_by_album_date( $orderby, $query ) {
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || $query->is_singular() ) { return $orderby; }
		$type = $query->get( 'post_type' );
		if ( 'qaf_gallery' !== $type && array( 'qaf_gallery' ) !== $type && ! $query->is_tax( 'qaf_gallery_category' ) ) { return $orderby; }
		global $wpdb;
		$year = 'CAST(SUBSTR(qaf_album_meta.meta_value, 1, 4) AS UNSIGNED)';
		$days = "CASE SUBSTR(qaf_album_meta.meta_value, 6, 2) WHEN '02' THEN (28 + CASE WHEN ($year % 400 = 0 OR ($year % 4 = 0 AND $year % 100 <> 0)) THEN 1 ELSE 0 END)"
			. " WHEN '04' THEN 30 WHEN '06' THEN 30 WHEN '09' THEN 30 WHEN '11' THEN 30 ELSE 31 END";
		// Only trusted table identifiers and literal metadata are interpolated.
		$album_date = "(SELECT MAX(qaf_album_meta.meta_value) FROM {$wpdb->postmeta} AS qaf_album_meta"
			. " WHERE qaf_album_meta.post_id = {$wpdb->posts}.ID AND qaf_album_meta.meta_key = '_qaf_album_date'"
			. " AND qaf_album_meta.meta_value REGEXP '^[1-9][0-9]{3}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$'"
			. " AND CAST(SUBSTR(qaf_album_meta.meta_value, 9, 2) AS UNSIGNED) <= ($days))";
		// Keep dated albums newest-first. Reverse only the undated group, with
		// a stable ascending ID tie-breaker for batches published at the same time.
		return "$album_date DESC,"
			. " CASE WHEN $album_date IS NULL THEN {$wpdb->posts}.post_date END ASC,"
			. " CASE WHEN $album_date IS NULL THEN {$wpdb->posts}.ID END ASC,"
			. " {$wpdb->posts}.post_date DESC, {$wpdb->posts}.ID DESC";
	}

	public static function validate_rows( $rows ) {
		if ( ! is_array( $rows ) || ! count( $rows ) || count( $rows ) > 58 ) { return 'Daftar tanggal harus berisi 1–58 entri.'; }
		$seen = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) || ! isset( $row['shortcode'], $row['album_date'], $row['evidence'] ) ) { return 'Shortcode, tanggal, dan bukti wajib diisi.'; }
			if ( ! is_string( $row['shortcode'] ) || ! preg_match( '/^[A-Za-z0-9_-]{8,20}$/D', $row['shortcode'] ) || isset( $seen[ $row['shortcode'] ] ) ) { return 'Shortcode tanggal tidak valid atau ganda.'; }
			if ( ! self::valid_date( $row['album_date'] ) ) { return 'Tanggal harus valid dalam format YYYY-MM-DD.'; }
			if ( ! is_string( $row['evidence'] ) || '' === trim( $row['evidence'] ) || strlen( $row['evidence'] ) > 1000 ) { return 'Bukti tanggal wajib diisi, maksimal 1000 karakter.'; }
			$seen[ $row['shortcode'] ] = true;
		}
		return '';
	}

	/** Fail closed if a source has multiple matching gallery records. */
	public static function find_target( $code ) {
		$matches = array();
		foreach ( get_posts( array( 'post_type' => 'qaf_gallery', 'post_status' => array_values( get_post_stati() ), 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true ) ) as $id ) {
			$source = get_post_meta( $id, QAF_Core_Reel_Archive::SOURCE_META, true );
			$url = get_post_meta( $id, '_qaf_video_url', true );
			$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
			$path = (string) wp_parse_url( $url, PHP_URL_PATH );
			if ( $source === $code || ( in_array( $host, array( 'instagram.com', 'www.instagram.com' ), true ) && preg_match( '#/(?:reel|p)/' . preg_quote( $code, '#' ) . '/?$#', $path ) ) ) { $matches[] = (int) $id; }
		}
		return 1 === count( $matches ) ? $matches[0] : 0;
	}

	/** Metadata only: never publishes drafts or modifies titles, captions, URLs or media. */
	public static function apply_rows( $rows ) {
		$error = self::validate_rows( $rows );
		if ( $error ) { return array( $error ); }
		$log = array();
		foreach ( $rows as $row ) {
			$id = self::find_target( $row['shortcode'] );
			if ( ! $id || 'qaf_gallery' !== get_post_type( $id ) || ! in_array( get_post_status( $id ), array( 'publish', 'draft', 'pending', 'private', 'future' ), true ) || ! current_user_can( 'edit_post', $id ) ) {
				$log[] = $row['shortcode'] . ': dilewati, galeri tidak ditemukan secara unik atau tidak dapat disunting.';
				continue;
			}
			$old = get_post_meta( $id, '_qaf_album_date', true );
			if ( ! metadata_exists( 'post', $id, '_qaf_album_date_before_review' ) ) {
				update_post_meta( $id, '_qaf_album_date_before_review', $old );
				if ( ! metadata_exists( 'post', $id, '_qaf_album_date_before_review' ) || $old !== get_post_meta( $id, '_qaf_album_date_before_review', true ) ) {
					$log[] = '#' . $id . ': cadangan gagal disimpan; tanggal tidak diubah.';
					continue;
				}
			}
			$evidence = sanitize_textarea_field( $row['evidence'] );
			update_post_meta( $id, '_qaf_album_date_evidence', wp_slash( $evidence ) );
			if ( $evidence !== get_post_meta( $id, '_qaf_album_date_evidence', true ) ) {
				$log[] = '#' . $id . ': bukti gagal disimpan; tanggal tidak diubah.';
				continue;
			}
			update_post_meta( $id, '_qaf_album_date', $row['album_date'] );
			$ok = $row['album_date'] === get_post_meta( $id, '_qaf_album_date', true );
			$log[] = '#' . $id . ': ' . ( $ok ? 'tanggal album ' . $row['album_date'] . ' tersimpan.' : 'tanggal gagal disimpan.' );
		}
		return $log;
	}

	public static function menu() {
		add_submenu_page( 'qaf-school', 'Tanggal Album Galeri', 'Tanggal Album Galeri', 'manage_options', 'qaf-gallery-dates', array( __CLASS__, 'render' ) );
	}

	public static function process() {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Akses ditolak.', '', array( 'response' => 403 ) ); }
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) { wp_die( 'POST diperlukan.', '', array( 'response' => 405 ) ); }
		check_admin_referer( 'qaf_gallery_dates' );
		$raw = wp_unslash( $_POST['date_manifest'] ?? '' );
		if ( ! is_string( $raw ) || strlen( $raw ) > 100000 ) { wp_die( 'Manifest tanggal terlalu besar.' ); }
		$rows = json_decode( $raw, true );
		$error = self::validate_rows( $rows );
		if ( $error ) { wp_die( esc_html( $error ) ); }
		if ( ! add_option( 'qaf_gallery_batch_lock', time(), '', false ) ) { wp_die( 'Proses galeri lain masih berjalan. Periksa hasilnya sebelum mencoba lagi.' ); }
		try { $report = self::apply_rows( $rows ); } finally { delete_option( 'qaf_gallery_batch_lock' ); }
		update_option( 'qaf_gallery_dates_report', array( 'time' => current_time( 'mysql' ), 'rows' => $report ), false );
		wp_safe_redirect( admin_url( 'admin.php?page=qaf-gallery-dates' ) );
		exit;
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$data = require QAF_CORE_PATH . 'includes/gallery-dates-data.php';
		$report = get_option( 'qaf_gallery_dates_report', array() );
		?>
		<div class="wrap"><h1>Tanggal Album Galeri</h1>
		<p>Galeri dan beranda menampilkan album bertanggal dari terbaru ke terlama terlebih dahulu. Setelah itu, album tanpa tanggal diurutkan berdasarkan publikasi paling lama ke paling baru. Tanggal penerbitan WordPress tidak diubah.</p>
		<p>Daftar di bawah hanya berisi tanggal yang terbaca pada video. Tanggal pada poster jadwal pendaftaran atau pengumuman layanan tidak dianggap sebagai tanggal rekaman kegiatan. Tanggal unggahan Instagram tidak digunakan.</p>
		<p>Tindakan ini hanya memperbarui metadata tanggal pada galeri yang cocok; tidak menerbitkan draf, mengganti caption, atau mengunggah media. Tanggal lama disimpan sebagai cadangan sebelum pembaruan pertama.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="qaf_gallery_dates"><?php wp_nonce_field( 'qaf_gallery_dates' ); ?>
		<label for="qaf-date-manifest">Tanggal album dan bukti dari video (JSON)</label>
		<textarea id="qaf-date-manifest" name="date_manifest" rows="18" class="large-text code" required><?php echo esc_textarea( wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); ?></textarea>
		<?php submit_button( 'Terapkan Tanggal Album Terverifikasi' ); ?></form>
		<p>Untuk menyunting satu album: Galeri → Sunting → Data Sekolah → Tanggal Album. Kosongkan jika tanggal kegiatan belum diketahui.</p>
		<?php if ( ! empty( $report['rows'] ) ) : ?><h2>Hasil terakhir — <?php echo esc_html( $report['time'] ); ?></h2><ul><?php foreach ( $report['rows'] as $line ) : ?><li><?php echo esc_html( $line ); ?></li><?php endforeach; ?></ul><?php endif; ?>
		</div>
		<?php
	}
}
