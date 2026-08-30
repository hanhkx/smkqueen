<?php
/**
 * Single post and custom school-content entry.
 *
 * @package Queen_AlFalah
 */

$queen_post_type   = get_post_type();
$queen_type_object = get_post_type_object( $queen_post_type );
$queen_type_label  = $queen_type_object ? $queen_type_object->labels->singular_name : __( 'Informasi Sekolah', 'queen-alfalah' );

/*
 * Field definitions stay in the presentation layer so the theme remains
 * useful when the companion plugin is deactivated after content was created.
 */
$queen_detail_fields = array(
	'qaf_program'     => array(
		array( 'program_code', __( 'Kode/Singkatan', 'queen-alfalah' ), 'text' ),
		array( 'program_head', __( 'Kepala Program', 'queen-alfalah' ), 'text' ),
		array( 'program_gender', __( 'Ketentuan Peserta', 'queen-alfalah' ), 'text' ),
		array( 'competencies', __( 'Kompetensi Utama', 'queen-alfalah' ), 'list' ),
		array( 'careers', __( 'Prospek Karier dan Studi', 'queen-alfalah' ), 'list' ),
	),
	'qaf_teacher'     => array(
		array( 'role', __( 'Jabatan/Peran', 'queen-alfalah' ), 'text' ),
		array( 'subject', __( 'Mata Pelajaran/Unit', 'queen-alfalah' ), 'text' ),
	),
	'qaf_notice'      => array(
		array( 'priority', __( 'Prioritas', 'queen-alfalah' ), 'priority' ),
		array( 'expiry', __( 'Berlaku Sampai', 'queen-alfalah' ), 'date' ),
		array( 'file_url', __( 'Lampiran Resmi', 'queen-alfalah' ), 'url', __( 'Buka lampiran', 'queen-alfalah' ) ),
	),
	'qaf_agenda'      => array(
		array( 'start_date', __( 'Mulai', 'queen-alfalah' ), 'datetime' ),
		array( 'end_date', __( 'Selesai', 'queen-alfalah' ), 'datetime' ),
		array( 'location', __( 'Lokasi', 'queen-alfalah' ), 'text' ),
	),
	'qaf_achievement' => array(
		array( 'award', __( 'Juara/Penghargaan', 'queen-alfalah' ), 'text' ),
		array( 'recipient', __( 'Penerima/Delegasi', 'queen-alfalah' ), 'multiline' ),
		array( 'field', __( 'Bidang', 'queen-alfalah' ), 'text' ),
		array( 'organizer', __( 'Penyelenggara', 'queen-alfalah' ), 'text' ),
		array( 'level', __( 'Ruang Lingkup', 'queen-alfalah' ), 'text' ),
		array( 'achievement_date', __( 'Tanggal Prestasi', 'queen-alfalah' ), 'date' ),
		array( 'source_url', __( 'Sumber Unggahan', 'queen-alfalah' ), 'url', __( 'Lihat unggahan sumber', 'queen-alfalah' ) ),
	),
	'qaf_extra'       => array(
		array( 'schedule', __( 'Jadwal Latihan', 'queen-alfalah' ), 'text' ),
		array( 'coach', __( 'Pembina/Pelatih', 'queen-alfalah' ), 'text' ),
		array( 'extra_location', __( 'Lokasi Kegiatan', 'queen-alfalah' ), 'text' ),
		array( 'benefits', __( 'Yang Dipelajari dan Didapat', 'queen-alfalah' ), 'list' ),
		array( 'career_relevance', __( 'Kegunaan untuk Studi dan Dunia Kerja', 'queen-alfalah' ), 'list' ),
		array( 'join_info', __( 'Informasi Bergabung', 'queen-alfalah' ), 'multiline' ),
	),
	'qaf_service'     => array(
		array( 'external_url', __( 'Layanan Digital', 'queen-alfalah' ), 'url', __( 'Buka layanan', 'queen-alfalah' ) ),
	),
	'qaf_gallery'     => array(
		array( 'album_date', __( 'Tanggal Dokumentasi', 'queen-alfalah' ), 'date' ),
		array( 'gallery_media_type', __( 'Jenis Media', 'queen-alfalah' ), 'gallery_media_type' ),
		array( 'video_url', __( 'Tautan Media', 'queen-alfalah' ), 'url', __( 'Buka sumber media', 'queen-alfalah' ) ),
	),
	'qaf_partner'     => array(
		array( 'partner_sector', __( 'Sektor Kerja Sama', 'queen-alfalah' ), 'text' ),
		array( 'partner_legal_name', __( 'Nama Legal/Bentuk Lembaga', 'queen-alfalah' ), 'text' ),
		array( 'partner_location', __( 'Lokasi', 'queen-alfalah' ), 'text' ),
		array( 'partner_programs', __( 'Program Keahlian Terkait', 'queen-alfalah' ), 'list' ),
		array( 'partner_expertise', __( 'Keahlian/Layanan Mitra', 'queen-alfalah' ), 'list' ),
		array( 'partner_cooperation', __( 'Penyaluran Kerja Sama ke Sekolah', 'queen-alfalah' ), 'multiline' ),
		array( 'partner_verification', __( 'Status Informasi', 'queen-alfalah' ), 'partner_verification' ),
		array( 'partner_url', __( 'Website Mitra', 'queen-alfalah' ), 'url', __( 'Kunjungi website', 'queen-alfalah' ) ),
		array( 'partner_source_url', __( 'Sumber Profil', 'queen-alfalah' ), 'url', __( 'Lihat sumber profil', 'queen-alfalah' ) ),
	),
	'qaf_vacancy'     => array(
		array( 'company', __( 'Perusahaan/Instansi', 'queen-alfalah' ), 'text' ),
		array( 'deadline', __( 'Batas Lamaran', 'queen-alfalah' ), 'date' ),
		array( 'apply_url', __( 'Kanal Lamaran Resmi', 'queen-alfalah' ), 'url', __( 'Buka informasi lamaran', 'queen-alfalah' ) ),
	),
	'qaf_alumni'      => array(
		array( 'graduation_year', __( 'Tahun Lulus', 'queen-alfalah' ), 'number' ),
		array( 'current_role', __( 'Aktivitas/Peran Saat Ini', 'queen-alfalah' ), 'text' ),
	),
	'qaf_facility'    => array(
		array( 'facility_location', __( 'Lokasi Sarana', 'queen-alfalah' ), 'text' ),
		array( 'capacity', __( 'Kapasitas/Jumlah', 'queen-alfalah' ), 'number' ),
		array( 'facility_status', __( 'Status Sarana', 'queen-alfalah' ), 'facility_status' ),
		array( 'facility_function', __( 'Fungsi Utama', 'queen-alfalah' ), 'multiline' ),
		array( 'facility_features', __( 'Fasilitas/Perlengkapan', 'queen-alfalah' ), 'list' ),
		array( 'facility_access', __( 'Akses dan Aturan Penggunaan', 'queen-alfalah' ), 'multiline' ),
		array( 'facility_manager', __( 'Penanggung Jawab', 'queen-alfalah' ), 'text' ),
		array( 'facility_last_check', __( 'Pemeriksaan Terakhir', 'queen-alfalah' ), 'date' ),
	),
);

$queen_details = array();
$queen_details_title = 'qaf_achievement' === $queen_post_type
	? __( 'Detail Prestasi', 'queen-alfalah' )
	: __( 'Informasi Utama', 'queen-alfalah' );

if ( 'qaf_gallery' === $queen_post_type ) {
	$queen_details[] = array(
		'gallery_source',
		__( 'Sumber Media', 'queen-alfalah' ),
		'text',
		'value' => queen_alfalah_gallery_source_label( get_the_ID() ),
	);
}

if ( isset( $queen_detail_fields[ $queen_post_type ] ) ) {
	foreach ( $queen_detail_fields[ $queen_post_type ] as $queen_field ) {
		$queen_value = queen_alfalah_meta( get_the_ID(), $queen_field[0] );
		if ( '' !== $queen_value && null !== $queen_value && false !== $queen_value && 0 !== $queen_value && '0' !== $queen_value ) {
			$queen_field['value'] = $queen_value;
			$queen_details[]      = $queen_field;
		}
	}
}

$queen_gallery_media = 'qaf_gallery' === $queen_post_type ? queen_alfalah_gallery_media( get_the_ID() ) : '';
$queen_thumbnail_id = has_post_thumbnail() ? (int) get_post_thumbnail_id() : 0;
$queen_thumbnail_caption = $queen_thumbnail_id ? (string) wp_get_attachment_caption( $queen_thumbnail_id ) : '';
$queen_instagram_source = 'post' === $queen_post_type ? (string) get_post_meta( get_the_ID(), '_qaf_instagram_source_url', true ) : '';
$queen_image_credit = 'post' === $queen_post_type ? (string) get_post_meta( get_the_ID(), '_qaf_image_credit', true ) : '';
$queen_fallback_visual = $queen_thumbnail_id ? array() : queen_alfalah_fallback_visual( get_the_ID() );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-entry' ); ?>>
	<header class="entry-header">
		<p class="eyebrow"><?php echo esc_html( $queen_type_label ); ?></p>
		<h1 class="entry-title"><?php echo esc_html( get_the_title() ); ?></h1>
		<?php queen_alfalah_post_meta(); ?>
	</header>

	<figure class="post-thumbnail">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'full', array( 'decoding' => 'async' ) ); ?>
		<?php elseif ( $queen_fallback_visual ) : ?>
			<img src="<?php echo esc_url( $queen_fallback_visual['url'] ); ?>" alt="" width="<?php echo esc_attr( $queen_fallback_visual['width'] ); ?>" height="<?php echo esc_attr( $queen_fallback_visual['height'] ); ?>" decoding="async">
		<?php endif; ?>
		<?php if ( $queen_thumbnail_id && ( $queen_thumbnail_caption || $queen_image_credit || $queen_instagram_source ) ) : ?>
			<figcaption class="post-thumbnail__caption">
				<?php if ( $queen_thumbnail_caption ) : ?>
					<span><?php echo esc_html( $queen_thumbnail_caption ); ?></span>
				<?php endif; ?>
				<?php if ( $queen_instagram_source ) : ?>
					<a href="<?php echo esc_url( $queen_instagram_source ); ?>" rel="external noopener">
						<?php echo esc_html( $queen_image_credit ? $queen_image_credit : __( 'Sumber: Instagram sekolah', 'queen-alfalah' ) ); ?>
					</a>
				<?php elseif ( $queen_image_credit ) : ?>
					<small><?php echo esc_html( $queen_image_credit ); ?></small>
				<?php endif; ?>
			</figcaption>
		<?php elseif ( $queen_fallback_visual ) : ?>
			<figcaption class="post-thumbnail__caption post-thumbnail__caption--illustration">
				<strong><?php echo esc_html( $queen_fallback_visual['label'] ); ?>.</strong>
				<span><?php echo esc_html( $queen_fallback_visual['caption'] ); ?></span>
			</figcaption>
		<?php endif; ?>
	</figure>

	<?php if ( $queen_details ) : ?>
		<section class="entry-details card widget flow" aria-labelledby="entry-details-title">
			<h2 id="entry-details-title"><?php echo esc_html( $queen_details_title ); ?></h2>
			<dl>
				<?php foreach ( $queen_details as $queen_detail ) : ?>
					<?php
					$queen_key        = $queen_detail[0];
					$queen_label      = $queen_detail[1];
					$queen_format     = $queen_detail[2];
					$queen_value      = $queen_detail['value'];
					$queen_link_label = isset( $queen_detail[3] ) ? $queen_detail[3] : $queen_label;
					?>
					<div class="entry-details__item">
						<dt><?php echo esc_html( $queen_label ); ?></dt>
						<dd>
							<?php if ( 'url' === $queen_format ) : ?>
								<a href="<?php echo esc_url( $queen_value ); ?>" rel="external"><?php echo esc_html( $queen_link_label ); ?><?php echo queen_alfalah_icon( 'external' ); ?></a>
							<?php elseif ( 'date' === $queen_format || 'datetime' === $queen_format ) : ?>
								<?php
								$queen_input_format = 'datetime' === $queen_format ? 'Y-m-d\TH:i' : 'Y-m-d';
								$queen_date         = DateTimeImmutable::createFromFormat( $queen_input_format, $queen_value, wp_timezone() );
								if ( $queen_date ) {
									$queen_output_format = 'datetime' === $queen_format ? get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) : get_option( 'date_format' );
									echo '<time datetime="' . esc_attr( $queen_value ) . '">' . esc_html( wp_date( $queen_output_format, $queen_date->getTimestamp(), wp_timezone() ) ) . '</time>';
								} else {
									echo esc_html( $queen_value );
								}
								?>
							<?php elseif ( 'list' === $queen_format ) : ?>
								<ul>
									<?php foreach ( preg_split( '/\r\n|\r|\n/', (string) $queen_value ) as $queen_line ) : ?>
										<?php if ( trim( $queen_line ) ) : ?>
											<li><?php echo esc_html( trim( $queen_line ) ); ?></li>
										<?php endif; ?>
									<?php endforeach; ?>
								</ul>
							<?php elseif ( 'multiline' === $queen_format ) : ?>
								<?php echo nl2br( esc_html( $queen_value ) ); ?>
							<?php elseif ( 'priority' === $queen_format ) : ?>
								<?php
								$queen_priority_labels = array( 'normal' => __( 'Normal', 'queen-alfalah' ), 'penting' => __( 'Penting', 'queen-alfalah' ), 'mendesak' => __( 'Mendesak', 'queen-alfalah' ) );
								echo esc_html( isset( $queen_priority_labels[ $queen_value ] ) ? $queen_priority_labels[ $queen_value ] : $queen_value );
								?>
							<?php elseif ( 'facility_status' === $queen_format ) : ?>
								<?php
								$queen_status_labels = array( 'baik' => __( 'Baik/Operasional', 'queen-alfalah' ), 'perlu-perawatan' => __( 'Perlu Perawatan', 'queen-alfalah' ), 'tidak-operasional' => __( 'Tidak Operasional', 'queen-alfalah' ) );
								echo esc_html( isset( $queen_status_labels[ $queen_value ] ) ? $queen_status_labels[ $queen_value ] : $queen_value );
								?>
							<?php elseif ( 'partner_verification' === $queen_format ) : ?>
								<?php
								$queen_verification_labels = array(
									'verified-profile'   => __( 'Profil publik terverifikasi; keterangan kerja sama mengikuti data sekolah', 'queen-alfalah' ),
									'school-data'        => __( 'Keterangan kerja sama berdasarkan data sekolah', 'queen-alfalah' ),
									'needs-confirmation' => __( 'Identitas/profil publik masih perlu konfirmasi sekolah', 'queen-alfalah' ),
								);
								echo esc_html( isset( $queen_verification_labels[ $queen_value ] ) ? $queen_verification_labels[ $queen_value ] : $queen_value );
								?>
							<?php elseif ( 'gallery_media_type' === $queen_format ) : ?>
								<?php
								$queen_gallery_type_labels = array(
									'photo' => __( 'Foto', 'queen-alfalah' ),
									'video' => __( 'Video', 'queen-alfalah' ),
									'mixed' => __( 'Foto dan Video', 'queen-alfalah' ),
								);
								echo esc_html( isset( $queen_gallery_type_labels[ $queen_value ] ) ? $queen_gallery_type_labels[ $queen_value ] : $queen_value );
								?>
							<?php elseif ( 'number' === $queen_format ) : ?>
								<?php echo esc_html( number_format_i18n( absint( $queen_value ) ) ); ?>
							<?php else : ?>
								<?php echo esc_html( $queen_value ); ?>
							<?php endif; ?>
						</dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</section>
	<?php endif; ?>

	<?php
	if ( $queen_gallery_media ) {
		echo $queen_gallery_media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from validated provider URLs and escaped attributes.
	}
	?>

	<div class="entry-content">
		<?php
		the_content();
		wp_link_pages(
			array(
				'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Halaman artikel', 'queen-alfalah' ) . '"><span>' . esc_html__( 'Halaman:', 'queen-alfalah' ) . '</span>',
				'after'  => '</nav>',
			)
		);
		?>
	</div>

	<footer class="entry-footer">
		<?php if ( 'post' === $queen_post_type ) : ?>
			<?php if ( get_the_category_list() ) : ?><span><?php esc_html_e( 'Kategori:', 'queen-alfalah' ); ?> <?php echo wp_kses_post( get_the_category_list( ', ' ) ); ?></span><?php endif; ?>
			<?php if ( get_the_tag_list() ) : ?><span><?php esc_html_e( 'Tag:', 'queen-alfalah' ); ?> <?php echo wp_kses_post( get_the_tag_list( '', ', ' ) ); ?></span><?php endif; ?>
		<?php else : ?>
			<?php foreach ( get_object_taxonomies( $queen_post_type, 'objects' ) as $queen_taxonomy ) : ?>
				<?php $queen_term_list = get_the_term_list( get_the_ID(), $queen_taxonomy->name, '', ', ' ); ?>
				<?php if ( $queen_term_list && ! is_wp_error( $queen_term_list ) ) : ?>
					<span><?php echo esc_html( $queen_taxonomy->labels->singular_name ); ?>: <?php echo wp_kses_post( $queen_term_list ); ?></span>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php
		edit_post_link(
			esc_html__( 'Sunting konten', 'queen-alfalah' ),
			'<span class="edit-link">',
			'</span>'
		);
		?>
	</footer>

	<?php queen_alfalah_share_links(); ?>
</article>
