<?php
/**
 * October content-completeness and governance audit.
 *
 * @package Queen_Alfalah_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provide a read-only dashboard for the October work-program priorities.
 */
final class QAF_Core_October_Audit {
	/** Admin page slug. */
	const MENU_SLUG = 'qaf-october-content-audit';

	/**
	 * Register the administrator screen.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_page' ), 45 );
	}

	/**
	 * Add the audit below the existing School menu.
	 *
	 * @return void
	 */
	public static function register_page() {
		add_submenu_page(
			'qaf-school',
			'Kelengkapan Konten Oktober',
			'Audit Kelengkapan',
			'manage_options',
			self::MENU_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Content contracts for the four October priority areas.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function get_scope() {
		return array(
			'qaf_program' => array(
				'label' => 'Program Keahlian',
				'required_meta' => array(
					'_qaf_program_code' => 'kode program',
					'_qaf_program_head' => 'kepala program',
					'_qaf_competencies' => 'kompetensi utama',
					'_qaf_careers'      => 'prospek karier',
				),
			),
			'qaf_teacher' => array(
				'label' => 'Guru & Tendik',
				'required_meta' => array(
					'_qaf_role' => 'jabatan/peran',
				),
			),
			'qaf_facility' => array(
				'label' => 'Sarana Prasarana',
				'required_meta' => array(
					'_qaf_facility_location'   => 'lokasi',
					'_qaf_facility_function'   => 'fungsi utama',
					'_qaf_facility_features'   => 'fasilitas/perlengkapan',
					'_qaf_facility_manager'    => 'penanggung jawab sarana',
					'_qaf_facility_status'     => 'status sarana',
					'_qaf_facility_last_check' => 'tanggal pemeriksaan kondisi',
				),
			),
			'qaf_extra' => array(
				'label' => 'Ekstrakurikuler',
				'required_meta' => array(
					'_qaf_schedule'         => 'jadwal latihan',
					'_qaf_coach'            => 'pembina/pelatih',
					'_qaf_benefits'         => 'manfaat kegiatan',
					'_qaf_career_relevance' => 'relevansi studi/dunia kerja',
					'_qaf_join_info'        => 'informasi bergabung',
				),
			),
		);
	}

	/**
	 * Inspect one published item without changing it.
	 *
	 * @param int                 $post_id Post ID.
	 * @param array<string,mixed> $definition Audit definition.
	 * @return array<string,array<int,string>>
	 */
	public static function audit_post( $post_id, $definition ) {
		$post    = get_post( $post_id );
		$content = array();
		$review  = array();

		if ( ! $post instanceof WP_Post ) {
			return array( 'content' => array( 'post tidak ditemukan' ), 'review' => array() );
		}

		if ( '' === trim( wp_strip_all_tags( (string) $post->post_excerpt ) ) ) {
			$content[] = 'ringkasan';
		}
		if ( '' === trim( wp_strip_all_tags( (string) $post->post_content ) ) ) {
			$content[] = 'narasi';
		}
		if ( ! has_post_thumbnail( $post_id ) ) {
			$content[] = 'gambar utama';
		}

		foreach ( $definition['required_meta'] as $meta_key => $label ) {
			if ( '' === trim( (string) get_post_meta( $post_id, $meta_key, true ) ) ) {
				$content[] = $label;
			}
		}

		if ( '' === trim( (string) get_post_meta( $post_id, '_qaf_content_owner', true ) ) ) {
			$review[] = 'penanggung jawab data';
		}
		if ( '' === trim( (string) get_post_meta( $post_id, '_qaf_last_reviewed', true ) ) ) {
			$review[] = 'tanggal tinjau';
		}

		return array(
			'content' => array_values( array_unique( $content ) ),
			'review'  => array_values( array_unique( $review ) ),
		);
	}

	/**
	 * Summarize one post type for the dashboard.
	 *
	 * @param string              $post_type Post type.
	 * @param array<string,mixed> $definition Audit definition.
	 * @return array<string,mixed>
	 */
	public static function summarize_type( $post_type, $definition ) {
		$ids = get_posts(
			array(
				'post_type'              => $post_type,
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'suppress_filters'       => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			)
		);

		$rows = array();
		foreach ( $ids as $post_id ) {
			$issues = self::audit_post( (int) $post_id, $definition );
			if ( empty( $issues['content'] ) && empty( $issues['review'] ) ) {
				continue;
			}
			$rows[] = array(
				'id'      => (int) $post_id,
				'title'   => get_the_title( $post_id ),
				'content' => $issues['content'],
				'review'  => $issues['review'],
			);
		}

		$content_incomplete = count( array_filter( $rows, static function ( $row ) { return ! empty( $row['content'] ); } ) );
		$review_incomplete  = count( array_filter( $rows, static function ( $row ) { return ! empty( $row['review'] ); } ) );

		return array(
			'total'              => count( $ids ),
			'content_complete'   => count( $ids ) - $content_incomplete,
			'content_incomplete' => $content_incomplete,
			'review_incomplete'  => $review_incomplete,
			'rows'               => $rows,
		);
	}

	/**
	 * Render the read-only report.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Anda tidak memiliki izin untuk membuka audit ini.', 'queen-alfalah-core' ) );
		}

		$scope   = self::get_scope();
		$reports = array();
		foreach ( $scope as $post_type => $definition ) {
			$reports[ $post_type ] = self::summarize_type( $post_type, $definition );
		}

		$structure = self::find_structure_page();
		?>
		<div class="wrap qaf-october-audit">
			<h1><?php esc_html_e( 'Audit Kelengkapan Konten Oktober', 'queen-alfalah-core' ); ?></h1>
			<p><?php esc_html_e( 'Laporan baca-saja ini memprioritaskan Program Keahlian, Guru & Tendik, Sarana Prasarana, Ekstrakurikuler, serta Struktur Organisasi sesuai program kerja Oktober. Tidak ada konten yang diubah otomatis.', 'queen-alfalah-core' ); ?></p>
			<style>
				.qaf-audit-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin:20px 0}.qaf-audit-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px}.qaf-audit-card h2{margin-top:0}.qaf-audit-number{font-size:28px;font-weight:700;color:#0a5c47}.qaf-audit-section{margin:28px 0}.qaf-audit-section table td{vertical-align:top}.qaf-audit-ok{color:#087f5b;font-weight:600}.qaf-audit-warn{color:#8a4b08;font-weight:600}
			</style>
			<div class="qaf-audit-grid">
			<?php foreach ( $scope as $post_type => $definition ) : $report = $reports[ $post_type ]; ?>
				<section class="qaf-audit-card">
					<h2><?php echo esc_html( $definition['label'] ); ?></h2>
					<div class="qaf-audit-number"><?php echo esc_html( $report['content_complete'] . '/' . $report['total'] ); ?></div>
					<p><?php esc_html_e( 'item lengkap secara isi', 'queen-alfalah-core' ); ?></p>
					<p><?php echo esc_html( $report['content_incomplete'] ); ?> perlu dilengkapi · <?php echo esc_html( $report['review_incomplete'] ); ?> belum memiliki data peninjauan</p>
				</section>
			<?php endforeach; ?>
				<section class="qaf-audit-card">
					<h2><?php esc_html_e( 'Struktur Organisasi', 'queen-alfalah-core' ); ?></h2>
					<?php if ( $structure instanceof WP_Post ) : ?>
						<div class="qaf-audit-number">✓</div>
						<p class="qaf-audit-ok"><?php esc_html_e( 'Halaman tersedia', 'queen-alfalah-core' ); ?></p>
						<p>Terakhir diubah: <?php echo esc_html( mysql2date( 'j F Y', $structure->post_modified ) ); ?> · <a href="<?php echo esc_url( get_edit_post_link( $structure->ID ) ); ?>">Tinjau halaman</a></p>
					<?php else : ?>
						<div class="qaf-audit-number">!</div>
						<p class="qaf-audit-warn"><?php esc_html_e( 'Halaman belum ditemukan', 'queen-alfalah-core' ); ?></p>
					<?php endif; ?>
				</section>
			</div>

			<?php foreach ( $scope as $post_type => $definition ) : $report = $reports[ $post_type ]; ?>
				<section class="qaf-audit-section">
					<h2><?php echo esc_html( $definition['label'] ); ?></h2>
					<?php if ( empty( $report['rows'] ) ) : ?>
						<p class="qaf-audit-ok"><?php esc_html_e( 'Semua item telah memenuhi pemeriksaan isi dan tata kelola.', 'queen-alfalah-core' ); ?></p>
					<?php else : ?>
						<table class="widefat striped">
							<thead><tr><th>Item</th><th>Kelengkapan isi</th><th>Tata kelola</th><th>Tindakan</th></tr></thead>
							<tbody>
							<?php foreach ( $report['rows'] as $row ) : ?>
								<tr>
									<td><strong><?php echo esc_html( $row['title'] ); ?></strong></td>
									<td><?php echo empty( $row['content'] ) ? '<span class="qaf-audit-ok">Lengkap</span>' : esc_html( implode( ', ', $row['content'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
									<td><?php echo empty( $row['review'] ) ? '<span class="qaf-audit-ok">Tercatat</span>' : esc_html( implode( ', ', $row['review'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
									<td><a class="button button-small" href="<?php echo esc_url( get_edit_post_link( $row['id'] ) ); ?>">Sunting</a></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</section>
			<?php endforeach; ?>
			<p><em><?php esc_html_e( 'Prioritas tindak lanjut: lengkapi fakta dan visual dari sumber sekolah, isi penanggung jawab data, lalu catat tanggal tinjau. Jangan mengarang data yang belum diverifikasi.', 'queen-alfalah-core' ); ?></em></p>
		</div>
		<?php
	}

	/**
	 * Locate the organization page even when WordPress nests it below Profile.
	 *
	 * @return WP_Post|null
	 */
	private static function find_structure_page() {
		$page = get_page_by_path( 'struktur-organisasi', OBJECT, 'page' );
		if ( $page instanceof WP_Post ) {
			return $page;
		}

		$matches = get_posts(
			array(
				'post_type'              => 'page',
				'post_status'            => 'publish',
				'title'                  => 'Struktur Organisasi',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'suppress_filters'       => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		return ! empty( $matches ) && $matches[0] instanceof WP_Post ? $matches[0] : null;
	}
}
