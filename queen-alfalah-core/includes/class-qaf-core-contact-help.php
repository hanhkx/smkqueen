<?php
/**
 * Privacy-conscious contact routing and help form.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provide contact-unit routing without storing visitor messages in WordPress.
 */
final class QAF_Core_Contact_Help {
	const ACTION = 'qaf_contact_help';
	const NONCE  = 'qaf_contact_help_submit';

	/** Register public form handlers and one conservative page-content cleanup. */
	public static function init() {
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle' ) );
		add_action( 'admin_post_nopriv_' . self::ACTION, array( __CLASS__, 'handle' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_refresh_contact_page' ), 48 );
	}

	/** Run the same conservative cleanup after plugin activation. */
	public static function activate() {
		self::maybe_refresh_contact_page();
	}

	/**
	 * Contact units displayed to visitors.
	 *
	 * @return array<string,array<string,string>>
	 */
	public static function units() {
		return array(
			'general' => array(
				'label'       => 'Informasi Umum & Tata Usaha',
				'description' => 'Surat, legalisasi, administrasi umum, dan informasi layanan sekolah.',
				'email_key'   => 'contact_general_email',
				'wa_key'      => 'contact_general_whatsapp',
			),
			'spmb' => array(
				'label'       => 'SPMB',
				'description' => 'Pendaftaran murid baru, persyaratan, jadwal, dan bantuan proses pendaftaran.',
				'email_key'   => 'contact_spmb_email',
				'wa_key'      => 'contact_spmb_whatsapp',
			),
			'curriculum' => array(
				'label'       => 'Kurikulum & Pembelajaran',
				'description' => 'Jadwal, pembelajaran, asesmen, praktik, dan informasi akademik.',
				'email_key'   => 'contact_curriculum_email',
				'wa_key'      => 'contact_curriculum_whatsapp',
			),
			'student' => array(
				'label'       => 'Kesiswaan & BK',
				'description' => 'Kegiatan siswa, pendampingan, tata tertib, dan layanan bimbingan konseling.',
				'email_key'   => 'contact_student_email',
				'wa_key'      => 'contact_student_whatsapp',
			),
			'facilities' => array(
				'label'       => 'Sarana Prasarana',
				'description' => 'Fasilitas, laboratorium, ruang praktik, peminjaman, dan pelaporan sarana.',
				'email_key'   => 'contact_facilities_email',
				'wa_key'      => 'contact_facilities_whatsapp',
			),
			'industry' => array(
				'label'       => 'Hubin, PKL & BKK',
				'description' => 'Kemitraan industri, praktik kerja lapangan, rekrutmen, dan Bursa Kerja Khusus.',
				'email_key'   => 'contact_industry_email',
				'wa_key'      => 'contact_industry_whatsapp',
			),
		);
	}

	/** Render the unit directory, using central channels as safe fallbacks. */
	public static function render_units() {
		$general_email = sanitize_email( QAF_Core_Settings::get_setting( 'email', '' ) );
		$general_wa    = self::digits( QAF_Core_Settings::get_setting( 'whatsapp', '' ) );
		?>
		<div class="qaf-contact-units">
			<?php foreach ( self::units() as $key => $unit ) : ?>
				<?php
				$email = sanitize_email( QAF_Core_Settings::get_setting( $unit['email_key'], '' ) );
				$wa    = self::digits( QAF_Core_Settings::get_setting( $unit['wa_key'], '' ) );
				$email = $email ? $email : $general_email;
				$wa    = $wa ? $wa : $general_wa;
				?>
				<article class="qaf-contact-unit">
					<h3><?php echo esc_html( $unit['label'] ); ?></h3>
					<p><?php echo esc_html( $unit['description'] ); ?></p>
					<div class="qaf-contact-unit__actions">
						<?php if ( $email ) : ?>
							<a href="<?php echo esc_url( 'mailto:' . $email ); ?>">Email unit</a>
						<?php endif; ?>
						<?php if ( $wa ) : ?>
							<a href="<?php echo esc_url( 'https://wa.me/' . $wa . '?text=' . rawurlencode( 'Assalamu’alaikum, saya ingin menghubungi layanan ' . $unit['label'] . ' SMK Queen Al-Falah.' ) ); ?>" target="_blank" rel="noopener noreferrer">WhatsApp<span class="screen-reader-text"> (tab baru)</span></a>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/** Render concise frequently asked questions. */
	public static function render_faq() {
		$items = array(
			'Kapan sekolah dapat dihubungi?' => (string) QAF_Core_Settings::get_setting( 'opening_hours', 'Senin–Sabtu, 08.00–14.00 WIB' ),
			'Kanal mana yang digunakan untuk pendaftaran?' => 'Pilih unit SPMB untuk pertanyaan persyaratan, jadwal, dan bantuan proses pendaftaran.',
			'Bagaimana menghubungi sekolah untuk PKL atau rekrutmen?' => 'Pilih unit Hubin, PKL & BKK agar pesan diarahkan ke layanan kerja sama industri.',
			'Apakah dokumen pribadi boleh dikirim melalui formulir?' => 'Tidak. Jangan mengirim NIK, NISN, kata sandi, data kesehatan, scan ijazah, atau dokumen pribadi. Petugas akan memberi arahan kanal resmi bila dokumen diperlukan.',
		);
		?>
		<div class="qaf-contact-faq">
			<?php foreach ( $items as $question => $answer ) : ?>
				<details>
					<summary><?php echo esc_html( $question ); ?></summary>
					<p><?php echo esc_html( $answer ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/** Render a minimal help form whose messages are sent by email, not stored. */
	public static function render_form() {
		$status = isset( $_GET['qaf_help'] ) ? sanitize_key( wp_unslash( $_GET['qaf_help'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$messages = array(
			'sent'    => array( 'success', 'Pesan berhasil dikirim. Petugas akan menindaklanjuti melalui email yang Anda cantumkan.' ),
			'invalid' => array( 'error', 'Pesan belum terkirim. Periksa unit, email, subjek, dan isi pesan lalu coba kembali.' ),
			'limited' => array( 'error', 'Batas pengiriman sementara tercapai. Silakan tunggu sekitar satu jam atau gunakan kanal resmi yang tersedia.' ),
			'error'   => array( 'error', 'Layanan email sedang mengalami kendala. Silakan gunakan WhatsApp atau email resmi sekolah.' ),
		);
		if ( isset( $messages[ $status ] ) ) {
			printf( '<div class="qaf-help-status qaf-help-status--%1$s" role="status"><p>%2$s</p></div>', esc_attr( $messages[ $status ][0] ), esc_html( $messages[ $status ][1] ) );
		}
		?>
		<form class="qaf-help-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
			<input type="hidden" name="qaf_started" value="<?php echo esc_attr( time() ); ?>">
			<?php wp_nonce_field( self::NONCE, 'qaf_help_nonce' ); ?>
			<p class="qaf-help-form__trap" aria-hidden="true"><label>Perusahaan <input type="text" name="qaf_company" tabindex="-1" autocomplete="off"></label></p>
			<div class="qaf-help-form__grid">
				<p><label for="qaf-help-unit">Unit tujuan <span aria-hidden="true">*</span></label><select id="qaf-help-unit" name="qaf_unit" required><option value="">Pilih layanan</option><?php foreach ( self::units() as $key => $unit ) : ?><option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $unit['label'] ); ?></option><?php endforeach; ?></select></p>
				<p><label for="qaf-help-name">Nama <span class="qaf-form-optional">(opsional)</span></label><input id="qaf-help-name" type="text" name="qaf_name" maxlength="100" autocomplete="name"></p>
				<p><label for="qaf-help-email">Email untuk balasan <span aria-hidden="true">*</span></label><input id="qaf-help-email" type="email" name="qaf_email" maxlength="190" autocomplete="email" required></p>
				<p><label for="qaf-help-subject">Subjek <span aria-hidden="true">*</span></label><input id="qaf-help-subject" type="text" name="qaf_subject" minlength="5" maxlength="160" required></p>
			</div>
			<p><label for="qaf-help-message">Pesan <span aria-hidden="true">*</span></label><textarea id="qaf-help-message" name="qaf_message" rows="7" minlength="30" maxlength="3000" required></textarea></p>
			<p class="qaf-help-form__privacy">Pesan dikirim ke email layanan dan tidak disimpan di basis data website. Jangan cantumkan NIK, NISN, kata sandi, data kesehatan, atau dokumen pribadi.</p>
			<p><button class="button" type="submit">Kirim permintaan bantuan</button></p>
		</form>
		<?php
	}

	/** Validate and email one form submission. */
	public static function handle() {
		$redirect = self::redirect_url();
		if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '' ) ) {
			self::redirect( $redirect, 'invalid' );
		}

		$nonce = isset( $_POST['qaf_help_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['qaf_help_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE ) ) {
			self::redirect( $redirect, 'invalid' );
		}

		$trap    = isset( $_POST['qaf_company'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['qaf_company'] ) ) ) : '';
		$started = isset( $_POST['qaf_started'] ) ? absint( $_POST['qaf_started'] ) : 0;
		if ( $trap || ! $started || time() - $started < 2 || time() - $started > DAY_IN_SECONDS ) {
			self::redirect( $redirect, 'invalid' );
		}

		$units   = self::units();
		$unit_id = isset( $_POST['qaf_unit'] ) ? sanitize_key( wp_unslash( $_POST['qaf_unit'] ) ) : '';
		$name    = isset( $_POST['qaf_name'] ) ? sanitize_text_field( wp_unslash( $_POST['qaf_name'] ) ) : '';
		$email   = isset( $_POST['qaf_email'] ) ? sanitize_email( wp_unslash( $_POST['qaf_email'] ) ) : '';
		$subject = isset( $_POST['qaf_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['qaf_subject'] ) ) : '';
		$message = isset( $_POST['qaf_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['qaf_message'] ) ) : '';
		if ( ! isset( $units[ $unit_id ] ) || ! is_email( $email ) || strlen( $subject ) < 5 || strlen( $message ) < 30 ) {
			self::redirect( $redirect, 'invalid' );
		}

		$rate_key = 'qaf_help_' . substr( hash_hmac( 'sha256', self::request_fingerprint( $email ), wp_salt( 'nonce' ) ), 0, 32 );
		$count    = (int) get_transient( $rate_key );
		if ( $count >= 3 ) {
			self::redirect( $redirect, 'limited' );
		}
		set_transient( $rate_key, $count + 1, HOUR_IN_SECONDS );

		$unit      = $units[ $unit_id ];
		$recipient = sanitize_email( QAF_Core_Settings::get_setting( $unit['email_key'], '' ) );
		$recipient = $recipient ? $recipient : sanitize_email( QAF_Core_Settings::get_setting( 'email', '' ) );
		if ( ! is_email( $recipient ) ) {
			self::redirect( $redirect, 'error' );
		}

		$body = "Unit tujuan: {$unit['label']}\n";
		$body .= 'Nama: ' . ( $name ? $name : 'Tidak dicantumkan' ) . "\n";
		$body .= "Email balasan: {$email}\n\n{$message}\n\n";
		$body .= 'Pesan dikirim melalui formulir bantuan website dan tidak disimpan di basis data WordPress.';
		$headers = array( 'Reply-To: ' . ( $name ? $name : 'Pengunjung Website' ) . ' <' . $email . '>' );
		$sent    = wp_mail( $recipient, '[Website SMK Queen] ' . $unit['label'] . ' — ' . $subject, $body, $headers );
		self::redirect( $redirect, $sent ? 'sent' : 'error' );
	}

	/** Remove the exact old editor reminder and duplicated placeholder block once. */
	public static function maybe_refresh_contact_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => 10,
				'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => 'page-templates/template-contact.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		foreach ( $pages as $page ) {
			$content = (string) $page->post_content;
			if ( false === strpos( $content, 'Periksa kembali jam layanan, nomor WhatsApp, dan tautan peta sebelum dipublikasikan.' ) ) {
				continue;
			}
			wp_update_post(
				wp_slash(
					array(
						'ID'           => $page->ID,
						'post_content' => '<p>Pilih unit layanan yang sesuai agar pertanyaan Anda dapat ditangani dengan lebih cepat. Gunakan hanya kanal resmi yang tercantum pada halaman ini.</p>',
					)
				)
			);
		}
	}

	/** Create a privacy-preserving rate-limit fingerprint. */
	private static function request_fingerprint( $email ) {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return strtolower( $email ) . '|' . $ip;
	}

	/** Keep a WhatsApp number in the format accepted by wa.me. */
	private static function digits( $value ) {
		return preg_replace( '/\D+/', '', (string) $value );
	}

	/** Resolve a same-site redirect target. */
	private static function redirect_url() {
		$referer = wp_get_referer();
		if ( $referer ) {
			return remove_query_arg( 'qaf_help', $referer );
		}
		$page = get_page_by_path( 'kontak' );
		return $page instanceof WP_Post ? get_permalink( $page ) : home_url( '/kontak/' );
	}

	/** Redirect with a public, non-sensitive result code. */
	private static function redirect( $url, $status ) {
		wp_safe_redirect( add_query_arg( 'qaf_help', sanitize_key( $status ), $url ) . '#bantuan-kontak' );
		exit;
	}
}
