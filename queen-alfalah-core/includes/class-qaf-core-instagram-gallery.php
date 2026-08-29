<?php
/**
 * Official Instagram API synchronization for Gallery videos and Reels.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Synchronize public videos from one authorized professional Instagram account.
 */
final class QAF_Core_Instagram_Gallery {
	/** Private, non-autoloaded connection settings. */
	const OPTION_NAME = 'qaf_instagram_gallery_sync';

	/** Non-sensitive synchronization status. */
	const STATE_OPTION = 'qaf_instagram_gallery_sync_state';

	/** Daily WordPress cron hook. */
	const CRON_HOOK = 'qaf_instagram_gallery_sync_event';

	/** Atomic option lock preventing overlapping manual and scheduled imports. */
	const LOCK_KEY = 'qaf_instagram_gallery_sync_lock';

	/** Official API root for Instagram Login. */
	const API_ROOT = 'https://graph.instagram.com';

	/** Pinned Graph API contract for this plugin release. */
	const API_VERSION = 'v26.0';

	/** Maximum JSON response accepted from one API page. */
	const MAX_RESPONSE_BYTES = 1048576;

	/** Maximum poster file copied into the Media Library. */
	const MAX_THUMBNAIL_BYTES = 8388608;

	/**
	 * Request-local canonical URL index for legacy/manual entries.
	 *
	 * @var array<string,int>|null
	 */
	private static $canonical_url_index = null;

	/**
	 * Attach administration, POST, cron, and privacy hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 35 );
		add_action( 'admin_post_qaf_instagram_gallery_save', array( __CLASS__, 'handle_save' ) );
		add_action( 'admin_post_qaf_instagram_gallery_sync', array( __CLASS__, 'handle_manual_sync' ) );
		add_action( self::CRON_HOOK, array( __CLASS__, 'handle_scheduled_sync' ) );
	}

	/**
	 * Install private defaults without overwriting an existing connection.
	 *
	 * @return void
	 */
	public static function activate() {
		add_option( self::OPTION_NAME, self::get_defaults(), '', false );
		add_option( self::STATE_OPTION, array(), '', false );
		self::reconcile_schedule( self::get_settings() );
	}

	/**
	 * Stop future synchronization while retaining imported school content.
	 *
	 * @return void
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
		delete_option( self::LOCK_KEY );
		delete_transient( self::LOCK_KEY );
	}

	/**
	 * Safe connection defaults.
	 *
	 * New items are drafts until an administrator explicitly changes this.
	 *
	 * @return array<string,mixed>
	 */
	public static function get_defaults() {
		return array(
			'user_id'             => '',
			'access_token'        => '',
			'auto_sync'           => 0,
			'post_status'         => 'draft',
			'max_items'           => 24,
			'download_thumbnails' => 1,
			'token_saved_at'      => '',
			'last_refresh_at'     => '',
		);
	}

	/**
	 * Read and normalize the private connection settings.
	 *
	 * @return array<string,mixed>
	 */
	public static function get_settings() {
		$stored = get_option( self::OPTION_NAME, array() );
		$stored = is_array( $stored ) ? $stored : array();
		return self::normalize_settings( wp_parse_args( $stored, self::get_defaults() ) );
	}

	/**
	 * Add the synchronization page beneath the shared School menu.
	 *
	 * @return void
	 */
	public static function register_menu() {
		add_submenu_page(
			'qaf-school',
			__( 'Sinkronisasi Galeri Instagram', 'queen-alfalah-core' ),
			__( 'Instagram Galeri', 'queen-alfalah-core' ),
			QAF_Core_Settings::get_capability(),
			'qaf-instagram-gallery',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Render connection controls without ever printing the stored token.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( QAF_Core_Settings::get_capability() ) ) {
			wp_die( esc_html__( 'Anda tidak memiliki izin untuk mengakses halaman ini.', 'queen-alfalah-core' ) );
		}

		$settings = self::get_settings();
		$state    = get_option( self::STATE_OPTION, array() );
		$state    = is_array( $state ) ? $state : array();
		$notice   = isset( $_GET['qaf_notice'] ) ? sanitize_key( wp_unslash( $_GET['qaf_notice'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only allowlist.
		$notices  = array(
			'saved'          => array( 'success', __( 'Pengaturan sinkronisasi disimpan.', 'queen-alfalah-core' ) ),
			'synced'         => array( 'success', __( 'Sinkronisasi Instagram selesai.', 'queen-alfalah-core' ) ),
			'sync_failed'    => array( 'error', __( 'Sinkronisasi belum berhasil. Periksa status di bawah.', 'queen-alfalah-core' ) ),
			'invalid_token'  => array( 'error', __( 'Token tidak disimpan karena formatnya tidak valid.', 'queen-alfalah-core' ) ),
			'invalid_request' => array( 'error', __( 'Permintaan tidak valid. Muat ulang halaman lalu coba lagi.', 'queen-alfalah-core' ) ),
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Sinkronisasi Galeri Instagram', 'queen-alfalah-core' ); ?></h1>
			<p><?php esc_html_e( 'Ambil Reel dan video akun Instagram resmi ke Galeri WordPress melalui API resmi Meta. Fitur ini tidak meminta atau menyimpan kata sandi Instagram.', 'queen-alfalah-core' ); ?></p>

			<?php if ( isset( $notices[ $notice ] ) ) : ?>
				<div class="notice notice-<?php echo esc_attr( $notices[ $notice ][0] ); ?> is-dismissible"><p><?php echo esc_html( $notices[ $notice ][1] ); ?></p></div>
			<?php endif; ?>

			<div class="notice notice-info inline">
				<p><strong><?php esc_html_e( 'Syarat akun', 'queen-alfalah-core' ); ?></strong></p>
				<p><?php esc_html_e( 'Gunakan akun Instagram Profesional (Business/Creator), aplikasi Meta bertipe Business, izin instagram_business_basic, Instagram User ID, dan long-lived access token. Jangan menempelkan App Secret, password, cookie, atau token ke entri Galeri.', 'queen-alfalah-core' ); ?></p>
			</div>

			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" autocomplete="off">
				<input type="hidden" name="action" value="qaf_instagram_gallery_save">
				<?php wp_nonce_field( 'qaf_instagram_gallery_save', 'qaf_instagram_nonce' ); ?>
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><label for="qaf-instagram-user-id"><?php esc_html_e( 'Instagram User ID', 'queen-alfalah-core' ); ?></label></th>
							<td>
								<input class="regular-text" id="qaf-instagram-user-id" name="qaf_instagram[user_id]" type="text" inputmode="numeric" pattern="[0-9]+" value="<?php echo esc_attr( $settings['user_id'] ); ?>">
								<p class="description"><?php esc_html_e( 'ID numerik akun profesional yang diberikan Instagram API; bukan @username.', 'queen-alfalah-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="qaf-instagram-token"><?php esc_html_e( 'Long-lived access token', 'queen-alfalah-core' ); ?></label></th>
							<td>
								<input class="regular-text" id="qaf-instagram-token" name="qaf_instagram[access_token]" type="password" value="" spellcheck="false" aria-describedby="qaf-instagram-token-help">
								<p id="qaf-instagram-token-help" class="description">
									<?php echo $settings['access_token'] ? esc_html__( 'Token sudah tersimpan di server. Biarkan kosong untuk mempertahankannya.', 'queen-alfalah-core' ) : esc_html__( 'Belum ada token. Tempel long-lived token dari aplikasi Meta resmi sekolah.', 'queen-alfalah-core' ); ?>
								</p>
								<?php if ( $settings['access_token'] ) : ?>
									<label><input type="checkbox" name="qaf_instagram[clear_token]" value="1"> <?php esc_html_e( 'Putuskan koneksi dan hapus token tersimpan', 'queen-alfalah-core' ); ?></label>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="qaf-instagram-status"><?php esc_html_e( 'Status video baru', 'queen-alfalah-core' ); ?></label></th>
							<td>
								<select id="qaf-instagram-status" name="qaf_instagram[post_status]">
									<option value="draft"<?php selected( $settings['post_status'], 'draft' ); ?>><?php esc_html_e( 'Draf — tinjau dahulu', 'queen-alfalah-core' ); ?></option>
									<option value="publish"<?php selected( $settings['post_status'], 'publish' ); ?>><?php esc_html_e( 'Terbit otomatis', 'queen-alfalah-core' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Perubahan ini hanya berlaku untuk entri yang baru dibuat; status entri lama tidak ditimpa.', 'queen-alfalah-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="qaf-instagram-max-items"><?php esc_html_e( 'Postingan terbaru yang diperiksa', 'queen-alfalah-core' ); ?></label></th>
							<td><input id="qaf-instagram-max-items" name="qaf_instagram[max_items]" type="number" min="1" max="100" step="1" value="<?php echo esc_attr( $settings['max_items'] ); ?>"></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Poster Galeri', 'queen-alfalah-core' ); ?></th>
							<td>
								<label><input type="checkbox" name="qaf_instagram[download_thumbnails]" value="1"<?php checked( $settings['download_thumbnails'] ); ?>> <?php esc_html_e( 'Salin thumbnail resmi ke Media Library bila Gambar Utama masih kosong', 'queen-alfalah-core' ); ?></label>
								<p class="description"><?php esc_html_e( 'Salinan lokal mencegah kartu Galeri bergantung pada alamat CDN sementara. Pastikan sekolah berhak menampilkan dokumentasi tersebut.', 'queen-alfalah-core' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Jadwal', 'queen-alfalah-core' ); ?></th>
							<td>
								<label><input type="checkbox" name="qaf_instagram[auto_sync]" value="1"<?php checked( $settings['auto_sync'] ); ?>> <?php esc_html_e( 'Periksa Reel/video baru satu kali setiap hari', 'queen-alfalah-core' ); ?></label>
								<p class="description"><?php esc_html_e( 'WordPress Cron berjalan ketika situs menerima kunjungan. Tombol Sinkronkan sekarang tetap dapat digunakan kapan saja.', 'queen-alfalah-core' ); ?></p>
							</td>
						</tr>
					</tbody>
				</table>
				<?php submit_button( __( 'Simpan Pengaturan', 'queen-alfalah-core' ) ); ?>
			</form>

			<hr>
			<h2><?php esc_html_e( 'Jalankan Sinkronisasi', 'queen-alfalah-core' ); ?></h2>
			<p><?php esc_html_e( 'Hanya media bertipe VIDEO, termasuk Reel, yang dibuat sebagai entri Galeri. Foto biasa dilewati. Sinkronisasi tidak menghapus konten saat postingan Instagram hilang dan tidak menimpa judul, isi, status, atau gambar yang sudah disunting.', 'queen-alfalah-core' ); ?></p>
			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="qaf_instagram_gallery_sync">
				<?php wp_nonce_field( 'qaf_instagram_gallery_sync', 'qaf_instagram_nonce' ); ?>
				<?php submit_button( __( 'Sinkronkan Sekarang', 'queen-alfalah-core' ), 'primary', 'submit', false, array( 'disabled' => empty( $settings['user_id'] ) || empty( $settings['access_token'] ) ) ); ?>
				<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=qaf_gallery' ) ); ?>"><?php esc_html_e( 'Buka daftar Galeri', 'queen-alfalah-core' ); ?></a>
			</form>

			<h2><?php esc_html_e( 'Status Terakhir', 'queen-alfalah-core' ); ?></h2>
			<table class="widefat striped" style="max-width:900px">
				<tbody>
					<tr><th scope="row"><?php esc_html_e( 'Koneksi', 'queen-alfalah-core' ); ?></th><td><?php echo $settings['access_token'] && $settings['user_id'] ? esc_html__( 'Siap', 'queen-alfalah-core' ) : esc_html__( 'Belum lengkap', 'queen-alfalah-core' ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Percobaan terakhir', 'queen-alfalah-core' ); ?></th><td><?php echo esc_html( self::format_state_time( isset( $state['attempted_at'] ) ? $state['attempted_at'] : '' ) ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Berhasil terakhir', 'queen-alfalah-core' ); ?></th><td><?php echo esc_html( self::format_state_time( isset( $state['succeeded_at'] ) ? $state['succeeded_at'] : '' ) ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Hasil', 'queen-alfalah-core' ); ?></th><td><?php echo esc_html( isset( $state['message'] ) ? $state['message'] : __( 'Belum pernah disinkronkan.', 'queen-alfalah-core' ) ); ?></td></tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Save settings after capability, method, nonce, and token validation.
	 *
	 * @return void
	 */
	public static function handle_save() {
		self::authorize_post( 'qaf_instagram_gallery_save' );

		$previous = self::get_settings();
		$posted   = isset( $_POST['qaf_instagram'] ) && is_array( $_POST['qaf_instagram'] ) ? wp_unslash( $_POST['qaf_instagram'] ) : array();
		$next     = $previous;

		$next['user_id']             = isset( $posted['user_id'] ) ? preg_replace( '/[^0-9]/', '', (string) $posted['user_id'] ) : '';
		$next['auto_sync']           = empty( $posted['auto_sync'] ) ? 0 : 1;
		$next['post_status']         = isset( $posted['post_status'] ) && 'publish' === $posted['post_status'] ? 'publish' : 'draft';
		$next['max_items']           = isset( $posted['max_items'] ) ? max( 1, min( 100, absint( $posted['max_items'] ) ) ) : 24;
		$next['download_thumbnails'] = empty( $posted['download_thumbnails'] ) ? 0 : 1;

		if ( ! empty( $posted['clear_token'] ) ) {
			$next['access_token']    = '';
			$next['token_saved_at']  = '';
			$next['last_refresh_at'] = '';
		} elseif ( isset( $posted['access_token'] ) && '' !== trim( (string) $posted['access_token'] ) ) {
			$token = self::sanitize_access_token( $posted['access_token'] );
			if ( '' === $token ) {
				self::redirect_with_notice( 'invalid_token' );
			}
			$next['access_token']    = $token;
			$next['token_saved_at']  = gmdate( 'c' );
			$next['last_refresh_at'] = '';
		}

		$next = self::normalize_settings( $next );
		update_option( self::OPTION_NAME, $next, false );
		self::reconcile_schedule( $next );
		self::redirect_with_notice( 'saved' );
	}

	/**
	 * Run a requested import and return to the status screen.
	 *
	 * @return void
	 */
	public static function handle_manual_sync() {
		self::authorize_post( 'qaf_instagram_gallery_sync' );
		$result = self::sync( 'manual' );
		self::redirect_with_notice( is_wp_error( $result ) ? 'sync_failed' : 'synced' );
	}

	/**
	 * Run the daily task only while explicitly enabled.
	 *
	 * @return void
	 */
	public static function handle_scheduled_sync() {
		$settings = self::get_settings();
		if ( empty( $settings['auto_sync'] ) ) {
			return;
		}
		self::sync( 'scheduled' );
	}

	/**
	 * Synchronize a bounded page set and preserve all prior school content.
	 *
	 * @param string $source Trigger source.
	 * @return array<string,int>|WP_Error
	 */
	public static function sync( $source = 'manual' ) {
		self::$canonical_url_index = null;
		$settings = self::get_settings();
		if ( empty( $settings['user_id'] ) || empty( $settings['access_token'] ) ) {
			$error = new WP_Error( 'missing_configuration', __( 'Instagram User ID atau token belum diisi.', 'queen-alfalah-core' ) );
			self::record_failure( $error, $source );
			return $error;
		}

		$lock_token = self::acquire_lock();
		if ( is_wp_error( $lock_token ) ) {
			$error = new WP_Error( 'sync_busy', __( 'Sinkronisasi lain masih berjalan. Coba lagi beberapa menit.', 'queen-alfalah-core' ) );
			self::record_failure( $error, $source );
			return $error;
		}

		try {
			$records = self::fetch_media( $settings );
			if ( is_wp_error( $records ) ) {
				self::record_failure( $records, $source );
				return $records;
			}

			$counts = array(
				'checked'        => count( $records ),
				'videos'         => 0,
				'created'        => 0,
				'updated'        => 0,
				'skipped'        => 0,
				'errors'         => 0,
				'thumbnail_errors' => 0,
			);
			$last_item_error = '';

			foreach ( $records as $record ) {
				self::refresh_lock( $lock_token );
				if ( ! self::is_video_record( $record ) ) {
					continue;
				}
				++$counts['videos'];
				$result = self::upsert_record( $record, $settings );
				if ( is_wp_error( $result ) ) {
					++$counts['errors'];
					$last_item_error = $result->get_error_message();
					continue;
				}
				$action = isset( $result['action'] ) ? $result['action'] : 'skipped';
				if ( isset( $counts[ $action ] ) ) {
					++$counts[ $action ];
				}
				if ( ! empty( $result['thumbnail_error'] ) ) {
					++$counts['thumbnail_errors'];
				}
			}

			$refresh_warning = self::maybe_refresh_access_token( $settings );
			if ( $counts['videos'] > 0 && $counts['errors'] === $counts['videos'] ) {
				$error = new WP_Error(
					'instagram_items_failed',
					$last_item_error ? $last_item_error : __( 'Semua video ditemukan, tetapi tidak ada yang dapat disimpan.', 'queen-alfalah-core' )
				);
				self::record_failure( $error, $source );
				return $error;
			}
			$item_warning = $counts['errors'] > 0
				? sprintf( __( '%d video gagal disimpan dan perlu diperiksa.', 'queen-alfalah-core' ), $counts['errors'] )
				: '';
			$warning = trim( $item_warning . ' ' . $refresh_warning );
			self::record_success( $counts, $source, $warning );
			return $counts;
		} finally {
			self::release_lock( $lock_token );
		}
	}

	/**
	 * Fetch recent account media through the official endpoint.
	 *
	 * @param array<string,mixed> $settings Connection settings.
	 * @return array<int,array<string,mixed>>|WP_Error
	 */
	private static function fetch_media( $settings ) {
		$items  = array();
		$cursor = '';
		$pages  = 0;
		$limit  = (int) $settings['max_items'];
		$seen   = array();

		do {
			$query = array(
				'fields'       => 'id,caption,media_type,permalink,thumbnail_url,timestamp,username',
				'limit'        => min( 50, $limit - count( $items ) ),
			);
			if ( $cursor ) {
				$query['after'] = $cursor;
			}

			$url      = add_query_arg( $query, self::API_ROOT . '/' . self::API_VERSION . '/' . rawurlencode( $settings['user_id'] ) . '/media' );
			$response = wp_safe_remote_get(
				$url,
				array(
					'timeout'             => 20,
					'redirection'         => 0,
					'limit_response_size' => self::MAX_RESPONSE_BYTES,
					'headers'             => array(
						'Accept'        => 'application/json',
						'Authorization' => 'Bearer ' . $settings['access_token'],
					),
					'user-agent'          => 'Queen-AlFalah-Core/' . QAF_CORE_VERSION . '; ' . home_url( '/' ),
				)
			);
			if ( is_wp_error( $response ) ) {
				return new WP_Error( 'instagram_network', __( 'Server tidak dapat menghubungi Instagram API. Periksa koneksi internet atau firewall hosting.', 'queen-alfalah-core' ) );
			}

			$code = (int) wp_remote_retrieve_response_code( $response );
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );
			if ( $code < 200 || $code >= 300 || ! is_array( $data ) || ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
				return self::api_error( $code, $data );
			}

			foreach ( $data['data'] as $record ) {
				if ( is_array( $record ) ) {
					$items[] = $record;
				}
				if ( count( $items ) >= $limit ) {
					break;
				}
			}

			$next_cursor = isset( $data['paging']['cursors']['after'] ) && is_scalar( $data['paging']['cursors']['after'] ) ? (string) $data['paging']['cursors']['after'] : '';
			$has_next    = ! empty( $data['paging']['next'] );
			if ( ! $has_next || ! $next_cursor || isset( $seen[ $next_cursor ] ) ) {
				$cursor = '';
			} else {
				$seen[ $next_cursor ] = true;
				$cursor               = $next_cursor;
			}
			++$pages;
		} while ( $cursor && count( $items ) < $limit && $pages < 10 );

		return $items;
	}

	/**
	 * Detect Instagram videos without relying on media_product_type.
	 *
	 * @param mixed $record API record.
	 * @return bool
	 */
	public static function is_video_record( $record ) {
		if ( ! is_array( $record ) ) {
			return false;
		}
		$type = isset( $record['media_type'] ) && is_scalar( $record['media_type'] ) ? strtoupper( (string) $record['media_type'] ) : '';
		if ( 'VIDEO' === $type ) {
			return true;
		}
		$permalink = isset( $record['permalink'] ) ? self::canonical_permalink( $record['permalink'] ) : '';
		return false !== strpos( $permalink, '/reel/' ) || false !== strpos( $permalink, '/tv/' );
	}

	/**
	 * Strictly normalize one public Instagram post/Reel URL.
	 *
	 * @param mixed $value Raw permalink.
	 * @return string
	 */
	public static function canonical_permalink( $value ) {
		$url = is_scalar( $value ) ? trim( (string) $value ) : '';
		if ( '' === $url ) {
			return '';
		}
		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) ) {
			return '';
		}
		$scheme = isset( $parts['scheme'] ) ? strtolower( $parts['scheme'] ) : '';
		$host   = isset( $parts['host'] ) ? strtolower( rtrim( $parts['host'], '.' ) ) : '';
		$port   = isset( $parts['port'] ) ? (int) $parts['port'] : null;
		if ( 'https' !== $scheme || ! in_array( $host, array( 'instagram.com', 'www.instagram.com' ), true ) || isset( $parts['user'] ) || isset( $parts['pass'] ) || ( null !== $port && 443 !== $port ) ) {
			return '';
		}
		$path = isset( $parts['path'] ) ? $parts['path'] : '';
		if ( ! preg_match( '#^/(p|reel|tv)/([A-Za-z0-9_-]{3,64})/?$#', $path, $matches ) ) {
			return '';
		}
		return 'https://www.instagram.com/' . strtolower( $matches[1] ) . '/' . $matches[2] . '/';
	}

	/**
	 * Create a new Gallery entry or enrich a matching item non-destructively.
	 *
	 * @param array<string,mixed> $record   API record.
	 * @param array<string,mixed> $settings Connection settings.
	 * @return array<string,mixed>|WP_Error
	 */
	private static function upsert_record( $record, $settings ) {
		$media_id = isset( $record['id'] ) && is_scalar( $record['id'] ) ? preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $record['id'] ) : '';
		$permalink = isset( $record['permalink'] ) ? self::canonical_permalink( $record['permalink'] ) : '';
		if ( '' === $media_id || '' === $permalink ) {
			return new WP_Error( 'invalid_media', __( 'Satu media dilewati karena ID atau permalink tidak valid.', 'queen-alfalah-core' ) );
		}

		$post_id = self::find_existing_post( $media_id, $permalink );
		$created = false;
		if ( $post_id && 'trash' === get_post_status( $post_id ) ) {
			return array( 'action' => 'skipped', 'thumbnail_error' => false );
		}

		$caption = isset( $record['caption'] ) && is_scalar( $record['caption'] ) ? sanitize_textarea_field( $record['caption'] ) : '';
		$date    = self::parse_timestamp( isset( $record['timestamp'] ) ? $record['timestamp'] : '' );
		if ( ! $post_id ) {
			$post_data = array(
				'post_type'    => 'qaf_gallery',
				'post_status'  => $settings['post_status'],
				'post_title'   => self::record_title( $caption, $date ),
				'post_name'    => 'instagram-' . sanitize_title( self::permalink_shortcode( $permalink ) ),
				'post_content' => $caption ? wpautop( esc_html( $caption ) ) : '',
				'post_excerpt' => $caption ? wp_trim_words( $caption, 32, '…' ) : '',
			);
			if ( $date ) {
				$post_data['post_date']     = $date->format( 'Y-m-d H:i:s' );
				$post_data['post_date_gmt'] = get_gmt_from_date( $post_data['post_date'] );
			}
			if ( function_exists( 'get_current_user_id' ) && get_current_user_id() ) {
				$post_data['post_author'] = get_current_user_id();
			}
			$post_id = wp_insert_post( wp_slash( $post_data ), true );
			if ( is_wp_error( $post_id ) ) {
				return $post_id;
			}
			$created = true;
		}

		$changed = self::set_gallery_meta( $post_id, $media_id, $permalink, $date );
		$thumbnail_error   = false;
		$thumbnail_changed = false;
		if ( ! empty( $settings['download_thumbnails'] ) && ! has_post_thumbnail( $post_id ) && ! empty( $record['thumbnail_url'] ) ) {
			$thumbnail = self::sideload_thumbnail( $record['thumbnail_url'], $post_id, $record, $media_id );
			$thumbnail_error   = is_wp_error( $thumbnail );
			$thumbnail_changed = ! $thumbnail_error;
		}

		return array(
			'action'          => $created ? 'created' : ( $changed || $thumbnail_changed ? 'updated' : 'skipped' ),
			'thumbnail_error' => $thumbnail_error,
		);
	}

	/**
	 * Locate an existing synchronized or manually-created entry.
	 *
	 * @param string $media_id Instagram media ID.
	 * @param string $permalink Canonical permalink.
	 * @return int
	 */
	private static function find_existing_post( $media_id, $permalink ) {
		$statuses = array( 'publish', 'future', 'draft', 'pending', 'private', 'trash' );
		$ids      = get_posts(
			array(
				'post_type'      => 'qaf_gallery',
				'post_status'    => $statuses,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => '_qaf_instagram_media_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $media_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		if ( $ids ) {
			return (int) $ids[0];
		}
		$ids = get_posts(
			array(
				'post_type'      => 'qaf_gallery',
				'post_status'    => $statuses,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => '_qaf_video_url', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $permalink, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		if ( $ids ) {
			return (int) $ids[0];
		}

		$shortcode = self::permalink_shortcode( $permalink );
		$ids       = get_posts(
			array(
				'post_type'      => 'qaf_gallery',
				'post_status'    => $statuses,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => '_qaf_instagram_shortcode', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $shortcode, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		if ( $ids ) {
			return (int) $ids[0];
		}

		// Older/manual entries may use an equivalent URL with ?igsh, no www,
		// or a different trailing slash. Compare their canonical forms before
		// creating anything so those editor-approved entries are reused. The
		// full legacy set is indexed only once per synchronization request.
		if ( null === self::$canonical_url_index ) {
			self::$canonical_url_index = array();
			$candidates = get_posts(
				array(
					'post_type'      => 'qaf_gallery',
					'post_status'    => $statuses,
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'no_found_rows'  => true,
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array(
							'key'     => '_qaf_video_url',
							'compare' => 'EXISTS',
						),
					),
				)
			);
			foreach ( $candidates as $candidate_id ) {
				$candidate_url = self::canonical_permalink( get_post_meta( $candidate_id, '_qaf_video_url', true ) );
				if ( $candidate_url && ! isset( self::$canonical_url_index[ $candidate_url ] ) ) {
					self::$canonical_url_index[ $candidate_url ] = (int) $candidate_id;
				}
			}
		}

		return isset( self::$canonical_url_index[ $permalink ] ) ? (int) self::$canonical_url_index[ $permalink ] : 0;
	}

	/**
	 * Apply synchronization metadata while respecting editor choices.
	 *
	 * @param int                $post_id   Gallery post ID.
	 * @param string             $media_id  Instagram media ID.
	 * @param string             $permalink Canonical source.
	 * @param DateTimeImmutable|null $date  Local media date.
	 * @return bool
	 */
	private static function set_gallery_meta( $post_id, $media_id, $permalink, $date ) {
		$changed = false;
		$required = array(
			'_qaf_gallery_source'     => 'instagram',
			'_qaf_gallery_media_type' => 'video',
			'_qaf_video_url'          => $permalink,
		);
		if ( (string) get_post_meta( $post_id, '_qaf_instagram_media_id', true ) !== (string) $media_id ) {
			update_post_meta( $post_id, '_qaf_instagram_media_id', $media_id );
			$changed = true;
		}
		$shortcode = self::permalink_shortcode( $permalink );
		if ( (string) get_post_meta( $post_id, '_qaf_instagram_shortcode', true ) !== (string) $shortcode ) {
			update_post_meta( $post_id, '_qaf_instagram_shortcode', $shortcode );
			$changed = true;
		}
		foreach ( $required as $key => $value ) {
			if ( ! metadata_exists( 'post', $post_id, $key ) || '' === get_post_meta( $post_id, $key, true ) ) {
				update_post_meta( $post_id, $key, $value );
				$changed = true;
			}
		}
		if ( ! metadata_exists( 'post', $post_id, '_qaf_gallery_embed_behavior' ) || '' === get_post_meta( $post_id, '_qaf_gallery_embed_behavior', true ) ) {
			update_post_meta( $post_id, '_qaf_gallery_embed_behavior', 'click' );
			$changed = true;
		}
		if ( $date && ( ! metadata_exists( 'post', $post_id, '_qaf_album_date' ) || '' === get_post_meta( $post_id, '_qaf_album_date', true ) ) ) {
			update_post_meta( $post_id, '_qaf_album_date', $date->format( 'Y-m-d' ) );
			$changed = true;
		}
		if ( is_array( self::$canonical_url_index ) ) {
			self::$canonical_url_index[ $permalink ] = (int) $post_id;
		}
		return $changed;
	}

	/**
	 * Copy one bounded official thumbnail into WordPress instead of hotlinking.
	 *
	 * @param mixed               $url      Thumbnail URL.
	 * @param int                 $post_id  Gallery post ID.
	 * @param array<string,mixed> $record   API record.
	 * @param string              $media_id Media ID.
	 * @return int|WP_Error
	 */
	private static function sideload_thumbnail( $url, $post_id, $record, $media_id ) {
		$url = self::sanitize_asset_url( $url );
		if ( '' === $url ) {
			return new WP_Error( 'invalid_thumbnail_host', __( 'Host thumbnail Instagram tidak dikenali.', 'queen-alfalah-core' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$tmp = wp_tempnam( 'qaf-instagram-' . $media_id . '.jpg' );
		if ( ! $tmp ) {
			return new WP_Error( 'thumbnail_temp', __( 'Berkas sementara thumbnail tidak dapat dibuat.', 'queen-alfalah-core' ) );
		}

		$response = wp_safe_remote_get(
			$url,
			array(
				'timeout'             => 25,
				'redirection'         => 0,
				'stream'              => true,
				'filename'            => $tmp,
				'limit_response_size' => self::MAX_THUMBNAIL_BYTES,
			)
		);
		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			wp_delete_file( $tmp );
			return new WP_Error( 'thumbnail_download', __( 'Thumbnail tidak dapat disalin dari Instagram.', 'queen-alfalah-core' ) );
		}

		$length = (int) wp_remote_retrieve_header( $response, 'content-length' );
		$size   = file_exists( $tmp ) ? (int) filesize( $tmp ) : 0;
		if ( $length > self::MAX_THUMBNAIL_BYTES || $size <= 0 || $size >= self::MAX_THUMBNAIL_BYTES ) {
			wp_delete_file( $tmp );
			return new WP_Error( 'thumbnail_size', __( 'Thumbnail kosong atau melebihi batas 8 MB.', 'queen-alfalah-core' ) );
		}

		$content_type = strtolower( trim( (string) wp_remote_retrieve_header( $response, 'content-type' ) ) );
		$content_type = trim( strtok( $content_type, ';' ) );
		$extensions   = array(
			'image/jpeg' => 'jpg',
			'image/png'  => 'png',
			'image/webp' => 'webp',
		);
		if ( ! isset( $extensions[ $content_type ] ) ) {
			wp_delete_file( $tmp );
			return new WP_Error( 'thumbnail_mime', __( 'Format thumbnail Instagram tidak didukung.', 'queen-alfalah-core' ) );
		}

		$file = array(
			'name'     => 'instagram-' . sanitize_file_name( $media_id ) . '.' . $extensions[ $content_type ],
			'tmp_name' => $tmp,
		);
		$attachment_id = media_handle_sideload( $file, $post_id, get_the_title( $post_id ) );
		if ( is_wp_error( $attachment_id ) ) {
			if ( file_exists( $tmp ) ) {
				wp_delete_file( $tmp );
			}
			return $attachment_id;
		}

		$username = isset( $record['username'] ) && is_scalar( $record['username'] ) ? preg_replace( '/[^A-Za-z0-9._]/', '', (string) $record['username'] ) : '';
		$alt      = $username ? sprintf( __( 'Sampul video Instagram @%s', 'queen-alfalah-core' ), $username ) : __( 'Sampul video Instagram SMK Queen Al-Falah', 'queen-alfalah-core' );
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
		set_post_thumbnail( $post_id, $attachment_id );
		return (int) $attachment_id;
	}

	/**
	 * Restrict poster downloads to Meta/Instagram CDN hosts over HTTPS.
	 *
	 * @param mixed $value Raw URL.
	 * @return string
	 */
	private static function sanitize_asset_url( $value ) {
		$url   = esc_url_raw( is_scalar( $value ) ? trim( (string) $value ) : '', array( 'https' ) );
		$parts = $url ? wp_parse_url( $url ) : false;
		if ( ! is_array( $parts ) || empty( $parts['host'] ) || isset( $parts['user'] ) || isset( $parts['pass'] ) ) {
			return '';
		}
		$host = strtolower( rtrim( $parts['host'], '.' ) );
		$port = isset( $parts['port'] ) ? (int) $parts['port'] : null;
		if ( ( null !== $port && 443 !== $port ) || ( ! self::host_has_suffix( $host, 'cdninstagram.com' ) && ! self::host_has_suffix( $host, 'fbcdn.net' ) ) ) {
			return '';
		}
		return $url;
	}

	/**
	 * Exact host or true DNS-label suffix match.
	 *
	 * @param string $host   Candidate hostname.
	 * @param string $suffix Allowed suffix.
	 * @return bool
	 */
	private static function host_has_suffix( $host, $suffix ) {
		return $host === $suffix || substr( $host, -strlen( '.' . $suffix ) ) === '.' . $suffix;
	}

	/**
	 * Refresh a stored long-lived token weekly after its first 24 hours.
	 *
	 * A refresh problem does not undo a successful content import.
	 *
	 * @param array<string,mixed> $settings Current settings.
	 * @return string
	 */
	private static function maybe_refresh_access_token( $settings ) {
		$saved_at   = ! empty( $settings['token_saved_at'] ) ? strtotime( $settings['token_saved_at'] ) : false;
		$refreshed  = ! empty( $settings['last_refresh_at'] ) ? strtotime( $settings['last_refresh_at'] ) : false;
		$now        = time();
		if ( ! $saved_at || $saved_at > $now - 2 * DAY_IN_SECONDS || ( $refreshed && $refreshed > $now - 7 * DAY_IN_SECONDS ) ) {
			return '';
		}

		$url      = add_query_arg(
			array(
				'grant_type'   => 'ig_refresh_token',
				'access_token' => $settings['access_token'],
			),
			self::API_ROOT . '/refresh_access_token'
		);
		$response = wp_safe_remote_get( $url, array( 'timeout' => 20, 'redirection' => 0, 'limit_response_size' => 65536 ) );
		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return __( 'Konten berhasil, tetapi masa berlaku token belum dapat diperbarui.', 'queen-alfalah-core' );
		}
		$data  = json_decode( wp_remote_retrieve_body( $response ), true );
		$token = is_array( $data ) && isset( $data['access_token'] ) ? self::sanitize_access_token( $data['access_token'] ) : '';
		if ( '' === $token ) {
			return __( 'Konten berhasil, tetapi respons pembaruan token tidak valid.', 'queen-alfalah-core' );
		}

		// Do not resurrect a token or roll back settings if an administrator
		// changed/disconnected the account while this remote request was active.
		$latest = self::get_settings();
		if ( empty( $latest['access_token'] ) || ! hash_equals( (string) $settings['access_token'], (string) $latest['access_token'] ) ) {
			return '';
		}
		$latest['access_token']    = $token;
		$latest['token_saved_at']  = gmdate( 'c' );
		$latest['last_refresh_at'] = gmdate( 'c' );
		$latest                    = self::normalize_settings( $latest );
		update_option( self::OPTION_NAME, $latest, false );
		self::reconcile_schedule( $latest );
		return '';
	}

	/**
	 * Convert API failures into actionable messages without exposing secrets.
	 *
	 * @param int   $http_code HTTP status.
	 * @param mixed $data      Decoded body.
	 * @return WP_Error
	 */
	private static function api_error( $http_code, $data ) {
		$api_code = is_array( $data ) && isset( $data['error']['code'] ) ? (int) $data['error']['code'] : 0;
		if ( 190 === $api_code ) {
			$message = __( 'Token Instagram kedaluwarsa atau dicabut. Buat long-lived token baru lalu hubungkan kembali.', 'queen-alfalah-core' );
		} elseif ( 200 === $api_code ) {
			$message = __( 'Aplikasi Meta belum memiliki izin instagram_business_basic untuk akun ini.', 'queen-alfalah-core' );
		} elseif ( 100 === $api_code ) {
			$message = __( 'Instagram User ID atau parameter API tidak cocok. Periksa konfigurasi akun profesional.', 'queen-alfalah-core' );
		} elseif ( 4 === $api_code || 17 === $api_code || 32 === $api_code || 429 === $http_code ) {
			$message = __( 'Batas permintaan Instagram sementara tercapai. Sinkronisasi berikutnya akan mencoba kembali.', 'queen-alfalah-core' );
		} else {
			$message = sprintf( __( 'Instagram API menolak permintaan (HTTP %d). Coba lagi atau periksa aplikasi Meta.', 'queen-alfalah-core' ), $http_code );
		}
		return new WP_Error( 'instagram_api_' . ( $api_code ? $api_code : $http_code ), $message );
	}

	/**
	 * Build a readable title from the first caption line.
	 *
	 * @param string                 $caption Caption.
	 * @param DateTimeImmutable|null $date    Local date.
	 * @return string
	 */
	private static function record_title( $caption, $date ) {
		$lines = preg_split( '/\R/u', trim( $caption ) );
		$first = $lines && isset( $lines[0] ) ? sanitize_text_field( $lines[0] ) : '';
		$first = preg_replace( '/(?:\s+#\S+)+\s*$/u', '', $first );
		$title = wp_trim_words( $first, 14, '…' );
		if ( '' !== trim( $title ) ) {
			return $title;
		}
		return $date ? sprintf( __( 'Video Instagram — %s', 'queen-alfalah-core' ), wp_date( get_option( 'date_format' ), $date->getTimestamp(), wp_timezone() ) ) : __( 'Video Instagram SMK Queen Al-Falah', 'queen-alfalah-core' );
	}

	/**
	 * Parse an API timestamp into the configured school timezone.
	 *
	 * @param mixed $value Timestamp.
	 * @return DateTimeImmutable|null
	 */
	private static function parse_timestamp( $value ) {
		if ( ! is_scalar( $value ) || '' === trim( (string) $value ) ) {
			return null;
		}
		try {
			$date = new DateTimeImmutable( (string) $value );
			return $date->setTimezone( wp_timezone() );
		} catch ( Exception $error ) {
			return null;
		}
	}

	/**
	 * Extract a canonical shortcode.
	 *
	 * @param string $permalink Canonical URL.
	 * @return string
	 */
	private static function permalink_shortcode( $permalink ) {
		$path = wp_parse_url( $permalink, PHP_URL_PATH );
		if ( is_string( $path ) && preg_match( '#^/(?:p|reel|tv)/([A-Za-z0-9_-]+)/?$#', $path, $matches ) ) {
			return $matches[1];
		}
		return substr( sha1( $permalink ), 0, 16 );
	}

	/**
	 * Normalize stored values and keep the secret out of autoloaded settings.
	 *
	 * @param array<string,mixed> $settings Raw settings.
	 * @return array<string,mixed>
	 */
	private static function normalize_settings( $settings ) {
		$defaults = self::get_defaults();
		$settings = is_array( $settings ) ? wp_parse_args( $settings, $defaults ) : $defaults;
		return array(
			'user_id'             => preg_replace( '/[^0-9]/', '', (string) $settings['user_id'] ),
			'access_token'        => self::sanitize_access_token( $settings['access_token'] ),
			'auto_sync'           => empty( $settings['auto_sync'] ) ? 0 : 1,
			'post_status'         => 'publish' === $settings['post_status'] ? 'publish' : 'draft',
			'max_items'           => max( 1, min( 100, absint( $settings['max_items'] ) ) ),
			'download_thumbnails' => empty( $settings['download_thumbnails'] ) ? 0 : 1,
			'token_saved_at'      => self::sanitize_iso_time( $settings['token_saved_at'] ),
			'last_refresh_at'     => self::sanitize_iso_time( $settings['last_refresh_at'] ),
		);
	}

	/**
	 * Accept opaque printable tokens while rejecting whitespace/control data.
	 *
	 * @param mixed $value Raw token.
	 * @return string
	 */
	private static function sanitize_access_token( $value ) {
		$token = is_scalar( $value ) ? trim( (string) $value ) : '';
		if ( '' === $token || strlen( $token ) > 4096 || preg_match( '/[\x00-\x20\x7F]/', $token ) ) {
			return '';
		}
		return $token;
	}

	/**
	 * Retain only parseable ISO timestamps.
	 *
	 * @param mixed $value Raw timestamp.
	 * @return string
	 */
	private static function sanitize_iso_time( $value ) {
		$value = is_scalar( $value ) ? trim( (string) $value ) : '';
		return $value && false !== strtotime( $value ) ? $value : '';
	}

	/**
	 * Acquire an atomic option-backed lock for a maximum of one hour.
	 *
	 * WordPress options have a unique name at the database layer, so concurrent
	 * requests cannot both win add_option(). An expired owner may be replaced.
	 *
	 * @return string|WP_Error Owner token or busy error.
	 */
	private static function acquire_lock() {
		$owner = wp_generate_uuid4();
		$lock  = array(
			'owner'   => $owner,
			'expires' => time() + HOUR_IN_SECONDS,
		);
		if ( add_option( self::LOCK_KEY, $lock, '', false ) ) {
			return $owner;
		}

		$current = get_option( self::LOCK_KEY, array() );
		$expires = is_array( $current ) && isset( $current['expires'] ) ? (int) $current['expires'] : 0;
		if ( $expires > time() ) {
			return new WP_Error( 'sync_busy', __( 'Sinkronisasi lain masih berjalan.', 'queen-alfalah-core' ) );
		}

		delete_option( self::LOCK_KEY );
		return add_option( self::LOCK_KEY, $lock, '', false )
			? $owner
			: new WP_Error( 'sync_busy', __( 'Sinkronisasi lain baru saja dimulai.', 'queen-alfalah-core' ) );
	}

	/**
	 * Extend the lock while a large poster batch is being processed.
	 *
	 * @param string $owner Owner token.
	 * @return void
	 */
	private static function refresh_lock( $owner ) {
		$current = get_option( self::LOCK_KEY, array() );
		if ( ! is_array( $current ) || empty( $current['owner'] ) || ! hash_equals( (string) $current['owner'], (string) $owner ) ) {
			return;
		}
		$current['expires'] = time() + HOUR_IN_SECONDS;
		update_option( self::LOCK_KEY, $current, false );
	}

	/**
	 * Release only the lock owned by this request.
	 *
	 * @param string $owner Owner token.
	 * @return void
	 */
	private static function release_lock( $owner ) {
		$current = get_option( self::LOCK_KEY, array() );
		if ( is_array( $current ) && ! empty( $current['owner'] ) && hash_equals( (string) $current['owner'], (string) $owner ) ) {
			delete_option( self::LOCK_KEY );
		}
	}

	/**
	 * Maintain exactly one daily event when the connection is ready.
	 *
	 * @param array<string,mixed> $settings Connection settings.
	 * @return void
	 */
	private static function reconcile_schedule( $settings ) {
		$ready = ! empty( $settings['auto_sync'] ) && ! empty( $settings['user_id'] ) && ! empty( $settings['access_token'] );
		$next  = wp_next_scheduled( self::CRON_HOOK );
		if ( $ready && ! $next ) {
			wp_schedule_event( time() + 5 * MINUTE_IN_SECONDS, 'daily', self::CRON_HOOK );
		} elseif ( ! $ready && $next ) {
			wp_clear_scheduled_hook( self::CRON_HOOK );
		}
	}

	/**
	 * Store a concise success summary with no token or raw API payload.
	 *
	 * @param array<string,int> $counts          Counters.
	 * @param string            $source          Trigger source.
	 * @param string            $warning Optional warning.
	 * @return void
	 */
	private static function record_success( $counts, $source, $warning = '' ) {
		$state = get_option( self::STATE_OPTION, array() );
		$state = is_array( $state ) ? $state : array();
		$now   = gmdate( 'c' );
		$message = sprintf(
			__( 'Diperiksa %1$d postingan; %2$d video; dibuat %3$d; dilengkapi %4$d; sudah ada/dilewati %5$d; gagal disimpan %6$d; poster gagal %7$d.', 'queen-alfalah-core' ),
			$counts['checked'],
			$counts['videos'],
			$counts['created'],
			$counts['updated'],
			$counts['skipped'],
			$counts['errors'],
			$counts['thumbnail_errors']
		);
		if ( $warning ) {
			$message .= ' ' . $warning;
		}
		$state = array(
			'attempted_at' => $now,
			'succeeded_at' => $now,
			'source'       => sanitize_key( $source ),
			'status'       => $warning ? 'warning' : 'success',
			'message'      => sanitize_text_field( $message ),
		);
		update_option( self::STATE_OPTION, $state, false );
	}

	/**
	 * Store a redacted failure summary and preserve last success time.
	 *
	 * @param WP_Error $error  Failure.
	 * @param string   $source Trigger source.
	 * @return void
	 */
	private static function record_failure( $error, $source ) {
		$state = get_option( self::STATE_OPTION, array() );
		$state = is_array( $state ) ? $state : array();
		$state['attempted_at'] = gmdate( 'c' );
		$state['source']       = sanitize_key( $source );
		$state['status']       = 'error';
		$state['message']      = sanitize_text_field( $error->get_error_message() );
		update_option( self::STATE_OPTION, $state, false );
	}

	/**
	 * Format UTC status timestamps in the configured timezone.
	 *
	 * @param mixed $value ISO timestamp.
	 * @return string
	 */
	private static function format_state_time( $value ) {
		$timestamp = is_scalar( $value ) ? strtotime( (string) $value ) : false;
		return $timestamp ? wp_date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $timestamp, wp_timezone() ) : __( 'Belum ada', 'queen-alfalah-core' );
	}

	/**
	 * Require an authenticated administration POST.
	 *
	 * @param string $nonce_action Expected nonce action.
	 * @return void
	 */
	private static function authorize_post( $nonce_action ) {
		if ( 'POST' !== strtoupper( isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '' ) ) {
			wp_die( esc_html__( 'Metode permintaan tidak diizinkan.', 'queen-alfalah-core' ), '', array( 'response' => 405 ) );
		}
		if ( ! current_user_can( QAF_Core_Settings::get_capability() ) ) {
			wp_die( esc_html__( 'Anda tidak memiliki izin untuk tindakan ini.', 'queen-alfalah-core' ), '', array( 'response' => 403 ) );
		}
		$nonce = isset( $_POST['qaf_instagram_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['qaf_instagram_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
			self::redirect_with_notice( 'invalid_request' );
		}
	}

	/**
	 * Return to the fixed plugin page using only a notice code.
	 *
	 * @param string $notice Notice code.
	 * @return void
	 */
	private static function redirect_with_notice( $notice ) {
		wp_safe_redirect( add_query_arg( 'qaf_notice', sanitize_key( $notice ), admin_url( 'admin.php?page=qaf-instagram-gallery' ) ) );
		exit;
	}

}
