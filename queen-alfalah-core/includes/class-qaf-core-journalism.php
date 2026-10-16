<?php
/** Least-privilege journalism accounts, created only after administrator input. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class QAF_Core_Journalism {
	const ROLE = 'qaf_journalism';
	const VERSION_OPTION = 'qaf_journalism_schema_version';
	const SCHEMA_VERSION = '1.0.0';
	const MENU_SLUG = 'qaf-journalism-accounts';
	const ACTION = 'qaf_provision_journalism_accounts';
	const NONCE = 'qaf_provision_journalism_accounts_once';
	const SOURCE_META = '_qaf_journalism_source_id';

	/** Call on plugins_loaded, before school post types are registered on init. */
	public static function init() {
		add_filter( 'register_post_type_args', array( __CLASS__, 'isolate_school_capabilities' ), 20, 2 );
		add_filter( 'map_meta_cap', array( __CLASS__, 'map_capabilities' ), 100, 4 );
		add_filter( 'user_has_cap', array( __CLASS__, 'limit_capabilities' ), 100, 4 );
		add_action( 'admin_init', array( __CLASS__, 'maybe_upgrade' ), 30 );
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 40 );
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle_provision' ) );
		add_filter( 'ajax_query_attachments_args', array( __CLASS__, 'own_media_query' ) );
		add_filter( 'rest_attachment_query', array( __CLASS__, 'own_media_query' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'own_admin_posts' ) );
	}

	/** No deletion, other-author editing, school administration or portal access. */
	public static function capabilities() {
		return array(
			'read', 'upload_files', 'edit_posts', 'edit_published_posts', 'publish_posts',
			'edit_qaf_works', 'edit_published_qaf_works', 'publish_qaf_works', 'assign_qaf_work_terms',
		);
	}

	public static function is_journalist( $user ) {
		return $user instanceof WP_User && in_array( self::ROLE, (array) $user->roles, true );
	}

	/** A versioned administrator request adds the role, never any user accounts. */
	public static function maybe_upgrade() {
		if ( ! current_user_can( 'manage_options' ) || self::SCHEMA_VERSION === get_option( self::VERSION_OPTION ) ) { return; }
		self::install_role();
		update_option( self::VERSION_OPTION, self::SCHEMA_VERSION, false );
	}

	private static function install_role() {
		$wanted = array_fill_keys( self::capabilities(), true );
		$role = get_role( self::ROLE );
		if ( ! $role ) {
			add_role( self::ROLE, 'Pembimbing Jurnalistik', $wanted );
			$role = get_role( self::ROLE );
		}
		if ( ! $role ) { return; }
		foreach ( array_keys( (array) $role->capabilities ) as $cap ) {
			if ( ! isset( $wanted[ $cap ] ) ) { $role->remove_cap( $cap ); }
		}
		foreach ( $wanted as $cap => $grant ) { $role->add_cap( $cap, $grant ); }
	}

	/** Explicit default post primitives. Meta capabilities remain object-aware. */
	public static function school_primitive_map() {
		return array(
			'edit_posts' => 'qaf_school_edit_posts',
			'edit_others_posts' => 'qaf_school_edit_others_posts',
			'publish_posts' => 'qaf_school_publish_posts',
			'read_private_posts' => 'qaf_school_read_private_posts',
			'delete_posts' => 'qaf_school_delete_posts',
			'delete_private_posts' => 'qaf_school_delete_private_posts',
			'delete_published_posts' => 'qaf_school_delete_published_posts',
			'delete_others_posts' => 'qaf_school_delete_others_posts',
			'edit_private_posts' => 'qaf_school_edit_private_posts',
			'edit_published_posts' => 'qaf_school_edit_published_posts',
			'create_posts' => 'qaf_school_edit_posts',
		);
	}

	/** Prevent edit_posts from opening unrelated school CPTs, including REST create. */
	public static function isolate_school_capabilities( $args, $post_type ) {
		if ( 0 !== strpos( $post_type, 'qaf_' ) || in_array( $post_type, array( 'qaf_work', 'qaf_notice' ), true ) ) { return $args; }
		$type = isset( $args['capability_type'] ) ? $args['capability_type'] : 'post';
		if ( 'post' !== $type && array( 'post', 'posts' ) !== $type ) { return $args; }
		$caps = isset( $args['capabilities'] ) && is_array( $args['capabilities'] ) ? $args['capabilities'] : array();
		foreach ( self::school_primitive_map() as $key => $dedicated ) {
			$original = 'create_posts' === $key ? 'edit_posts' : $key;
			if ( ! isset( $caps[ $key ] ) || $original === $caps[ $key ] ) { $caps[ $key ] = $dedicated; }
		}
		$args['capabilities'] = $caps;
		$args['map_meta_cap'] = true;
		return $args;
	}

	/**
	 * Bridge school primitives for existing users without granting anything new.
	 * Journalists are denied even if another role/direct user capability leaks in.
	 * Object checks cover wp-admin, AJAX, REST, XML-RPC and registered meta access.
	 */
	public static function map_capabilities( $caps, $cap, $user_id, $args ) {
		$user = get_userdata( $user_id );
		if ( ! self::is_journalist( $user ) ) {
			$bridge = array_flip( self::school_primitive_map() );
			$bridge['qaf_school_edit_posts'] = 'edit_posts'; // create_posts shares this primitive.
			foreach ( $caps as $index => $required ) {
				if ( isset( $bridge[ $required ] ) ) { $caps[ $index ] = $bridge[ $required ]; }
			}
			return array_values( array_unique( $caps ) );
		}

		$object_caps = array(
			'edit_post', 'edit_page', 'edit_qaf_work', 'publish_post',
			'delete_post', 'delete_page', 'delete_qaf_work',
			'read_post', 'read_page', 'read_qaf_work',
			'add_post_meta', 'edit_post_meta', 'delete_post_meta', 'read_post_meta',
		);
		if ( in_array( $cap, $object_caps, true ) ) {
			$post = isset( $args[0] ) ? get_post( (int) $args[0] ) : null;
			if ( $post && 'revision' === $post->post_type ) { $post = get_post( $post->post_parent ); }
			if ( ! $post ) { return array( 'do_not_allow' ); }
			$read = in_array( $cap, array( 'read_post', 'read_page', 'read_qaf_work' ), true );
			$own = (int) $post->post_author === (int) $user_id;
			$allowed_type = in_array( $post->post_type, array( 'post', 'qaf_work', 'attachment' ), true );
			$status = get_post_status_object( get_post_status( $post ) );
			if ( $read ) {
				// Published public content remains readable; non-public content is own-only.
				if ( ( ! $status || ! $status->public ) && ( ! $own || ! $allowed_type ) ) { return array( 'do_not_allow' ); }
			} elseif ( ! $own || ! $allowed_type || in_array( $cap, array( 'delete_post', 'delete_page', 'delete_qaf_work' ), true ) ) {
				return array( 'do_not_allow' );
			}
		}

		// Leave WordPress's own self-profile allowance (empty caps) untouched.
		foreach ( $caps as $required ) {
			if ( ! in_array( $required, self::capabilities(), true ) ) { return array( 'do_not_allow' ); }
		}
		return $caps;
	}

	/** Do not let unrelated roles or per-user grants expand this dedicated role. */
	public static function limit_capabilities( $allcaps, $caps, $args, $user ) {
		if ( ! self::is_journalist( $user ) ) { return $allcaps; }
		foreach ( $allcaps as $cap => $grant ) {
			if ( ! in_array( $cap, self::capabilities(), true ) ) { $allcaps[ $cap ] = false; }
		}
		return $allcaps;
	}

	public static function own_media_query( $args ) {
		if ( self::is_journalist( wp_get_current_user() ) ) { $args['author'] = get_current_user_id(); }
		return $args;
	}

	public static function own_admin_posts( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || ! self::is_journalist( wp_get_current_user() ) ) { return; }
		$type = $query->get( 'post_type' );
		if ( ! $type || in_array( $type, array( 'post', 'qaf_work', 'attachment' ), true ) ) { $query->set( 'author', get_current_user_id() ); }
	}

	public static function account_definitions() {
		return array(
			'garina' => array( 'username' => 'jurnalistik.garina', 'display_name' => 'Garina Rahmi Rahmani, S.Pd.', 'source_id' => 'jurnalistik:garina-rahmi-rahmani' ),
			'nurul' => array( 'username' => 'jurnalistik.nurul', 'display_name' => 'Nurul Hidayah, S.Sos.', 'source_id' => 'jurnalistik:nurul-hidayah' ),
		);
	}

	private static function normalized_name( $name ) {
		$name = strtolower( remove_accents( (string) $name ) );
		$name = preg_replace( '/(?:,.*$|\s+s\.?\s*(?:pd|sos)\.?\s*$)/u', '', $name );
		return trim( preg_replace( '/[^a-z0-9]+/', ' ', $name ) );
	}

	/** A collision is skipped, never merged, reassigned, or given a new password. */
	public static function existing_account( $definition, $email = '' ) {
		$existing = username_exists( $definition['username'] );
		if ( ! $existing && $email ) { $existing = email_exists( $email ); }
		if ( $existing ) { return (int) $existing; }
		$name = self::normalized_name( $definition['display_name'] );
		foreach ( get_users( array( 'fields' => 'all' ) ) as $user ) {
			if ( $name === self::normalized_name( $user->display_name ) || $definition['source_id'] === get_user_meta( $user->ID, self::SOURCE_META, true ) ) { return (int) $user->ID; }
		}
		return 0;
	}

	public static function register_menu() {
		add_submenu_page( 'qaf-school', 'Akun Pembimbing Jurnalistik', 'Akun Jurnalistik', 'manage_options', self::MENU_SLUG, array( __CLASS__, 'render_page' ) );
	}

	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'create_users' ) ) { wp_die( 'Akses ditolak.', '', array( 'response' => 403 ) ); }
		?>
		<div class="wrap"><h1>Akun Pembimbing Jurnalistik</h1>
		<p>Akun hanya dapat membuat dan menerbitkan Berita/Artikel serta Karya Siswa miliknya sendiri, dan mengunggah media. Tidak dapat mengubah milik pengguna lain atau pengaturan sekolah.</p>
		<p>Isi alamat email aktif yang telah dikonfirmasi untuk masing-masing pembimbing. Kolom kosong dilewati. WordPress mengirimkan tautan pengaturan kata sandi; kata sandi tidak ditampilkan atau disimpan dalam laporan.</p>
		<p>Alternatif tanpa email tersedia di bawah. Kata sandi acak ditampilkan sekali kepada administrator; pemulihan akses selanjutnya harus dibantu administrator.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
			<?php wp_nonce_field( self::NONCE ); ?>
			<table class="widefat striped"><thead><tr><th>Nama</th><th>Username</th><th>Email aktif / Status</th></tr></thead><tbody>
			<?php foreach ( self::account_definitions() as $key => $definition ) : ?>
				<tr><td><?php echo esc_html( $definition['display_name'] ); ?></td><td><code><?php echo esc_html( $definition['username'] ); ?></code></td><td>
				<?php if ( self::existing_account( $definition ) ) : ?>
					Akun orang ini sudah terdeteksi; dilewati tanpa mengubah peran, email, atau kata sandi.
				<?php else : ?>
					<label class="screen-reader-text" for="qaf-journalism-<?php echo esc_attr( $key ); ?>">Email <?php echo esc_html( $definition['display_name'] ); ?></label>
					<input type="email" class="regular-text" id="qaf-journalism-<?php echo esc_attr( $key ); ?>" name="emails[<?php echo esc_attr( $key ); ?>]" autocomplete="off">
				<?php endif; ?></td></tr>
			<?php endforeach; ?></tbody></table>
			<?php submit_button( 'Buat Akun dan Kirim Tautan Pengaturan Kata Sandi' ); ?>
		</form><p>Jika email tidak diterima, administrator perlu memeriksa pengiriman email situs. Akun yang sudah ada tidak dibuat ulang.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
			<input type="hidden" name="without_email" value="1">
			<?php wp_nonce_field( self::NONCE ); ?>
			<p><label><input type="checkbox" name="confirm_without_email" value="1" required> Saya menyetujui pembuatan kedua akun tanpa email dan akan menyampaikan kata sandi melalui jalur pribadi.</label></p>
			<?php submit_button( 'Buat Kedua Akun Tanpa Email', 'secondary' ); ?>
		</form></div>
		<?php
	}

	public static function handle_provision() {
		if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'create_users' ) ) { wp_die( 'Akses ditolak.', '', array( 'response' => 403 ) ); }
		if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( (string) $_SERVER['REQUEST_METHOD'] ) : '' ) ) { wp_die( 'Gunakan formulir POST.', '', array( 'response' => 405 ) ); }
		check_admin_referer( self::NONCE );
		$emails = isset( $_POST['emails'] ) && is_array( $_POST['emails'] ) ? wp_unslash( $_POST['emails'] ) : array();
		$without_email = isset( $_POST['without_email'], $_POST['confirm_without_email'] ) && '1' === $_POST['without_email'] && '1' === $_POST['confirm_without_email'];
		$result = self::provision_accounts( $emails, $without_email );
		nocache_headers();
		header( 'Cache-Control: no-store, private, max-age=0' );
		header( 'Referrer-Policy: no-referrer' );
		$html = '<h1>Hasil Penyiapan Akun Jurnalistik</h1><ul>';
		foreach ( $result as $row ) {
			$html .= '<li>' . esc_html( $row['username'] . ': ' . $row['message'] );
			if ( ! empty( $row['initial_password'] ) ) { $html .= '<p>Kata sandi awal (simpan sekarang, hanya ditampilkan sekali): <code>' . esc_html( $row['initial_password'] ) . '</code></p>'; }
			$html .= '</li>';
		}
		$html .= '</ul><p><a href="' . esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG ) ) . '">Kembali</a></p>';
		wp_die( $html, 'Akun Jurnalistik', array( 'response' => 200 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped components above.
	}

	/** Public seam for tests; the only runtime caller is the nonce-protected POST. */
	public static function provision_accounts( $emails, $without_email = false ) {
		if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'create_users' ) ) { return array( array( 'username' => '', 'message' => 'Akses ditolak.' ) ); }
		self::install_role();
		$results = array();
		foreach ( self::account_definitions() as $key => $definition ) {
			$raw = isset( $emails[ $key ] ) && is_string( $emails[ $key ] ) ? trim( $emails[ $key ] ) : '';
			$email = sanitize_email( $raw );
			$message = '';
			$initial_password = '';
			if ( $without_email ) { $raw = ''; $email = ''; }
			if ( ! $without_email && '' === $raw ) { $message = 'Dilewati; email belum diisi.'; }
			elseif ( ! $without_email && ( ! is_email( $email ) || $email !== $raw ) ) { $message = 'Dilewati; alamat email tidak valid.'; }
			elseif ( self::existing_account( $definition, $email ) ) { $message = 'Dilewati; username, email, atau nama sudah dimiliki akun yang ada. Tidak ada akun yang diubah.'; }
			else {
				$password = wp_generate_password( 32, true, false );
				$user_id = wp_insert_user( array(
					'user_login' => $definition['username'], 'user_email' => $email,
					'user_pass' => $password,
					'display_name' => $definition['display_name'], 'nickname' => $definition['display_name'], 'role' => self::ROLE,
				) );
				if ( is_wp_error( $user_id ) ) { $message = 'Gagal dibuat: ' . $user_id->get_error_message(); }
				else {
					update_user_meta( $user_id, self::SOURCE_META, $definition['source_id'] );
					if ( $without_email ) {
						$initial_password = $password;
						$message = 'Akun dibuat tanpa email. Pemulihan kata sandi harus melalui administrator.';
					} else {
						wp_new_user_notification( $user_id, null, 'user' );
						$message = 'Akun dibuat; WordPress telah diminta mengirim tautan pengaturan kata sandi. Periksa kotak masuk/spam; status pengiriman email tidak dapat dipastikan oleh alat ini.';
					}
				}
			}
			$results[] = array( 'username' => $definition['username'], 'message' => $message, 'initial_password' => $initial_password );
		}
		return $results;
	}
}
