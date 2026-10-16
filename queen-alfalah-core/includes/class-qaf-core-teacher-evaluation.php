<?php
/**
 * Accountable student feedback and teacher-performance analytics.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Collect privacy-conscious, attributable student feedback and summarize it for school leaders.
 */
final class QAF_Core_Teacher_Evaluation {
	const DB_VERSION_OPTION = 'qaf_teacher_evaluation_db_version';
	const DB_VERSION        = '2.0.0';
	const PAGE_SLUG         = 'penilaian-kinerja-guru';
	const SHORTCODE         = 'qaf_teacher_evaluation';
	const ADMIN_SLUG        = 'qaf-teacher-evaluation';
	const SUBMIT_ACTION     = 'qaf_submit_teacher_evaluation';
	const NONCE_ACTION      = 'qaf_teacher_evaluation_submit';
	const TOKEN_FIELD       = 'qaf_evaluation_token';

	/** Register front-end, submission, installer, and reporting hooks. */
	public static function init() {
		add_shortcode( self::SHORTCODE, array( __CLASS__, 'render_shortcode' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_install' ), 28 );
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_page' ), 36 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_assets' ) );
		add_action( 'admin_post_' . self::SUBMIT_ACTION, array( __CLASS__, 'handle_submission' ) );
		add_action( 'admin_post_nopriv_' . self::SUBMIT_ACTION, array( __CLASS__, 'handle_submission' ) );
	}

	/** Install the table and public page during plugin activation. */
	public static function activate() {
		self::install_schema();
		self::ensure_page();
	}

	/** Apply schema/page upgrades only for an authenticated administrator. */
	public static function maybe_install() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( self::DB_VERSION !== get_option( self::DB_VERSION_OPTION ) ) {
			self::install_schema();
		}
		self::ensure_page();
	}

	/** Return the portable response-table name. */
	private static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'qaf_teacher_evaluations';
	}

	/** Create/update the response table. */
	private static function install_schema() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table   = self::table_name();
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			created_at datetime NOT NULL,
			academic_period varchar(32) NOT NULL,
			student_name varchar(191) NOT NULL DEFAULT '',
			student_hash char(64) DEFAULT NULL,
			teacher_id bigint(20) unsigned NOT NULL,
			teacher_name varchar(191) NOT NULL,
			subject varchar(191) NOT NULL,
			class_group varchar(48) NOT NULL,
			satisfaction tinyint(3) unsigned NOT NULL,
			understanding tinyint(3) unsigned NOT NULL,
			influence tinyint(3) unsigned NOT NULL,
			discipline tinyint(3) unsigned NOT NULL,
			relevance tinyint(3) unsigned NOT NULL DEFAULT 0,
			engagement tinyint(3) unsigned NOT NULL DEFAULT 0,
			feedback tinyint(3) unsigned NOT NULL DEFAULT 0,
			fairness tinyint(3) unsigned NOT NULL DEFAULT 0,
			classroom_climate tinyint(3) unsigned NOT NULL DEFAULT 0,
			support tinyint(3) unsigned NOT NULL DEFAULT 0,
			story text NOT NULL,
			strength_story text NOT NULL,
			improvement_story text NOT NULL,
			message_story text NOT NULL,
			respondent_hash char(64) NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY respondent_teacher_period (teacher_id,academic_period,respondent_hash),
			UNIQUE KEY student_teacher_period (teacher_id,academic_period,student_hash),
			KEY teacher_period (teacher_id,academic_period),
			KEY class_period (class_group,academic_period),
			KEY period_created (academic_period,created_at)
		) {$charset};";

		dbDelta( $sql );
		update_option( self::DB_VERSION_OPTION, self::DB_VERSION, false );
	}

	/** Create the shortcode page without overwriting editor-managed content. */
	private static function ensure_page() {
		$page = get_page_by_path( self::PAGE_SLUG, OBJECT, 'page' );
		if ( $page instanceof WP_Post ) {
			if ( '' === trim( $page->post_content ) ) {
				wp_update_post(
					array(
						'ID'           => $page->ID,
						'post_content' => '<!-- wp:shortcode -->[' . self::SHORTCODE . ']<!-- /wp:shortcode -->',
					)
				);
			}
			return (int) $page->ID;
		}

		$result = wp_insert_post(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'post_title'     => 'Penilaian Kinerja Guru',
				'post_name'      => self::PAGE_SLUG,
				'post_excerpt'   => 'Evaluasi siswa yang bertanggung jawab untuk memberi umpan balik pembelajaran kepada guru SMK Queen Al-Falah.',
				'post_content'   => '<!-- wp:shortcode -->[' . self::SHORTCODE . ']<!-- /wp:shortcode -->',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			),
			true
		);

		return is_wp_error( $result ) ? 0 : (int) $result;
	}

	/** Public survey URL used by the application directory. */
	public static function public_url() {
		$page = get_page_by_path( self::PAGE_SLUG, OBJECT, 'page' );
		return $page instanceof WP_Post ? get_permalink( $page ) : home_url( '/' . self::PAGE_SLUG . '/' );
	}

	/** Load local survey assets only on the survey page. */
	public static function maybe_enqueue_assets() {
		$post = get_post();
		if ( ! $post instanceof WP_Post || ( self::PAGE_SLUG !== $post->post_name && ! has_shortcode( $post->post_content, self::SHORTCODE ) ) ) {
			return;
		}

		wp_enqueue_style(
			'qaf-teacher-evaluation',
			QAF_CORE_URL . 'assets/css/teacher-evaluation.css',
			array(),
			QAF_CORE_VERSION
		);
		wp_enqueue_script(
			'qaf-teacher-evaluation',
			QAF_CORE_URL . 'assets/js/teacher-evaluation.js',
			array(),
			QAF_CORE_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	/**
	 * Reflective, age-appropriate questions with contextual answer choices.
	 *
	 * Scores remain numeric for transparent aggregation, while every label asks
	 * students to recall an observable learning experience instead of choosing
	 * a generic satisfaction level.
	 */
	public static function question_definitions() {
		return array(
			'satisfaction' => array(
				'label'   => 'Ketika menjelaskan materi, pengalaman mana yang paling sering kamu rasakan?',
				'answers' => array( 5 => 'Penjelasan runtut, jelas, dan mudah saya ikuti', 4 => 'Umumnya jelas, hanya sedikit bagian perlu diulang', 3 => 'Sebagian jelas, sebagian masih membingungkan', 2 => 'Sering terlalu cepat atau kurang runtut', 1 => 'Saya hampir selalu kesulitan mengikuti penjelasan' ),
			),
			'understanding' => array(
				'label'   => 'Setelah satu topik selesai, apa yang biasanya dapat kamu lakukan?',
				'answers' => array( 5 => 'Menjelaskan kembali dan mengerjakan tugas tanpa bantuan', 4 => 'Memahami inti materi dan hanya perlu sedikit bantuan', 3 => 'Memahami dasar tetapi masih perlu contoh tambahan', 2 => 'Hanya memahami sebagian kecil materi', 1 => 'Belum memahami tujuan dan isi materi' ),
			),
			'relevance' => array(
				'label'   => 'Bagaimana guru menghubungkan materi dengan praktik, kehidupan, atau dunia kerja?',
				'answers' => array( 5 => 'Selalu memberi contoh nyata dan latihan yang relevan', 4 => 'Sering menghubungkan materi dengan contoh nyata', 3 => 'Kadang memberi contoh atau praktik yang relevan', 2 => 'Jarang menghubungkan materi dengan situasi nyata', 1 => 'Materi disampaikan tanpa contoh penggunaan yang saya pahami' ),
			),
			'engagement' => array(
				'label'   => 'Seberapa besar kesempatan siswa terlibat aktif selama pembelajaran?',
				'answers' => array( 5 => 'Semua siswa sering diajak bertanya, berdiskusi, dan mencoba', 4 => 'Banyak siswa mendapat kesempatan terlibat', 3 => 'Kesempatan terlibat ada tetapi belum merata', 2 => 'Hanya beberapa siswa yang sering dilibatkan', 1 => 'Pembelajaran hampir selalu satu arah' ),
			),
			'feedback' => array(
				'label'   => 'Apa yang terjadi setelah kamu mengerjakan tugas atau melakukan kesalahan?',
				'answers' => array( 5 => 'Guru memberi umpan balik jelas dan menunjukkan cara memperbaiki', 4 => 'Guru menjelaskan sebagian besar kekurangan pekerjaan', 3 => 'Saya mendapat nilai, tetapi arahan perbaikannya terbatas', 2 => 'Umpan balik jarang atau datang terlalu lama', 1 => 'Saya tidak mengetahui kesalahan dan cara memperbaikinya' ),
			),
			'fairness' => array(
				'label'   => 'Bagaimana guru memperlakukan dan menilai siswa di kelas?',
				'answers' => array( 5 => 'Adil, konsisten, dan tidak membeda-bedakan siswa', 4 => 'Umumnya adil dengan sedikit ketidakkonsistenan', 3 => 'Cukup adil tetapi aturan penilaian belum selalu jelas', 2 => 'Perlakuan atau penilaian sering terasa tidak konsisten', 1 => 'Saya sering melihat perlakuan atau penilaian yang tidak adil' ),
			),
			'classroom_climate' => array(
				'label'   => 'Saat ingin bertanya atau berpendapat, bagaimana suasana yang dibangun guru?',
				'answers' => array( 5 => 'Saya merasa aman, dihargai, dan tidak takut salah', 4 => 'Saya umumnya nyaman bertanya dan berpendapat', 3 => 'Saya cukup nyaman tetapi kadang masih ragu', 2 => 'Saya sering takut salah atau malu untuk bertanya', 1 => 'Saya merasa tidak aman atau tidak dihargai saat berbicara' ),
			),
			'support' => array(
				'label'   => 'Ketika siswa mengalami kesulitan, bagaimana guru memberi dukungan?',
				'answers' => array( 5 => 'Mencari tahu kesulitan dan memberi bantuan yang sesuai', 4 => 'Bersedia membantu ketika siswa meminta', 3 => 'Memberi bantuan tetapi belum cukup untuk semua siswa', 2 => 'Sulit ditemui atau bantuan yang diberikan sangat terbatas', 1 => 'Kesulitan siswa sering tidak ditanggapi' ),
			),
			'influence' => array(
				'label'   => 'Setelah belajar bersama guru ini, perubahan apa yang paling sesuai dengan dirimu?',
				'answers' => array( 5 => 'Saya lebih termotivasi, percaya diri, dan ingin berkembang', 4 => 'Motivasi dan kepercayaan diri saya meningkat', 3 => 'Ada pengaruh positif tetapi belum konsisten', 2 => 'Saya jarang merasa terdorong untuk berkembang', 1 => 'Saya justru kehilangan motivasi atau kepercayaan diri' ),
			),
			'discipline' => array(
				'label'   => 'Bagaimana konsistensi guru dalam hadir, memulai, dan menuntaskan pembelajaran?',
				'answers' => array( 5 => 'Selalu hadir tepat waktu dan pembelajaran selesai terarah', 4 => 'Hampir selalu hadir dan tepat waktu', 3 => 'Cukup disiplin dengan beberapa keterlambatan', 2 => 'Cukup sering terlambat, kosong, atau kegiatan tidak tuntas', 1 => 'Sering tidak hadir atau waktu belajar tidak digunakan dengan baik' ),
			),
		);
	}

	/** Exact class groups used for identity validation and reporting. */
	public static function class_options() {
		return array(
			'X LK', 'X MPLB', 'X DKV 1', 'X DKV 2', 'X DKV 3', 'X DKV 4', 'X TJKT 1', 'X TJKT 2', 'X TJKT 3',
			'XI LK', 'XI MPLB', 'XI DKV 1', 'XI DKV 2', 'XI DKV 3', 'XI DKV 4', 'XI TJKT 1', 'XI TJKT 2', 'XI TJKT 3', 'XI TJKT 4', 'XI TJKT 5',
			'XII LK', 'XII MPLB', 'XII DKV 1', 'XII DKV 2', 'XII DKV 3', 'XII DKV 4', 'XII TJKT 1', 'XII TJKT 2', 'XII TJKT 3', 'XII TJKT 4', 'XII TJKT 5', 'XII TJKT 6',
		);
	}

	/** Current semester label, kept as a snapshot on every response. */
	public static function current_period( $timestamp = null ) {
		$timestamp = null === $timestamp ? current_time( 'timestamp' ) : (int) $timestamp;
		$year      = (int) wp_date( 'Y', $timestamp, wp_timezone() );
		$month     = (int) wp_date( 'n', $timestamp, wp_timezone() );
		if ( $month >= 7 ) {
			return sprintf( '%d/%d Ganjil', $year, $year + 1 );
		}
		return sprintf( '%d/%d Genap', $year - 1, $year );
	}

	/** Return published Guru profiles, excluding Tendik where taxonomy data exists. */
	private static function teachers() {
		$posts = get_posts(
			array(
				'post_type'              => 'qaf_teacher',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'orderby'                => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
				'no_found_rows'          => true,
				'suppress_filters'       => false,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => true,
			)
		);

		return array_values(
			array_filter(
				$posts,
				static function ( $post ) {
					$terms = get_the_terms( $post, 'qaf_staff_type' );
					if ( ! is_array( $terms ) ) {
						return true;
					}
					foreach ( $terms as $term ) {
						if ( in_array( sanitize_title( $term->name ), array( 'tendik', 'tenaga-kependidikan' ), true ) ) {
							return false;
						}
					}
					return true;
				}
			)
		);
	}

	/** Render the accountable student survey. */
	public static function render_shortcode() {
		$teachers = self::teachers();
		$status   = isset( $_GET['qaf_evaluation'] ) ? sanitize_key( wp_unslash( $_GET['qaf_evaluation'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only status message.
		ob_start();
		?>
		<section class="qaf-evaluation" aria-labelledby="qaf-evaluation-title">
			<header class="qaf-evaluation__hero">
				<p class="qaf-evaluation__eyebrow">Suara Siswa untuk Pembelajaran Lebih Baik</p>
				<h1 id="qaf-evaluation-title">Penilaian Kinerja Guru</h1>
				<p>Renungkan pengalaman belajarmu bersama guru yang dipilih, lalu berikan jawaban yang jujur, sopan, dan berdasarkan kejadian yang benar-benar kamu alami.</p>
			</header>

			<?php if ( 'success' === $status ) : ?>
				<div class="qaf-evaluation__notice qaf-evaluation__notice--success" role="status"><strong>Terima kasih.</strong> Penilaianmu sudah tersimpan dan akan menjadi bahan evaluasi pembelajaran.</div>
			<?php elseif ( 'duplicate' === $status ) : ?>
				<div class="qaf-evaluation__notice" role="status">Kamu sudah mengisi penilaian untuk guru ini pada semester yang sama.</div>
			<?php elseif ( 'invalid' === $status || 'error' === $status ) : ?>
				<div class="qaf-evaluation__notice qaf-evaluation__notice--error" role="alert">Jawaban belum dapat disimpan. Periksa kembali semua pilihan lalu kirim ulang.</div>
			<?php elseif ( 'wait' === $status ) : ?>
				<div class="qaf-evaluation__notice" role="status">Mohon tunggu sebentar sebelum mengirim penilaian berikutnya.</div>
			<?php endif; ?>

			<?php if ( empty( $teachers ) ) : ?>
				<div class="qaf-evaluation__notice">Daftar guru sedang disiapkan oleh sekolah.</div>
			<?php else : ?>
				<form class="qaf-evaluation__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" data-qaf-evaluation-form>
					<input type="hidden" name="action" value="<?php echo esc_attr( self::SUBMIT_ACTION ); ?>">
					<input type="hidden" name="<?php echo esc_attr( self::TOKEN_FIELD ); ?>" value="" data-qaf-evaluation-token>
					<?php wp_nonce_field( self::NONCE_ACTION, 'qaf_evaluation_nonce' ); ?>
					<div class="qaf-evaluation__trap" aria-hidden="true"><label>Jangan isi bidang ini <input type="text" name="qaf_website" value="" tabindex="-1" autocomplete="off"></label></div>

					<div class="qaf-evaluation__identity">
						<label for="qaf-student-name"><span>Nama lengkap siswa</span>
							<input id="qaf-student-name" type="text" name="student_name" minlength="3" maxlength="80" autocomplete="name" required placeholder="Tulis nama sesuai daftar kelas">
							<small>Nama digunakan untuk mencegah pengisian berulang dan hanya terlihat oleh administrator sekolah.</small>
						</label>
						<label for="qaf-class"><span>Kelasmu saat ini</span>
							<select id="qaf-class" name="class_group" required>
								<option value="">Pilih kelas lengkap</option>
								<?php foreach ( self::class_options() as $class_group ) : ?>
									<option value="<?php echo esc_attr( $class_group ); ?>"><?php echo esc_html( $class_group ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<label for="qaf-teacher"><span>Guru dan mata pelajaran</span>
							<select id="qaf-teacher" name="teacher_id" required>
								<option value="">Pilih guru yang dinilai</option>
								<?php foreach ( $teachers as $teacher ) : ?>
									<?php $subject = sanitize_text_field( get_post_meta( $teacher->ID, '_qaf_subject', true ) ); ?>
									<option value="<?php echo esc_attr( $teacher->ID ); ?>"><?php echo esc_html( $teacher->post_title . ( $subject ? ' — ' . $subject : '' ) ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					</div>

					<p class="qaf-evaluation__scale-note"><strong>Cara menjawab:</strong> pilih pernyataan yang paling sering terjadi selama kamu belajar bersama guru ini. Jangan menilai hanya dari satu pertemuan atau karena suka/tidak suka secara pribadi.</p>
					<?php foreach ( self::question_definitions() as $key => $question ) : ?>
						<fieldset class="qaf-evaluation__question">
							<legend><?php echo esc_html( $question['label'] ); ?></legend>
							<div class="qaf-evaluation__answers">
								<?php foreach ( $question['answers'] as $score => $label ) : ?>
									<label><input type="radio" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $score ); ?>" required><span><?php echo esc_html( $label ); ?></span></label>
								<?php endforeach; ?>
							</div>
						</fieldset>
					<?php endforeach; ?>

					<div class="qaf-evaluation__reflection" aria-labelledby="qaf-reflection-title">
						<h2 id="qaf-reflection-title">Refleksi pengalaman belajar</h2>
						<p>Berikan contoh yang spesifik tanpa menghina, mengancam, atau menyebut data pribadi siswa lain.</p>
						<label class="qaf-evaluation__story" for="qaf-strength-story">
							<span>Pengalaman apa yang paling membantu kamu belajar bersama guru ini?</span>
							<small>Contohkan cara menjelaskan, praktik, dukungan, atau sikap guru yang perlu dipertahankan.</small>
							<textarea id="qaf-strength-story" name="strength_story" rows="5" minlength="20" maxlength="1000" required placeholder="Contoh: Saat praktik, guru memberi contoh langkah demi langkah lalu mengecek pekerjaan setiap kelompok." data-qaf-story data-counter-target="qaf-strength-count"></textarea>
							<small class="qaf-evaluation__counter"><span id="qaf-strength-count">0</span>/1000 karakter</small>
						</label>
						<label class="qaf-evaluation__story" for="qaf-improvement-story">
							<span>Perubahan apa yang paling kamu butuhkan agar pembelajaran menjadi lebih baik?</span>
							<small>Jelaskan situasi dan dampaknya terhadap pemahaman, keberanian, atau keterlibatan siswa.</small>
							<textarea id="qaf-improvement-story" name="improvement_story" rows="5" minlength="20" maxlength="1000" required placeholder="Contoh: Setelah tugas, kami membutuhkan penjelasan tentang bagian yang salah agar tidak mengulang kesalahan yang sama." data-qaf-story data-counter-target="qaf-improvement-count"></textarea>
							<small class="qaf-evaluation__counter"><span id="qaf-improvement-count">0</span>/1000 karakter</small>
						</label>
						<label class="qaf-evaluation__story" for="qaf-message-story">
							<span>Pesan atau harapan untuk guru <em>(opsional)</em></span>
							<textarea id="qaf-message-story" name="message_story" rows="4" maxlength="600" placeholder="Sampaikan apresiasi atau harapanmu dengan bahasa yang baik." data-qaf-story data-counter-target="qaf-message-count"></textarea>
							<small class="qaf-evaluation__counter"><span id="qaf-message-count">0</span>/600 karakter</small>
						</label>
					</div>

					<div class="qaf-evaluation__privacy"><strong>Akuntabilitas dan privasi:</strong> nama dan kelas disimpan untuk mencegah pengisian berulang. Identitas serta jawaban rinci hanya dapat dilihat administrator sekolah, tidak ditampilkan pada halaman publik, dan tidak dikirim ke layanan AI eksternal.</div>
					<button class="qaf-evaluation__submit" type="submit">Kirim Penilaian</button>
				</form>
			<?php endif; ?>
		</section>
		<?php
		return ob_get_clean();
	}

	/** Validate and persist one accountable response. */
	public static function handle_submission() {
		$redirect = self::public_url();
		$nonce    = isset( $_POST['qaf_evaluation_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['qaf_evaluation_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) || ! empty( $_POST['qaf_website'] ) ) {
			wp_safe_redirect( add_query_arg( 'qaf_evaluation', 'invalid', $redirect ) );
			exit;
		}

		$student_name = isset( $_POST['student_name'] ) ? self::sanitize_student_name( wp_unslash( $_POST['student_name'] ) ) : '';
		$teacher_id = isset( $_POST['teacher_id'] ) ? absint( $_POST['teacher_id'] ) : 0;
		$class_group = isset( $_POST['class_group'] ) ? sanitize_text_field( wp_unslash( $_POST['class_group'] ) ) : '';
		$token       = isset( $_POST[ self::TOKEN_FIELD ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::TOKEN_FIELD ] ) ) : '';
		$teacher     = get_post( $teacher_id );
		$questions   = self::question_definitions();
		$scores      = array();

		foreach ( array_keys( $questions ) as $key ) {
			$value = isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : 0;
			if ( $value < 1 || $value > 5 ) {
				wp_safe_redirect( add_query_arg( 'qaf_evaluation', 'invalid', $redirect ) );
				exit;
			}
			$scores[ $key ] = $value;
		}

		if (
			'' === $student_name ||
			! $teacher instanceof WP_Post ||
			'qaf_teacher' !== $teacher->post_type ||
			'publish' !== $teacher->post_status ||
			! in_array( $class_group, self::class_options(), true ) ||
			! preg_match( '/^[a-f0-9]{32}$/D', $token )
		) {
			wp_safe_redirect( add_query_arg( 'qaf_evaluation', 'invalid', $redirect ) );
			exit;
		}

		$period   = self::current_period();
		$hash     = hash_hmac( 'sha256', $token, wp_salt( 'nonce' ) );
		$name_key = function_exists( 'mb_strtolower' ) ? mb_strtolower( $student_name, 'UTF-8' ) : strtolower( $student_name );
		$name_key = preg_replace( '/\s+/u', ' ', trim( $name_key ) );
		$student_hash = hash_hmac( 'sha256', $name_key . '|' . $class_group . '|' . $period, wp_salt( 'auth' ) );
		$rate_key = 'qaf_teacher_eval_' . substr( $hash, 0, 32 );
		if ( get_transient( $rate_key ) ) {
			wp_safe_redirect( add_query_arg( 'qaf_evaluation', 'wait', $redirect ) );
			exit;
		}
		set_transient( $rate_key, 1, 30 );

		global $wpdb;
		$table  = self::table_name();
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE teacher_id = %d AND academic_period = %s AND (respondent_hash = %s OR student_hash = %s) LIMIT 1",
				$teacher_id,
				$period,
				$hash,
				$student_hash
			)
		);
		if ( $exists ) {
			wp_safe_redirect( add_query_arg( 'qaf_evaluation', 'duplicate', $redirect ) );
			exit;
		}

		$strength_story    = isset( $_POST['strength_story'] ) ? self::sanitize_reflection( wp_unslash( $_POST['strength_story'] ), 1000 ) : '';
		$improvement_story = isset( $_POST['improvement_story'] ) ? self::sanitize_reflection( wp_unslash( $_POST['improvement_story'] ), 1000 ) : '';
		$message_story     = isset( $_POST['message_story'] ) ? self::sanitize_reflection( wp_unslash( $_POST['message_story'] ), 600 ) : '';
		if ( self::text_length( $strength_story ) < 20 || self::text_length( $improvement_story ) < 20 ) {
			wp_safe_redirect( add_query_arg( 'qaf_evaluation', 'invalid', $redirect ) );
			exit;
		}
		$story = "Hal yang membantu:\n{$strength_story}\n\nPerubahan yang dibutuhkan:\n{$improvement_story}";
		if ( '' !== $message_story ) {
			$story .= "\n\nPesan atau harapan:\n{$message_story}";
		}
		$saved = $wpdb->insert(
			$table,
			array(
				'created_at'       => current_time( 'mysql' ),
				'academic_period'  => $period,
				'student_name'     => $student_name,
				'student_hash'     => $student_hash,
				'teacher_id'       => $teacher_id,
				'teacher_name'     => sanitize_text_field( $teacher->post_title ),
				'subject'          => sanitize_text_field( get_post_meta( $teacher_id, '_qaf_subject', true ) ),
				'class_group'      => $class_group,
				'satisfaction'     => $scores['satisfaction'],
				'understanding'    => $scores['understanding'],
				'relevance'        => $scores['relevance'],
				'engagement'       => $scores['engagement'],
				'feedback'         => $scores['feedback'],
				'fairness'         => $scores['fairness'],
				'classroom_climate' => $scores['classroom_climate'],
				'support'          => $scores['support'],
				'influence'        => $scores['influence'],
				'discipline'       => $scores['discipline'],
				'story'            => $story,
				'strength_story'   => $strength_story,
				'improvement_story' => $improvement_story,
				'message_story'    => $message_story,
				'respondent_hash'  => $hash,
			),
			array( '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		wp_safe_redirect( add_query_arg( 'qaf_evaluation', false === $saved ? 'error' : 'success', $redirect ) );
		exit;
	}

	/** Validate a human name without accepting markup, numbers, or controls. */
	private static function sanitize_student_name( $value ) {
		$value = sanitize_text_field( is_scalar( $value ) ? (string) $value : '' );
		$value = preg_replace( '/\s+/u', ' ', trim( $value ) );
		$length = self::text_length( $value );
		if ( $length < 3 || $length > 80 || ! preg_match( "/^[\p{L}\p{M} .’'\-]+$/u", $value ) ) {
			return '';
		}
		return $value;
	}

	/** Normalize one reflective response and enforce its storage limit. */
	private static function sanitize_reflection( $value, $limit ) {
		$value = sanitize_textarea_field( is_scalar( $value ) ? (string) $value : '' );
		return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $limit, 'UTF-8' ) : substr( $value, 0, $limit );
	}

	/** Unicode-aware text length. */
	private static function text_length( $value ) {
		return function_exists( 'mb_strlen' ) ? mb_strlen( (string) $value, 'UTF-8' ) : strlen( (string) $value );
	}

	/** Register the administrator-only analysis dashboard. */
	public static function register_admin_page() {
		add_submenu_page(
			'qaf-school',
			'Analisis Penilaian Guru',
			'Penilaian Guru',
			'manage_options',
			self::ADMIN_SLUG,
			array( __CLASS__, 'render_admin_page' )
		);
	}

	/** Human-readable labels for each scored aspect. */
	public static function metric_labels() {
		return array(
			'satisfaction'  => 'Kejelasan penjelasan',
			'understanding' => 'Pemahaman materi',
			'relevance'     => 'Relevansi dan praktik',
			'engagement'    => 'Keterlibatan siswa',
			'feedback'      => 'Umpan balik tugas',
			'fairness'      => 'Keadilan perlakuan',
			'classroom_climate' => 'Rasa aman di kelas',
			'support'       => 'Dukungan saat kesulitan',
			'influence'     => 'Motivasi dan percaya diri',
			'discipline'    => 'Kedisiplinan',
		);
	}

	/**
	 * Transparent rule-based analysis: averages, strengths, priorities, and themes.
	 *
	 * @param array<int,array<string,mixed>|object> $rows Response rows.
	 * @return array<string,mixed>
	 */
	public static function analyze_responses( $rows ) {
		$labels = self::metric_labels();
		$totals = array_fill_keys( array_keys( $labels ), 0.0 );
		$count  = count( $rows );
		$stories = array();
		$class_breakdown = array();
		$fallbacks = array(
			'relevance'         => 'understanding',
			'engagement'        => 'influence',
			'feedback'          => 'satisfaction',
			'fairness'          => 'satisfaction',
			'classroom_climate' => 'influence',
			'support'           => 'influence',
		);

		foreach ( $rows as $row ) {
			$row = is_object( $row ) ? get_object_vars( $row ) : $row;
			foreach ( $totals as $key => $unused ) {
				$value = isset( $row[ $key ] ) ? (int) $row[ $key ] : 0;
				if ( $value < 1 && isset( $fallbacks[ $key ] ) && isset( $row[ $fallbacks[ $key ] ] ) ) {
					$value = (int) $row[ $fallbacks[ $key ] ];
				}
				$value          = max( 1, min( 5, $value ) );
				$totals[ $key ] += $value;
			}
			foreach ( array( 'strength_story', 'improvement_story', 'message_story' ) as $story_key ) {
				if ( ! empty( $row[ $story_key ] ) ) {
					$stories[] = (string) $row[ $story_key ];
				}
			}
			if ( empty( $row['strength_story'] ) && empty( $row['improvement_story'] ) && ! empty( $row['story'] ) ) {
				$stories[] = (string) $row['story'];
			}
			if ( ! empty( $row['class_group'] ) ) {
				$class = sanitize_text_field( (string) $row['class_group'] );
				$class_breakdown[ $class ] = isset( $class_breakdown[ $class ] ) ? $class_breakdown[ $class ] + 1 : 1;
			}
		}

		$averages = array();
		foreach ( $totals as $key => $total ) {
			$averages[ $key ] = $count ? round( $total / $count, 2 ) : 0.0;
		}
		$overall = $count ? round( array_sum( $averages ) / count( $averages ), 2 ) : 0.0;
		$rating  = self::rating_label( $overall );
		$sorted  = $averages;
		arsort( $sorted );
		$best_key = (string) array_key_first( $sorted );
		asort( $sorted );
		$low_key = (string) array_key_first( $sorted );

		$themes = self::story_themes( $stories );
		$recommendations = self::build_recommendations( $averages, $labels );
		arsort( $class_breakdown );
		if ( 0 === $count ) {
			$conclusion = 'Belum ada jawaban yang dapat dianalisis.';
		} else {
			$conclusion = sprintf(
				'Dari %1$d respons, kinerja pembelajaran berada pada kategori %2$s dengan nilai rata-rata %3$s dari 5. Aspek terkuat adalah %4$s (%5$s), sedangkan %6$s (%7$s) menjadi prioritas refleksi berikutnya.',
				$count,
				strtolower( $rating ),
				number_format( $overall, 2, ',', '.' ),
				strtolower( $labels[ $best_key ] ),
				number_format( $averages[ $best_key ], 2, ',', '.' ),
				strtolower( $labels[ $low_key ] ),
				number_format( $averages[ $low_key ], 2, ',', '.' )
			);
		}

		return array(
			'count'      => $count,
			'averages'   => $averages,
			'overall'    => $overall,
			'rating'     => $rating,
			'best_key'   => $best_key,
			'low_key'    => $low_key,
			'themes'     => $themes,
			'class_breakdown' => $class_breakdown,
			'recommendations' => $recommendations,
			'conclusion' => $conclusion,
		);
	}

	/** Build practical suggestions from the three lowest-scoring aspects. */
	private static function build_recommendations( $averages, $labels ) {
		$actions = array(
			'satisfaction'      => 'Gunakan alur pembuka–contoh–latihan–rangkuman dan cek pemahaman sebelum berpindah topik.',
			'understanding'     => 'Tambahkan asesmen singkat, remedial terarah, dan kesempatan menjelaskan kembali dengan bahasa siswa.',
			'relevance'         => 'Hubungkan materi dengan praktik program keahlian, studi kasus, atau situasi dunia kerja.',
			'engagement'        => 'Gunakan diskusi kelompok, giliran bertanya, demonstrasi, dan pembagian peran agar keterlibatan lebih merata.',
			'feedback'          => 'Berikan komentar yang menyebut bagian sudah baik, kesalahan utama, dan langkah perbaikan berikutnya.',
			'fairness'          => 'Jelaskan rubrik dan aturan kelas sejak awal serta terapkan secara konsisten kepada semua siswa.',
			'classroom_climate' => 'Bangun kebiasaan menanggapi pertanyaan tanpa mempermalukan siswa dan tegaskan bahwa salah adalah bagian belajar.',
			'support'           => 'Sediakan waktu bantuan, pemetaan kesulitan, dan tindak lanjut bagi siswa yang tertinggal.',
			'influence'         => 'Berikan tujuan yang bermakna, apresiasi atas proses, dan harapan realistis agar siswa percaya diri berkembang.',
			'discipline'        => 'Perkuat ketepatan waktu, kesiapan perangkat ajar, dan rencana pengganti saat berhalangan hadir.',
		);
		$sorted = $averages;
		asort( $sorted );
		$result = array();
		foreach ( array_slice( $sorted, 0, 3, true ) as $key => $score ) {
			$result[] = array(
				'aspect' => isset( $labels[ $key ] ) ? $labels[ $key ] : $key,
				'score'  => (float) $score,
				'action' => isset( $actions[ $key ] ) ? $actions[ $key ] : '',
			);
		}
		return $result;
	}

	/** Convert an average into a clear improvement band. */
	private static function rating_label( $score ) {
		if ( $score >= 4.5 ) { return 'Sangat baik'; }
		if ( $score >= 4.0 ) { return 'Baik'; }
		if ( $score >= 3.25 ) { return 'Cukup baik'; }
		if ( $score >= 2.5 ) { return 'Perlu perhatian'; }
		return 'Prioritas perbaikan';
	}

	/** Extract recurring, predefined themes without sending comments externally. */
	private static function story_themes( $stories ) {
		$dictionary = array(
			'Penjelasan jelas'      => array( 'jelas', 'mudah dipahami', 'mengerti', 'paham' ),
			'Sabar dan membantu'    => array( 'sabar', 'membantu', 'membimbing', 'peduli' ),
			'Memotivasi siswa'      => array( 'motivasi', 'semangat', 'percaya diri', 'inspirasi' ),
			'Pembelajaran menarik'  => array( 'menyenangkan', 'menarik', 'kreatif', 'praktik' ),
			'Tempo perlu disesuaikan' => array( 'terlalu cepat', 'kecepatan', 'terburu', 'pelan' ),
			'Perlu lebih disiplin'  => array( 'terlambat', 'tidak hadir', 'jarang masuk', 'sering kosong' ),
			'Perlu contoh tambahan' => array( 'contoh', 'latihan', 'kurang jelas', 'bingung' ),
			'Butuh umpan balik'     => array( 'umpan balik', 'dikoreksi', 'kesalahan', 'nilai saja', 'cara memperbaiki' ),
			'Pelibatan belum merata' => array( 'satu arah', 'tidak dilibatkan', 'hanya beberapa', 'jarang ditanya' ),
			'Keadilan dan konsistensi' => array( 'tidak adil', 'pilih kasih', 'dibeda', 'tidak konsisten' ),
			'Rasa aman perlu dijaga' => array( 'takut bertanya', 'dipermalukan', 'diejek', 'tidak dihargai', 'takut salah' ),
			'Butuh dukungan belajar' => array( 'kesulitan', 'tertinggal', 'remedial', 'bantuan', 'didampingi' ),
		);
		$counts = array_fill_keys( array_keys( $dictionary ), 0 );
		foreach ( $stories as $story ) {
			$text = function_exists( 'mb_strtolower' ) ? mb_strtolower( $story ) : strtolower( $story );
			foreach ( $dictionary as $theme => $keywords ) {
				foreach ( $keywords as $keyword ) {
					if ( false !== strpos( $text, $keyword ) ) {
						++$counts[ $theme ];
						break;
					}
				}
			}
		}
		$counts = array_filter( $counts );
		arsort( $counts );
		return array_slice( $counts, 0, 8, true );
	}

	/** Render anonymized aggregate reports and comments for administrators. */
	public static function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Anda tidak memiliki izin untuk melihat laporan ini.', 'queen-alfalah-core' ) );
		}

		global $wpdb;
		$table      = self::table_name();
		$periods    = $wpdb->get_col( "SELECT DISTINCT academic_period FROM {$table} ORDER BY academic_period DESC" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Trusted table name and fixed query.
		$current    = self::current_period();
		$periods    = array_values( array_unique( array_merge( array( $current ), is_array( $periods ) ? $periods : array() ) ) );
		$period     = isset( $_GET['period'] ) ? sanitize_text_field( wp_unslash( $_GET['period'] ) ) : $current; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only report filter.
		if ( ! in_array( $period, $periods, true ) ) { $period = $current; }
		$teacher_id = isset( $_GET['teacher_id'] ) ? absint( $_GET['teacher_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only report filter.

		$summary_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT teacher_id, teacher_name, subject, COUNT(*) AS responses,
				AVG(satisfaction) AS satisfaction,
				AVG(understanding) AS understanding,
				COALESCE(AVG(NULLIF(relevance,0)),AVG(understanding)) AS relevance,
				COALESCE(AVG(NULLIF(engagement,0)),AVG(influence)) AS engagement,
				COALESCE(AVG(NULLIF(feedback,0)),AVG(satisfaction)) AS feedback,
				COALESCE(AVG(NULLIF(fairness,0)),AVG(satisfaction)) AS fairness,
				COALESCE(AVG(NULLIF(classroom_climate,0)),AVG(influence)) AS classroom_climate,
				COALESCE(AVG(NULLIF(support,0)),AVG(influence)) AS support,
				AVG(influence) AS influence,
				AVG(discipline) AS discipline
				FROM {$table} WHERE academic_period = %s GROUP BY teacher_id, teacher_name, subject ORDER BY teacher_name ASC",
				$period
			),
			ARRAY_A
		);
		$detail_rows = $teacher_id ? $wpdb->get_results(
			$wpdb->prepare(
				"SELECT created_at, student_name, class_group, satisfaction, understanding, relevance, engagement, feedback, fairness, classroom_climate, support, influence, discipline, story, strength_story, improvement_story, message_story FROM {$table} WHERE academic_period = %s AND teacher_id = %d ORDER BY created_at DESC LIMIT 500",
				$period,
				$teacher_id
			),
			ARRAY_A
		) : array();
		$analysis    = self::analyze_responses( $detail_rows );
		$labels      = self::metric_labels();
		?>
		<div class="wrap qaf-evaluation-admin">
			<h1>Analisis Penilaian Kinerja Guru</h1>
			<p>Ringkasan memakai rata-rata sepuluh aspek, pemetaan tema, dan rekomendasi berbasis aturan yang transparan. Data tidak dikirim ke layanan AI atau pihak luar. Nama siswa hanya tampil pada rincian administrator untuk akuntabilitas dan pemeriksaan pengisian ganda.</p>
			<form method="get" class="qaf-evaluation-admin__filters">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::ADMIN_SLUG ); ?>">
				<label>Semester <select name="period"><?php foreach ( $periods as $item ) : ?><option value="<?php echo esc_attr( $item ); ?>"<?php selected( $period, $item ); ?>><?php echo esc_html( $item ); ?></option><?php endforeach; ?></select></label>
				<label>Guru <select name="teacher_id"><option value="0">Pilih guru untuk melihat rincian</option><?php foreach ( $summary_rows as $row ) : ?><option value="<?php echo esc_attr( $row['teacher_id'] ); ?>"<?php selected( $teacher_id, (int) $row['teacher_id'] ); ?>><?php echo esc_html( $row['teacher_name'] . ( $row['subject'] ? ' — ' . $row['subject'] : '' ) ); ?></option><?php endforeach; ?></select></label>
				<button class="button button-primary">Tampilkan</button>
			</form>

			<h2>Ringkasan semua guru</h2>
			<table class="widefat striped"><thead><tr><th>Guru / Mapel</th><th>Respons</th><?php foreach ( $labels as $label ) : ?><th><?php echo esc_html( $label ); ?></th><?php endforeach; ?><th>Rata-rata</th></tr></thead><tbody>
			<?php if ( empty( $summary_rows ) ) : ?><tr><td colspan="13">Belum ada penilaian pada semester ini.</td></tr><?php endif; ?>
			<?php foreach ( $summary_rows as $row ) : ?>
				<?php $overall = array_sum( array_map( static function ( $key ) use ( $row ) { return (float) $row[ $key ]; }, array_keys( $labels ) ) ) / count( $labels ); ?>
				<tr><td><a href="<?php echo esc_url( add_query_arg( array( 'page' => self::ADMIN_SLUG, 'period' => $period, 'teacher_id' => $row['teacher_id'] ), admin_url( 'admin.php' ) ) ); ?>"><strong><?php echo esc_html( $row['teacher_name'] ); ?></strong></a><br><small><?php echo esc_html( $row['subject'] ); ?></small></td><td><?php echo esc_html( number_format_i18n( $row['responses'] ) ); ?></td><?php foreach ( array_keys( $labels ) as $key ) : ?><td><?php echo esc_html( number_format_i18n( $row[ $key ], 2 ) ); ?></td><?php endforeach; ?><td><strong><?php echo esc_html( number_format_i18n( $overall, 2 ) ); ?></strong></td></tr>
			<?php endforeach; ?>
			</tbody></table>

			<?php if ( $teacher_id ) : ?>
				<section class="qaf-evaluation-admin__detail">
					<h2>Analisis guru terpilih</h2>
					<div class="qaf-evaluation-admin__headline"><strong><?php echo esc_html( number_format_i18n( $analysis['overall'], 2 ) ); ?>/5</strong><span><?php echo esc_html( $analysis['rating'] ); ?> · <?php echo esc_html( number_format_i18n( $analysis['count'] ) ); ?> respons</span></div>
					<p class="qaf-evaluation-admin__conclusion"><?php echo esc_html( $analysis['conclusion'] ); ?></p>
					<div class="qaf-evaluation-admin__chart" role="img" aria-label="Grafik nilai rata-rata setiap aspek dari skala satu sampai lima">
						<?php foreach ( $labels as $key => $label ) : ?>
							<div class="qaf-evaluation-admin__bar"><span><?php echo esc_html( $label ); ?></span><div><i style="width:<?php echo esc_attr( 20 * $analysis['averages'][ $key ] ); ?>%"></i></div><b><?php echo esc_html( number_format_i18n( $analysis['averages'][ $key ], 2 ) ); ?></b></div>
						<?php endforeach; ?>
					</div>
					<h3>Tema yang sering muncul</h3>
					<?php if ( $analysis['themes'] ) : ?><ul class="qaf-evaluation-admin__themes"><?php foreach ( $analysis['themes'] as $theme => $mentions ) : ?><li><?php echo esc_html( $theme ); ?> <span><?php echo esc_html( number_format_i18n( $mentions ) ); ?> sebutan</span></li><?php endforeach; ?></ul><?php else : ?><p>Belum cukup komentar untuk menemukan tema berulang.</p><?php endif; ?>
					<h3>Saran tindak lanjut otomatis</h3>
					<ol class="qaf-evaluation-admin__recommendations">
						<?php foreach ( $analysis['recommendations'] as $recommendation ) : ?>
							<li><strong><?php echo esc_html( $recommendation['aspect'] ); ?> (<?php echo esc_html( number_format_i18n( $recommendation['score'], 2 ) ); ?>)</strong><br><?php echo esc_html( $recommendation['action'] ); ?></li>
						<?php endforeach; ?>
					</ol>
					<h3>Sebaran kelas responden</h3>
					<?php if ( $analysis['class_breakdown'] ) : ?><ul class="qaf-evaluation-admin__themes"><?php foreach ( $analysis['class_breakdown'] as $class => $responses ) : ?><li><?php echo esc_html( $class ); ?> <span><?php echo esc_html( number_format_i18n( $responses ) ); ?> respons</span></li><?php endforeach; ?></ul><?php else : ?><p>Data kelas belum tersedia.</p><?php endif; ?>
					<h3>Refleksi siswa</h3>
					<div class="qaf-evaluation-admin__stories">
					<?php $story_count = 0; foreach ( $detail_rows as $row ) : if ( '' === trim( $row['story'] ) ) { continue; } ++$story_count; if ( $story_count > 50 ) { break; } ?><blockquote><p><?php echo nl2br( esc_html( $row['story'] ) ); ?></p><footer><strong><?php echo esc_html( $row['student_name'] ? $row['student_name'] : 'Identitas belum tersedia (data lama)' ); ?></strong> · <?php echo esc_html( $row['class_group'] ); ?> · <?php echo esc_html( mysql2date( 'j F Y', $row['created_at'] ) ); ?></footer></blockquote><?php endforeach; ?>
					<?php if ( 0 === $story_count ) : ?><p>Belum ada cerita tertulis.</p><?php endif; ?>
					</div>
				</section>
			<?php endif; ?>
		</div>
		<style>
		.qaf-evaluation-admin__filters{display:flex;flex-wrap:wrap;gap:12px;align-items:end;margin:18px 0 24px;padding:16px;background:#fff;border:1px solid #dcdcde}.qaf-evaluation-admin__filters label{display:grid;gap:5px}.qaf-evaluation-admin__filters select{min-width:220px}.qaf-evaluation-admin .widefat{display:block;max-width:100%;overflow-x:auto;white-space:nowrap}.qaf-evaluation-admin__detail{margin-top:28px;padding:22px;background:#fff;border:1px solid #dcdcde}.qaf-evaluation-admin__headline{display:flex;align-items:baseline;gap:15px}.qaf-evaluation-admin__headline strong{font-size:36px;color:#0b5d4b}.qaf-evaluation-admin__conclusion{max-width:920px;font-size:15px}.qaf-evaluation-admin__chart{display:grid;gap:12px;max-width:900px;margin:24px 0}.qaf-evaluation-admin__bar{display:grid;grid-template-columns:190px 1fr 48px;gap:12px;align-items:center}.qaf-evaluation-admin__bar div{height:16px;overflow:hidden;border-radius:999px;background:#e8ecea}.qaf-evaluation-admin__bar i{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,#0b5d4b,#d4a72c)}.qaf-evaluation-admin__themes{display:flex;flex-wrap:wrap;gap:8px;padding:0;list-style:none}.qaf-evaluation-admin__themes li{padding:8px 12px;border-radius:999px;background:#edf7f3}.qaf-evaluation-admin__themes span{color:#5b6864}.qaf-evaluation-admin__recommendations{display:grid;gap:10px;max-width:920px}.qaf-evaluation-admin__recommendations li{padding:12px 14px;border-left:4px solid #0b5d4b;background:#f3f8f6}.qaf-evaluation-admin__stories{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:12px}.qaf-evaluation-admin__stories blockquote{margin:0;padding:16px;border-left:4px solid #d4a72c;background:#f7f7f7}.qaf-evaluation-admin__stories footer{color:#646970;font-size:12px}@media(max-width:782px){.qaf-evaluation-admin__bar{grid-template-columns:1fr 42px}.qaf-evaluation-admin__bar>span{grid-column:1/-1}.qaf-evaluation-admin__filters label,.qaf-evaluation-admin__filters select{width:100%}}
		</style>
		<?php
	}
}
