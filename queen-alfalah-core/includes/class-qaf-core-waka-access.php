<?php
/**
 * Least-privilege Waka access and opt-in account provisioning.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give Wakil Kepala Sekolah a focused announcement workflow.
 */
final class QAF_Core_Waka_Access {
	const ROLE                       = 'qaf_waka';
	const SOURCE_META                = '_qaf_waka_source_id';
	const FORCE_PASSWORD_META        = '_qaf_force_password_change';
	const VERSION_OPTION             = 'qaf_waka_access_schema_version';
	const SCHEMA_VERSION             = '1.0.0';
	const MENU_SLUG                  = 'qaf-waka-accounts';
	const PROVISION_ACTION           = 'qaf_provision_waka_accounts';
	const PROVISION_NONCE_ACTION     = 'qaf_provision_waka_accounts_once';

	/**
	 * Attach runtime and administrator hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_upgrade' ), 25 );
		add_action( 'admin_init', array( __CLASS__, 'enforce_password_change' ), 35 );
		add_action( 'admin_menu', array( __CLASS__, 'register_menus' ), 35 );
		add_action( 'admin_post_' . self::PROVISION_ACTION, array( __CLASS__, 'handle_provision' ) );
		add_action( 'profile_update', array( __CLASS__, 'maybe_clear_password_flag' ), 20, 1 );
		add_action( 'after_password_reset', array( __CLASS__, 'clear_password_flag_after_reset' ), 10, 1 );
		add_action( 'admin_notices', array( __CLASS__, 'render_password_notice' ) );
		add_filter( 'login_redirect', array( __CLASS__, 'login_redirect' ), 20, 3 );
		add_filter( 'user_has_cap', array( __CLASS__, 'restrict_temporary_password' ), 20, 4 );
	}

	/** Enforce first-login restrictions on REST/AJAX as well as dashboard pages. */
	public static function restrict_temporary_password( $allcaps, $caps, $args, $user ) {
		if ( ! self::is_waka_user( $user ) || ! empty( $allcaps['manage_options'] ) || ! get_user_meta( $user->ID, self::FORCE_PASSWORD_META, true ) ) {
			return $allcaps;
		}
		foreach ( self::get_waka_capabilities() as $capability ) {
			if ( 'read' !== $capability ) { $allcaps[ $capability ] = false; }
		}
		return $allcaps;
	}

	/**
	 * Install or update the role capabilities. No user account is created here.
	 *
	 * @return void
	 */
	public static function activate() {
		self::install_capabilities();
		update_option( self::VERSION_OPTION, self::SCHEMA_VERSION, false );
	}

	/**
	 * Upgrade capabilities only from an authenticated administrator request.
	 *
	 * @return void
	 */
	public static function maybe_upgrade() {
		if ( self::SCHEMA_VERSION === get_option( self::VERSION_OPTION ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::activate();
	}

	/**
	 * Return the exact capabilities required by Waka accounts.
	 *
	 * @return array<int,string>
	 */
	public static function get_waka_capabilities() {
		return array(
			'read',
			QAF_Core_Media_Center::CAPABILITY,
			'upload_files',
			'edit_qaf_notices',
			'edit_others_qaf_notices',
			'publish_qaf_notices',
			'read_private_qaf_notices',
			'edit_private_qaf_notices',
			'edit_published_qaf_notices',
			'assign_qaf_notice_terms',
			'assign_qaf_audience_terms',
		);
	}

	/**
	 * Add dedicated capabilities without granting generic Post/Page access.
	 *
	 * Built-in editorial roles receive equivalent notice capabilities so this
	 * migration does not unexpectedly remove access they already had while
	 * qaf_notice used the generic post capability map.
	 *
	 * @return void
	 */
	public static function install_capabilities() {
		$waka = get_role( self::ROLE );
		if ( ! $waka ) {
			add_role(
				self::ROLE,
				'Waka Sekolah',
				array(
					'read'                              => true,
					QAF_Core_Media_Center::CAPABILITY   => true,
				)
			);
			$waka = get_role( self::ROLE );
		}

		if ( $waka ) {
			foreach ( self::get_waka_capabilities() as $capability ) {
				$waka->add_cap( $capability );
			}

			// These generic capabilities are intentionally excluded: they would
			// expose Berita, Halaman, or site administration outside the request.
			foreach ( self::get_disallowed_waka_capabilities() as $capability ) {
				if ( $waka->has_cap( $capability ) ) {
					$waka->remove_cap( $capability );
				}
			}
		}

		self::preserve_builtin_editor_access();
	}

	/**
	 * Return generic privileges that must never be carried by the Waka role.
	 *
	 * @return array<int,string>
	 */
	private static function get_disallowed_waka_capabilities() {
		return array(
			'manage_options',
			'edit_posts',
			'edit_others_posts',
			'edit_published_posts',
			'publish_posts',
			'delete_posts',
			'delete_others_posts',
			'delete_published_posts',
			'edit_pages',
			'edit_others_pages',
			'edit_published_pages',
			'publish_pages',
			'delete_pages',
			'edit_users',
			'create_users',
			'delete_users',
			'promote_users',
			'list_users',
			'activate_plugins',
			'install_plugins',
			'update_plugins',
			'delete_plugins',
			'switch_themes',
			'edit_theme_options',
			'update_core',
			'import',
			'export',
		);
	}

	/**
	 * Mirror the previous generic-post permissions for WordPress built-in roles.
	 *
	 * @return void
	 */
	private static function preserve_builtin_editor_access() {
		$primitive_map = array(
			'edit_posts'             => 'edit_qaf_notices',
			'edit_others_posts'      => 'edit_others_qaf_notices',
			'publish_posts'          => 'publish_qaf_notices',
			'read_private_posts'     => 'read_private_qaf_notices',
			'delete_posts'           => 'delete_qaf_notices',
			'delete_private_posts'   => 'delete_private_qaf_notices',
			'delete_published_posts' => 'delete_published_qaf_notices',
			'delete_others_posts'    => 'delete_others_qaf_notices',
			'edit_private_posts'     => 'edit_private_qaf_notices',
			'edit_published_posts'   => 'edit_published_qaf_notices',
		);

		foreach ( array( 'administrator', 'editor', 'author', 'contributor' ) as $role_key ) {
			$role = get_role( $role_key );
			if ( ! $role ) {
				continue;
			}

			foreach ( $primitive_map as $generic => $dedicated ) {
				if ( $role->has_cap( $generic ) ) {
					$role->add_cap( $dedicated );
				}
			}

			if ( $role->has_cap( 'edit_posts' ) ) {
				$role->add_cap( 'assign_qaf_notice_terms' );
				$role->add_cap( 'assign_qaf_audience_terms' );
			}

			if ( $role->has_cap( 'manage_categories' ) ) {
				foreach (
					array(
						'manage_qaf_notice_terms',
						'edit_qaf_notice_terms',
						'delete_qaf_notice_terms',
						'manage_qaf_audience_terms',
						'edit_qaf_audience_terms',
						'delete_qaf_audience_terms',
					) as $term_capability
				) {
					$role->add_cap( $term_capability );
				}
			}
		}

		$administrator = get_role( 'administrator' );
		if ( $administrator ) {
			foreach ( QAF_Core_Post_Types::get_notice_capabilities() as $capability ) {
				$administrator->add_cap( $capability );
			}
		}
	}

	/**
	 * Add a focused Waka menu and the administrator-only provisioner.
	 *
	 * @return void
	 */
	public static function register_menus() {
		if ( current_user_can( 'edit_qaf_notices' ) && ! current_user_can( 'manage_options' ) ) {
			add_menu_page(
				'Pengumuman Sekolah',
				'Pengumuman',
				'edit_qaf_notices',
				'edit.php?post_type=qaf_notice',
				'',
				'dashicons-megaphone',
				25
			);
		}

		add_submenu_page(
			'qaf-school',
			'Akun Wakil Kepala Sekolah',
			'Akun Waka',
			'manage_options',
			self::MENU_SLUG,
			array( __CLASS__, 'render_provision_page' )
		);
	}

	/**
	 * Stable account definitions sourced from the school organization structure.
	 *
	 * No email address or password is bundled in source control.
	 *
	 * @return array<int,array<string,string>>
	 */
	public static function get_account_definitions() {
		return array(
			array(
				'source_id'    => 'waka:kurikulum:moh-saean-marzuki',
				'username'     => 'waka-kurikulum',
				'display_name' => 'Moh. Saean Marzuki, S.Pd.',
				'unit'         => 'Wakil Kepala Sekolah Bidang Kurikulum',
			),
			array(
				'source_id'    => 'waka:kesiswaan:efi-risca-ferbriana-wati',
				'username'     => 'waka-kesiswaan',
				'display_name' => 'Efi Risca Ferbriana Wati, S.Pd.',
				'unit'         => 'Wakil Kepala Sekolah Bidang Kesiswaan',
			),
			array(
				'source_id'    => 'waka:sarpras:nur-ahmad-fatihin',
				'username'     => 'waka-sarpras',
				'display_name' => 'Nur Ahmad Fatihin, S.Pd.',
				'unit'         => 'Wakil Kepala Sekolah Bidang Sarana dan Prasarana',
			),
			array(
				'source_id'    => 'waka:hubin-humas:ahmad-sirojudin',
				'username'     => 'waka-hubin-humas',
				'display_name' => 'Ahmad Sirojudin, S.Pd.I.',
				'unit'         => 'Wakil Kepala Sekolah Bidang Hubungan Industri dan Hubungan Masyarakat',
			),
		);
	}

	/**
	 * Render a read-only preview and explicit provisioning control.
	 *
	 * @return void
	 */
	public static function render_provision_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Anda tidak memiliki izin untuk mengakses halaman ini.', 'queen-alfalah-core' ) );
		}

		$missing = 0;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Akun Wakil Kepala Sekolah', 'queen-alfalah-core' ); ?></h1>
			<p><?php esc_html_e( 'Alat ini menyiapkan empat akun Waka dari Struktur Organisasi 2026/2027. Akun tidak dibuat sampai tombol di bawah ditekan.', 'queen-alfalah-core' ); ?></p>
			<div class="notice notice-info inline"><p><?php esc_html_e( 'Setiap akun hanya dapat mengunggah ke Media Library serta membuat, menyunting, dan menerbitkan Pengumuman. Guru dan Tendik tetap diarahkan ke Pusat Media tanpa dashboard.', 'queen-alfalah-core' ); ?></p></div>
			<table class="widefat striped">
				<thead><tr><th><?php esc_html_e( 'Nama', 'queen-alfalah-core' ); ?></th><th><?php esc_html_e( 'Username tetap', 'queen-alfalah-core' ); ?></th><th><?php esc_html_e( 'Unit/Jabatan', 'queen-alfalah-core' ); ?></th><th><?php esc_html_e( 'Status', 'queen-alfalah-core' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( self::get_account_definitions() as $definition ) : ?>
					<?php $existing = self::find_existing_account( $definition ); ?>
					<?php if ( ! $existing ) { ++$missing; } ?>
					<tr>
						<td><?php echo esc_html( $definition['display_name'] ); ?></td>
						<td><code><?php echo esc_html( $definition['username'] ); ?></code></td>
						<td><?php echo esc_html( $definition['unit'] ); ?></td>
						<td><?php echo $existing ? esc_html__( 'Sudah ada — tidak akan diubah', 'queen-alfalah-core' ) : esc_html__( 'Siap dibuat', 'queen-alfalah-core' ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ( $missing ) : ?>
				<p><?php esc_html_e( 'Password acak 32 karakter dibuat oleh WordPress dan hanya ditampilkan sekali setelah proses selesai. Pengguna wajib menggantinya saat pertama masuk.', 'queen-alfalah-core' ); ?></p>
				<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<input type="hidden" name="action" value="<?php echo esc_attr( self::PROVISION_ACTION ); ?>">
					<?php wp_nonce_field( self::PROVISION_NONCE_ACTION ); ?>
					<?php submit_button( sprintf( __( 'Buat %d Akun yang Belum Ada', 'queen-alfalah-core' ), $missing ) ); ?>
				</form>
			<?php else : ?>
				<p><strong><?php esc_html_e( 'Semua akun terdeteksi. Tidak ada akun yang perlu dibuat.', 'queen-alfalah-core' ); ?></strong></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Process the fixed account list via POST only.
	 *
	 * @return void
	 */
	public static function handle_provision() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html__( 'Anda tidak memiliki izin untuk membuat akun Waka.', 'queen-alfalah-core' ),
				esc_html__( 'Akses ditolak', 'queen-alfalah-core' ),
				array( 'response' => 403 )
			);
		}

		$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '';
		if ( 'POST' !== $request_method ) {
			wp_die(
				esc_html__( 'Metode permintaan tidak valid.', 'queen-alfalah-core' ),
				esc_html__( 'Permintaan tidak valid', 'queen-alfalah-core' ),
				array( 'response' => 405 )
			);
		}

		check_admin_referer( self::PROVISION_NONCE_ACTION );
		$result = self::provision_accounts();
		self::render_provision_result( $result );
	}

	/**
	 * Create only missing accounts and return plaintext passwords once.
	 *
	 * @return array<string,array<int,mixed>>|WP_Error
	 */
	public static function provision_accounts() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'qaf_waka_forbidden', 'Hanya administrator yang dapat membuat akun Waka.' );
		}

		self::install_capabilities();
		$result = array(
			'created' => array(),
			'skipped' => array(),
			'errors'  => array(),
		);

		foreach ( self::get_account_definitions() as $definition ) {
			$existing = self::find_existing_account( $definition );
			if ( $existing ) {
				$result['skipped'][] = array(
					'username'     => $definition['username'],
					'display_name' => $definition['display_name'],
					'existing_id'  => (int) $existing->ID,
					'existing_login' => (string) $existing->user_login,
				);
				continue;
			}

			$password = wp_generate_password( 32, true, true );
			$user_id  = wp_insert_user(
				array(
					'user_login'   => $definition['username'],
					'user_pass'    => $password,
					'display_name' => $definition['display_name'],
					'nickname'     => $definition['display_name'],
					'role'         => self::ROLE,
				)
			);

			if ( is_wp_error( $user_id ) ) {
				$result['errors'][] = array(
					'username' => $definition['username'],
					'message'  => $user_id->get_error_message(),
				);
				continue;
			}

			update_user_meta( $user_id, self::SOURCE_META, $definition['source_id'] );
			update_user_meta( $user_id, QAF_Core_Media_Center::UNIT_META, $definition['unit'] );
			update_user_meta( $user_id, self::FORCE_PASSWORD_META, '1' );
			update_user_option( $user_id, 'default_password_nag', true, true );

			$result['created'][] = array(
				'user_id'      => (int) $user_id,
				'username'     => $definition['username'],
				'password'     => $password,
				'display_name' => $definition['display_name'],
				'unit'         => $definition['unit'],
			);
		}

		return $result;
	}

	/**
	 * Locate an account without modifying it.
	 *
	 * Username and the private source marker are authoritative. An exact
	 * normalized display-name match also blocks creation, preventing a duplicate
	 * when an older username is in use.
	 *
	 * @param array<string,string> $definition Account definition.
	 * @return WP_User|null
	 */
	private static function find_existing_account( $definition ) {
		$user_id = username_exists( $definition['username'] );
		if ( $user_id ) {
			$user = get_userdata( $user_id );
			return $user instanceof WP_User ? $user : null;
		}

		$wanted = self::normalize_person_name( $definition['display_name'] );
		foreach ( get_users( array( 'fields' => 'all' ) ) as $user ) {
			if ( ! $user instanceof WP_User ) {
				continue;
			}

			if ( $definition['source_id'] === get_user_meta( $user->ID, self::SOURCE_META, true ) ) {
				return $user;
			}

			if ( $wanted === self::normalize_person_name( $user->display_name ) ) {
				return $user;
			}
		}

		return null;
	}

	/**
	 * Normalize a public display name while ignoring academic suffixes.
	 *
	 * @param string $name Display name.
	 * @return string
	 */
	private static function normalize_person_name( $name ) {
		$name = preg_replace( '/,.*$/u', '', (string) $name );
		$name = strtolower( remove_accents( (string) $name ) );
		$name = preg_replace( '/[^a-z0-9]+/', ' ', $name );
		return trim( preg_replace( '/\s+/', ' ', (string) $name ) );
	}

	/**
	 * Display generated passwords directly, without a database transient or URL.
	 *
	 * @param array<string,array<int,mixed>>|WP_Error $result Provisioning result.
	 * @return void
	 */
	private static function render_provision_result( $result ) {
		nocache_headers();
		if ( ! headers_sent() ) {
			header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
			header( 'Referrer-Policy: no-referrer' );
		}

		$back_url = admin_url( 'admin.php?page=' . self::MENU_SLUG );
		$html     = '<p><a href="' . esc_url( $back_url ) . '">&larr; ' . esc_html__( 'Kembali ke daftar akun Waka', 'queen-alfalah-core' ) . '</a></p>';

		if ( is_wp_error( $result ) ) {
			$html .= '<p>' . esc_html( $result->get_error_message() ) . '</p>';
			wp_die( $html, esc_html__( 'Akun Waka', 'queen-alfalah-core' ), array( 'response' => 403 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		if ( ! empty( $result['created'] ) ) {
			$html .= '<h2>' . esc_html__( 'Simpan kredensial ini sekarang', 'queen-alfalah-core' ) . '</h2>';
			$html .= '<p><strong>' . esc_html__( 'Password tidak disimpan dalam laporan dan tidak dapat ditampilkan lagi setelah halaman ini ditutup. Berikan setiap kredensial hanya kepada pemilik akun.', 'queen-alfalah-core' ) . '</strong></p>';
			$html .= '<table><thead><tr><th>' . esc_html__( 'Nama', 'queen-alfalah-core' ) . '</th><th>' . esc_html__( 'Username', 'queen-alfalah-core' ) . '</th><th>' . esc_html__( 'Password sementara', 'queen-alfalah-core' ) . '</th></tr></thead><tbody>';
			foreach ( $result['created'] as $account ) {
				$html .= '<tr><td>' . esc_html( $account['display_name'] ) . '</td><td><code>' . esc_html( $account['username'] ) . '</code></td><td><code>' . esc_html( $account['password'] ) . '</code></td></tr>';
			}
			$html .= '</tbody></table><p>' . esc_html__( 'Setiap pengguna akan diarahkan untuk mengganti password sementara saat pertama masuk.', 'queen-alfalah-core' ) . '</p>';
		} else {
			$html .= '<p><strong>' . esc_html__( 'Tidak ada akun baru yang dibuat.', 'queen-alfalah-core' ) . '</strong> ' . esc_html__( 'Semua username atau nama personel sudah terdeteksi dan tidak diubah.', 'queen-alfalah-core' ) . '</p>';
		}

		if ( ! empty( $result['skipped'] ) ) {
			$html .= '<p>' . esc_html( sprintf( __( '%d akun dilewati karena sudah ada.', 'queen-alfalah-core' ), count( $result['skipped'] ) ) ) . '</p>';
		}
		if ( ! empty( $result['errors'] ) ) {
			$html .= '<h2>' . esc_html__( 'Galat', 'queen-alfalah-core' ) . '</h2><ul>';
			foreach ( $result['errors'] as $error ) {
				$html .= '<li><code>' . esc_html( $error['username'] ) . '</code>: ' . esc_html( $error['message'] ) . '</li>';
			}
			$html .= '</ul>';
		}

		wp_die( $html, esc_html__( 'Akun Waka selesai diproses', 'queen-alfalah-core' ), array( 'response' => 200 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Whether a WordPress user is assigned the dedicated Waka role.
	 *
	 * @param WP_User|mixed $user User object.
	 * @return bool
	 */
	public static function is_waka_user( $user ) {
		return $user instanceof WP_User && in_array( self::ROLE, (array) $user->roles, true );
	}

	/**
	 * Send Waka users to the correct first screen after login.
	 *
	 * @param string           $redirect_to           Default destination.
	 * @param string           $requested_redirect_to Requested destination.
	 * @param WP_User|WP_Error $user                  Authenticated user.
	 * @return string
	 */
	public static function login_redirect( $redirect_to, $requested_redirect_to, $user ) {
		unset( $requested_redirect_to );

		if ( ! self::is_waka_user( $user ) || user_can( $user, 'manage_options' ) ) {
			return $redirect_to;
		}

		if ( get_user_meta( $user->ID, self::FORCE_PASSWORD_META, true ) ) {
			return admin_url( 'profile.php?qaf-password=change' );
		}

		return admin_url( 'edit.php?post_type=qaf_notice' );
	}

	/**
	 * Keep a provisioned Waka on the profile page until the password is changed.
	 *
	 * @return void
	 */
	public static function enforce_password_change() {
		if ( ! is_user_logged_in() || current_user_can( 'manage_options' ) ) {
			return;
		}

		$user = wp_get_current_user();
		if ( ! self::is_waka_user( $user ) || ! get_user_meta( $user->ID, self::FORCE_PASSWORD_META, true ) ) {
			return;
		}

		global $pagenow;
		if ( in_array( $pagenow, array( 'profile.php', 'admin-ajax.php' ), true ) ) {
			return;
		}

		wp_safe_redirect( admin_url( 'profile.php?qaf-password=change' ) );
		exit;
	}

	/**
	 * Explain the mandatory first-login password change.
	 *
	 * @return void
	 */
	public static function render_password_notice() {
		$user = wp_get_current_user();
		if ( self::is_waka_user( $user ) && get_user_meta( $user->ID, self::FORCE_PASSWORD_META, true ) ) {
			echo '<div class="notice notice-warning"><p><strong>' . esc_html__( 'Keamanan akun:', 'queen-alfalah-core' ) . '</strong> ' . esc_html__( 'Ganti password sementara pada bagian Manajemen Akun, lalu simpan profil untuk membuka Media dan Pengumuman.', 'queen-alfalah-core' ) . '</p></div>';
		}
	}

	/**
	 * Clear the first-login marker only after WordPress saved the posted password.
	 *
	 * @param int $user_id Updated user ID.
	 * @return void
	 */
	public static function maybe_clear_password_flag( $user_id ) {
		if ( ! get_user_meta( $user_id, self::FORCE_PASSWORD_META, true ) || empty( $_POST['pass1'] ) ) {
			return;
		}

		$user     = get_userdata( $user_id );
		$password = (string) wp_unslash( $_POST['pass1'] );
		if ( $user instanceof WP_User && self::is_waka_user( $user ) && wp_check_password( $password, $user->user_pass, $user_id ) ) {
			delete_user_meta( $user_id, self::FORCE_PASSWORD_META );
			delete_user_option( $user_id, 'default_password_nag', true );
		}
	}

	/**
	 * A successful lost-password flow also satisfies the password-change rule.
	 *
	 * @param WP_User $user Reset user.
	 * @return void
	 */
	public static function clear_password_flag_after_reset( $user ) {
		if ( self::is_waka_user( $user ) ) {
			delete_user_meta( $user->ID, self::FORCE_PASSWORD_META );
			delete_user_option( $user->ID, 'default_password_nag', true );
		}
	}
}
