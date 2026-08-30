<?php
/**
 * Private school media center with one editable Google Drive link per division.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register school roles, provision the portal, and enforce division access.
 */
final class QAF_Core_Media_Center {
	const CAPABILITY           = 'qaf_access_media_center';
	const UNIT_META            = '_qaf_school_unit';
	const VERSION_OPTION       = 'qaf_media_center_schema_version';
	const SCHEMA_VERSION       = '2.0.0';
	const PAGE_OPTION          = 'qaf_media_center_page_id';
	const PROFILE_NONCE        = 'qaf_media_profile_nonce';
	const PROFILE_NONCE_ACTION = 'qaf_save_media_profile';

	/**
	 * Register runtime hooks.
	 *
	 * Pusat Media intentionally has no upload, download, OAuth, or Drive API
	 * hooks. WordPress only authenticates the user and reveals the configured
	 * link for that user's division.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_upgrade' ) );
		add_action( 'admin_init', array( __CLASS__, 'restrict_portal_users' ), 20 );
		add_shortcode( 'qaf_media_center', array( __CLASS__, 'render_shortcode' ) );
		add_action( 'show_user_profile', array( __CLASS__, 'render_user_fields' ) );
		add_action( 'edit_user_profile', array( __CLASS__, 'render_user_fields' ) );
		add_action( 'user_new_form', array( __CLASS__, 'render_new_user_fields' ) );
		add_action( 'personal_options_update', array( __CLASS__, 'save_user_fields' ) );
		add_action( 'edit_user_profile_update', array( __CLASS__, 'save_user_fields' ) );
		add_action( 'user_register', array( __CLASS__, 'save_new_user_fields' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_filter( 'login_redirect', array( __CLASS__, 'login_redirect' ), 10, 3 );
		add_filter( 'show_admin_bar', array( __CLASS__, 'show_admin_bar' ) );
		add_filter( 'wp_robots', array( __CLASS__, 'robots' ) );
	}

	/**
	 * Install roles and portal content during plugin activation.
	 *
	 * @return void
	 */
	public static function activate() {
		self::install_roles();
		$page_id = self::ensure_page();

		// A failed page write must remain retryable on a later admin request.
		if ( ! is_wp_error( $page_id ) && $page_id ) {
			update_option( self::VERSION_OPTION, self::SCHEMA_VERSION, false );
		}
	}

	/**
	 * Apply non-destructive upgrades to existing installations.
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
	 * Add dedicated roles without replacing existing role configuration.
	 *
	 * @return void
	 */
	private static function install_roles() {
		$roles = array(
			'qaf_waka'    => 'Waka Sekolah',
			'qaf_teacher' => 'Guru',
			'qaf_staff'   => 'Tenaga Kependidikan',
		);

		foreach ( $roles as $role_key => $role_label ) {
			$role = get_role( $role_key );
			if ( ! $role ) {
				add_role(
					$role_key,
					$role_label,
					array(
						'read'           => true,
						self::CAPABILITY => true,
					)
				);
				$role = get_role( $role_key );
			}

			if ( $role && ! $role->has_cap( self::CAPABILITY ) ) {
				$role->add_cap( self::CAPABILITY );
			}
		}

		// Preserve access from versions that used the two older role names.
		foreach ( array( 'qaf_media_team', 'qaf_school_unit' ) as $legacy_role_key ) {
			$legacy_role = get_role( $legacy_role_key );
			if ( $legacy_role && ! $legacy_role->has_cap( self::CAPABILITY ) ) {
				$legacy_role->add_cap( self::CAPABILITY );
			}
		}

		$administrator = get_role( 'administrator' );
		if ( $administrator && ! $administrator->has_cap( self::CAPABILITY ) ) {
			$administrator->add_cap( self::CAPABILITY );
		}
	}

	/**
	 * Create the shortcode page once and reuse an editor-created matching page.
	 *
	 * @return int|WP_Error
	 */
	private static function ensure_page() {
		$page_id = absint( get_option( self::PAGE_OPTION ) );
		$page    = $page_id ? get_post( $page_id ) : null;
		if ( $page instanceof WP_Post && 'page' === $page->post_type && 'trash' !== $page->post_status ) {
			return $page_id;
		}

		$existing = get_page_by_path( 'pusat-media', OBJECT, 'page' );
		if ( $existing instanceof WP_Post && 'trash' !== $existing->post_status ) {
			update_option( self::PAGE_OPTION, $existing->ID, false );
			return (int) $existing->ID;
		}

		$page_id = wp_insert_post(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'post_title'     => 'Pusat Media',
				'post_name'      => 'pusat-media',
				'post_content'   => '<!-- wp:shortcode -->[qaf_media_center]<!-- /wp:shortcode -->',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'meta_input'     => array(
					'_wp_page_template' => 'page-templates/template-full-width.php',
				),
			),
			true
		);

		if ( ! is_wp_error( $page_id ) ) {
			update_option( self::PAGE_OPTION, (int) $page_id, false );
		}

		return $page_id;
	}

	/**
	 * Return the stable portal URL even before the page upgrade has run.
	 *
	 * @return string
	 */
	public static function portal_url() {
		$page_id = absint( get_option( self::PAGE_OPTION ) );
		$url     = $page_id ? get_permalink( $page_id ) : '';

		return $url ? $url : home_url( '/pusat-media/' );
	}

	/**
	 * Load isolated portal presentation styles.
	 *
	 * @return void
	 */
	public static function enqueue_assets() {
		$page_id = absint( get_option( self::PAGE_OPTION ) );
		if ( ! $page_id || ! is_page( $page_id ) ) {
			return;
		}

		wp_enqueue_style(
			'qaf-media-center',
			QAF_CORE_URL . 'assets/css/media-center.css',
			array(),
			QAF_CORE_VERSION
		);
	}

	/**
	 * Keep the private portal and login form out of search indexes.
	 *
	 * @param array<string,bool> $robots Existing directives.
	 * @return array<string,bool>
	 */
	public static function robots( $robots ) {
		$page_id = absint( get_option( self::PAGE_OPTION ) );
		if ( $page_id && is_page( $page_id ) ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}

		return $robots;
	}

	/**
	 * Redirect portal-only users to their media center after login.
	 *
	 * @param string           $redirect_to           Default destination.
	 * @param string           $requested_redirect_to Requested destination.
	 * @param WP_User|WP_Error $user                  Authenticated user.
	 * @return string
	 */
	public static function login_redirect( $redirect_to, $requested_redirect_to, $user ) {
		unset( $requested_redirect_to );

		if ( $user instanceof WP_User && user_can( $user, self::CAPABILITY ) && ! user_can( $user, 'manage_options' ) ) {
			return self::portal_url();
		}

		return $redirect_to;
	}

	/**
	 * Keep portal-only accounts out of WordPress administration screens.
	 *
	 * @return void
	 */
	public static function restrict_portal_users() {
		if ( ! current_user_can( self::CAPABILITY ) || current_user_can( 'manage_options' ) || wp_doing_ajax() ) {
			return;
		}

		wp_safe_redirect( self::portal_url() );
		exit;
	}

	/**
	 * Hide the WordPress toolbar from portal-only accounts.
	 *
	 * @param bool $show Existing decision.
	 * @return bool
	 */
	public static function show_admin_bar( $show ) {
		if ( current_user_can( self::CAPABILITY ) && ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		return $show;
	}

	/**
	 * Add the optional school-unit label to an existing user profile.
	 *
	 * @param WP_User $user Profile owner.
	 * @return void
	 */
	public static function render_user_fields( $user ) {
		if ( ! $user instanceof WP_User || ! current_user_can( 'edit_users' ) || ! current_user_can( 'edit_user', $user->ID ) ) {
			return;
		}

		self::render_access_fields( $user );
	}

	/**
	 * Add the optional school-unit label while an administrator creates a user.
	 *
	 * @param string $operation User creation operation.
	 * @return void
	 */
	public static function render_new_user_fields( $operation ) {
		if ( ! in_array( $operation, array( 'add-new-user', 'create-user' ), true ) || ! current_user_can( 'create_users' ) ) {
			return;
		}

		self::render_access_fields( null );
	}

	/**
	 * Render shared field markup for new and existing accounts.
	 *
	 * @param WP_User|null $user Profile owner.
	 * @return void
	 */
	private static function render_access_fields( $user ) {
		$user_id = $user instanceof WP_User ? $user->ID : 0;
		$unit    = $user_id ? get_user_meta( $user_id, self::UNIT_META, true ) : '';

		wp_nonce_field( self::PROFILE_NONCE_ACTION, self::PROFILE_NONCE );
		?>
		<h2><?php esc_html_e( 'Akses Pusat Media', 'queen-alfalah-core' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th><label for="qaf-school-unit"><?php esc_html_e( 'Unit / Jabatan', 'queen-alfalah-core' ); ?></label></th>
				<td>
					<input class="regular-text" type="text" id="qaf-school-unit" name="qaf_school_unit" value="<?php echo esc_attr( $unit ); ?>">
					<p class="description"><?php esc_html_e( 'Contoh: Waka Kurikulum, Guru TJKT, Tata Usaha, BK, Hubin, atau unit lain pada struktur sekolah.', 'queen-alfalah-core' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save the unit label on an existing account.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public static function save_user_fields( $user_id ) {
		if ( ! current_user_can( 'edit_users' ) || ! current_user_can( 'edit_user', $user_id ) || ! self::valid_profile_nonce() ) {
			return;
		}

		self::persist_access_fields( $user_id );
	}

	/**
	 * Save the unit label when a new user is created.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public static function save_new_user_fields( $user_id ) {
		if ( ! current_user_can( 'create_users' ) || ! self::valid_profile_nonce() ) {
			return;
		}

		self::persist_access_fields( $user_id );
	}

	/**
	 * Verify the profile form nonce without accepting missing values.
	 *
	 * @return bool
	 */
	private static function valid_profile_nonce() {
		return isset( $_POST[ self::PROFILE_NONCE ] )
			&& wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST[ self::PROFILE_NONCE ] ) ),
				self::PROFILE_NONCE_ACTION
			);
	}

	/**
	 * Store only the normalized public unit label.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	private static function persist_access_fields( $user_id ) {
		$unit = isset( $_POST['qaf_school_unit'] )
			? sanitize_text_field( wp_unslash( $_POST['qaf_school_unit'] ) )
			: '';

		if ( $unit ) {
			update_user_meta( $user_id, self::UNIT_META, $unit );
		} else {
			delete_user_meta( $user_id, self::UNIT_META );
		}
	}

	/**
	 * Return the Drive category that owns a portal account.
	 *
	 * @param WP_User $user WordPress user.
	 * @return string
	 */
	private static function user_category( $user ) {
		$roles = $user instanceof WP_User ? (array) $user->roles : array();
		$map   = array(
			'qaf_waka'        => 'Waka',
			'qaf_teacher'     => 'Guru',
			'qaf_staff'       => 'Tendik',
			'qaf_media_team'  => 'Tendik',
			'qaf_school_unit' => 'Tendik',
		);

		foreach ( $map as $role => $category ) {
			if ( in_array( $role, $roles, true ) ) {
				return $category;
			}
		}

		return '';
	}

	/**
	 * Render login, authorization state, or the assigned Drive link.
	 *
	 * @return string
	 */
	public static function render_shortcode() {
		ob_start();
		?>
		<section class="qaf-media-center" aria-labelledby="qaf-media-title">
			<header class="qaf-media-hero">
				<p class="qaf-media-eyebrow"><?php esc_html_e( 'Ruang Kerja Privat', 'queen-alfalah-core' ); ?></p>
				<h2 id="qaf-media-title"><?php esc_html_e( 'Pusat Media Sekolah', 'queen-alfalah-core' ); ?></h2>
				<p><?php esc_html_e( 'Akses dokumen resmi sesuai divisi yang ditetapkan pada akun Anda.', 'queen-alfalah-core' ); ?></p>
			</header>

			<?php if ( ! is_user_logged_in() ) : ?>
				<div class="qaf-media-login">
					<h3><?php esc_html_e( 'Masuk ke akun sekolah', 'queen-alfalah-core' ); ?></h3>
					<p><?php esc_html_e( 'Gunakan username dan password yang diberikan administrator.', 'queen-alfalah-core' ); ?></p>
					<?php
					echo wp_login_form(
						array(
							'echo'           => false,
							'redirect'       => self::portal_url(),
							'label_username' => __( 'Username', 'queen-alfalah-core' ),
							'label_password' => __( 'Password', 'queen-alfalah-core' ),
							'label_log_in'   => __( 'Masuk', 'queen-alfalah-core' ),
							'remember'       => true,
						)
					); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<p class="qaf-media-login__help"><a href="<?php echo esc_url( wp_lostpassword_url( self::portal_url() ) ); ?>"><?php esc_html_e( 'Lupa password?', 'queen-alfalah-core' ); ?></a></p>
				</div>
			<?php elseif ( ! current_user_can( self::CAPABILITY ) ) : ?>
				<div class="qaf-media-notice qaf-media-notice--error">
					<strong><?php esc_html_e( 'Akses belum diberikan.', 'queen-alfalah-core' ); ?></strong>
					<?php esc_html_e( 'Hubungi administrator untuk menetapkan peran Pusat Media pada akun Anda.', 'queen-alfalah-core' ); ?>
				</div>
			<?php else : ?>
				<?php self::render_authenticated_portal(); ?>
			<?php endif; ?>
		</section>
		<?php

		return ob_get_clean();
	}

	/**
	 * Return the configured link rows visible to one account.
	 *
	 * Portal-only users receive exactly one division link. Administrators may
	 * see all configured rows so they can verify settings without changing
	 * their own WordPress role.
	 *
	 * @param WP_User $user Current WordPress user.
	 * @return array<int,array<string,string>>
	 */
	private static function drive_links_for_user( $user ) {
		$definitions = array(
			'Waka'   => array(
				'label'   => 'Folder Waka',
				'setting' => 'media_drive_waka_url',
			),
			'Guru'   => array(
				'label'   => 'Folder Guru',
				'setting' => 'media_drive_teacher_url',
			),
			'Tendik' => array(
				'label'   => 'Folder Tendik',
				'setting' => 'media_drive_staff_url',
			),
		);
		$category    = self::user_category( $user );
		$allowed     = user_can( $user, 'manage_options' ) ? array_keys( $definitions ) : array( $category );
		$links       = array();

		foreach ( $allowed as $key ) {
			if ( ! isset( $definitions[ $key ] ) ) {
				continue;
			}

			$url    = qaf_core_get_setting( $definitions[ $key ]['setting'], '' );
			$url    = is_string( $url ) ? esc_url_raw( $url, array( 'https' ) ) : '';
			$scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );
			$host   = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
			if ( 'https' !== $scheme || ! in_array( $host, array( 'drive.google.com', 'docs.google.com' ), true ) ) {
				continue;
			}

			$links[] = array(
				'category' => $key,
				'label'    => $definitions[ $key ]['label'],
				'url'      => $url,
			);
		}

		return $links;
	}

	/**
	 * Render the authorized user's division link.
	 *
	 * @return void
	 */
	private static function render_authenticated_portal() {
		$user = wp_get_current_user();
		$unit = get_user_meta( $user->ID, self::UNIT_META, true );
		?>
		<div class="qaf-media-account">
			<div>
				<span><?php esc_html_e( 'Masuk sebagai', 'queen-alfalah-core' ); ?></span>
				<strong><?php echo esc_html( $user->display_name ); ?></strong>
				<?php if ( $unit ) : ?><small><?php echo esc_html( $unit ); ?></small><?php endif; ?>
			</div>
			<a href="<?php echo esc_url( wp_logout_url( self::portal_url() ) ); ?>"><?php esc_html_e( 'Keluar', 'queen-alfalah-core' ); ?></a>
		</div>
		<?php

		$category = self::user_category( $user );
		if ( ! current_user_can( 'manage_options' ) && ! $category ) {
			self::render_notice( __( 'Akun belum memakai peran Waka, Guru, atau Tendik. Hubungi administrator Pusat Media.', 'queen-alfalah-core' ), true );
			return;
		}

		$links = self::drive_links_for_user( $user );
		if ( empty( $links ) ) {
			$message = current_user_can( 'manage_options' )
				? __( 'Belum ada tautan Drive yang valid. Isi bagian Pusat Media pada menu Sekolah → Pengaturan.', 'queen-alfalah-core' )
				: __( 'Tautan Google Drive untuk divisi Anda belum diatur. Hubungi administrator Pusat Media.', 'queen-alfalah-core' );
			self::render_notice( $message, true );
			return;
		}
		?>
		<div class="qaf-media-link-panel">
			<p><?php esc_html_e( 'Dokumen disimpan dan dikelola langsung di Google Drive. Izin melihat, mengunggah, menyunting, atau mengunduh mengikuti akun Google yang diberi akses pada folder tersebut.', 'queen-alfalah-core' ); ?></p>
			<div class="qaf-media-links">
				<?php foreach ( $links as $link ) : ?>
					<a class="qaf-media-drive-link" href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="external noopener noreferrer" referrerpolicy="no-referrer">
						<span aria-hidden="true">&#128193;</span>
						<strong><?php echo esc_html( $link['label'] ); ?></strong>
						<small><?php echo esc_html( sprintf( __( 'Akses divisi %s', 'queen-alfalah-core' ), $link['category'] ) ); ?></small>
					</a>
				<?php endforeach; ?>
			</div>
			<p class="qaf-media-security"><?php esc_html_e( 'Gunakan berbagi Google Drive “Dibatasi” dan berikan izin hanya kepada akun Google yang berwenang.', 'queen-alfalah-core' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Print a consistent portal warning.
	 *
	 * @param string $message Notice text.
	 * @param bool   $error   Whether this is an error.
	 * @return void
	 */
	private static function render_notice( $message, $error = false ) {
		printf(
			'<div class="qaf-media-notice%1$s">%2$s</div>',
			$error ? ' qaf-media-notice--error' : '',
			esc_html( $message )
		);
	}
}
