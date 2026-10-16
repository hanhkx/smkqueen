<?php
/** Manual, non-destructive Reel archive checklist. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class QAF_Core_Reel_Archive {
	const SOURCE_META = '_qaf_manual_reel_shortcode';
	const MENU = 'qaf-reel-archive';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_qaf_prepare_reel_archive', array( __CLASS__, 'prepare' ) );
	}

	public static function menu() {
		add_submenu_page( 'qaf-school', 'Arsip Reel Manual', 'Arsip Reel Manual', 'manage_options', self::MENU, array( __CLASS__, 'render' ) );
	}

	public static function data() {
		return require QAF_CORE_PATH . 'includes/reel-archive-data.php';
	}

	/** Match both legacy embeds and manually archived records, including Trash. */
	public static function find_existing( $code ) {
		$ids = get_posts( array(
			'post_type' => 'qaf_gallery', 'post_status' => array_values( get_post_stati() ),
			'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true,
		) );
		foreach ( $ids as $id ) {
			if ( $code === get_post_meta( $id, self::SOURCE_META, true ) ) { return (int) $id; }
			$url = get_post_meta( $id, '_qaf_video_url', true );
			$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
			$path = (string) wp_parse_url( $url, PHP_URL_PATH );
			if ( in_array( $host, array( 'instagram.com', 'www.instagram.com' ), true ) && preg_match( '#/(?:reel|p)/' . preg_quote( $code, '#' ) . '/?$#', $path ) ) {
				return (int) $id;
			}
		}
		return 0;
	}

	/** Only explicit administrator POST creates drafts; activation never publishes. */
	public static function prepare() {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Akses ditolak.', '', array( 'response' => 403 ) ); }
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) { wp_die( 'POST diperlukan.', '', array( 'response' => 405 ) ); }
		check_admin_referer( 'qaf_prepare_reel_archive' );
		// Atomic lock: a second click cannot insert duplicate drafts.
		if ( ! add_option( 'qaf_reel_archive_lock', time(), '', false ) ) {
			wp_die( 'Penyiapan lain sedang berjalan. Jika terhenti, administrator perlu memeriksa kunci proses sebelum mencoba lagi.' );
		}
		$created = 0;
		$errors = 0;
		try {
			$data = self::data();
			foreach ( $data['items'] as $item ) {
				if ( self::find_existing( $item['shortcode'] ) ) { continue; }
				$id = wp_insert_post( wp_slash( array(
					'post_type' => 'qaf_gallery', 'post_status' => 'draft',
					'post_title' => $item['title'] . ' | SMK Queen Al-Falah',
					'post_name' => 'reel-' . strtolower( $item['shortcode'] ),
					'post_excerpt' => $item['caption'],
					'post_content' => '<p>' . esc_html( $item['caption'] ) . '</p><p>Tanggal unggahan Instagram: ' . esc_html( $item['date'] ) . '. Tanggal unggahan tidak selalu sama dengan tanggal kegiatan.</p><p>Sumber: <a href="' . esc_url( $item['source_url'] ) . '">Instagram resmi SMK Queen Al-Falah</a>.</p>',
					'meta_input' => array(
						self::SOURCE_META => $item['shortcode'],
						'_qaf_video_url' => $item['source_url'],
						'_qaf_gallery_source' => 'local',
						'_qaf_gallery_media_type' => 'video',
						'_qaf_reel_posted_date' => $item['date'],
					),
				) ), true );
				if ( is_wp_error( $id ) ) { ++$errors; } else { ++$created; }
			}
		} finally {
			delete_option( 'qaf_reel_archive_lock' );
		}
		wp_safe_redirect( add_query_arg( array( 'page' => self::MENU, 'created' => $created, 'errors' => $errors ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$data = self::data();
		?>
		<div class="wrap"><h1>Arsip Reel Manual — TP 2026/2027</h1>
		<p>Daftar 25 Reel akun resmi, rentang 1 Juli–5 September 2026. Tidak memerlukan token Meta. Video tidak diunduh otomatis.</p>
		<p>1. Unduh berkas asli milik sekolah. 2. Buka entri Galeri, pilih <strong>Lokal / Media Library → Video</strong>, unggah/pilih Video Lokal dan gambar unggulan. 3. Periksa video beserta suara, lalu terbitkan. Entri lama tidak ditimpa.</p>
		<p><strong>Tanggal di bawah adalah tanggal unggahan Instagram, bukan klaim tanggal kegiatan.</strong> Draf tanpa video belum siap terbit. Satu konten kolaborator milik SMA Queen dikecualikan dari daftar ini.</p>
		<?php if ( isset( $_GET['created'] ) ) : ?><div class="notice notice-info inline"><p><?php echo esc_html( sprintf( '%d draf dibuat; %d galat. Entri yang sudah ada dilewati.', absint( $_GET['created'] ), absint( $_GET['errors'] ?? 0 ) ) ); ?></p></div><?php endif; ?>
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="qaf_prepare_reel_archive">
			<?php wp_nonce_field( 'qaf_prepare_reel_archive' ); submit_button( 'Siapkan Draf yang Belum Ada' ); ?>
		</form>
		<table class="widefat striped"><thead><tr><th>Tanggal unggahan</th><th>Judul &amp; sumber</th><th>Video lokal</th><th>Galeri</th></tr></thead><tbody>
		<?php foreach ( $data['items'] as $item ) :
			$id = self::find_existing( $item['shortcode'] );
			$video = $id ? absint( get_post_meta( $id, '_qaf_gallery_local_video_id', true ) ) : 0;
			$valid = $video && 'attachment' === get_post_type( $video ) && 0 === strpos( (string) get_post_mime_type( $video ), 'video/' ) && wp_get_attachment_url( $video );
			?>
			<tr><td><?php echo esc_html( $item['date'] ); ?></td><td><?php echo esc_html( $item['title'] ); ?><br><a href="<?php echo esc_url( $item['source_url'] ); ?>" target="_blank" rel="noopener noreferrer">Lihat sumber Instagram</a></td><td><?php echo $valid ? 'Terpasang — periksa pemutaran' : 'Belum terpasang'; ?></td><td><?php if ( $id ) : ?><a href="<?php echo esc_url( get_edit_post_link( $id ) ); ?>">Sunting #<?php echo absint( $id ); ?></a> (<?php echo esc_html( get_post_status( $id ) ); ?>)<?php else : ?>Belum dibuat<?php endif; ?></td></tr>
		<?php endforeach; ?>
		</tbody></table></div>
		<?php
	}
}
