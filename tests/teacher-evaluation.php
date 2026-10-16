<?php
/** Dependency-free contracts for teacher evaluation questions and analysis. */
define( 'ABSPATH', __DIR__ );
if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $value ) { return trim( (string) $value ); }
}
require dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-teacher-evaluation.php';

function verify_teacher_evaluation( $ok, $label ) {
	if ( ! $ok ) {
		fwrite( STDERR, "FAIL: {$label}\n" );
		exit( 1 );
	}
}

$questions = QAF_Core_Teacher_Evaluation::question_definitions();
verify_teacher_evaluation( array( 'satisfaction', 'understanding', 'relevance', 'engagement', 'feedback', 'fairness', 'classroom_climate', 'support', 'influence', 'discipline' ) === array_keys( $questions ), 'ten reflective aspects' );
foreach ( $questions as $question ) {
	verify_teacher_evaluation( array( 5, 4, 3, 2, 1 ) === array_keys( $question['answers'] ), 'five analyzable contextual choices' );
	$answer_text = implode( ' ', $question['answers'] );
	verify_teacher_evaluation( false === stripos( $answer_text, 'Sangat puas' ) && false === stripos( $answer_text, 'Tidak puas' ), 'answers must describe observable experiences, not generic satisfaction' );
}

$classes = QAF_Core_Teacher_Evaluation::class_options();
verify_teacher_evaluation( 32 === count( $classes ), 'all 32 exact class groups are available' );
verify_teacher_evaluation( in_array( 'X DKV 4', $classes, true ) && in_array( 'XII TJKT 6', $classes, true ), 'class options include parallel classes' );

$rows = array(
	array( 'satisfaction' => 5, 'understanding' => 4, 'relevance' => 5, 'engagement' => 4, 'feedback' => 3, 'fairness' => 5, 'classroom_climate' => 5, 'support' => 4, 'influence' => 5, 'discipline' => 3, 'class_group' => 'X DKV 1', 'strength_story' => 'Guru sabar, penjelasan jelas, dan memberi praktik yang sangat membantu.', 'improvement_story' => 'Kami membutuhkan umpan balik agar memahami kesalahan dan cara memperbaiki.' ),
	array( 'satisfaction' => 4, 'understanding' => 3, 'relevance' => 4, 'engagement' => 3, 'feedback' => 2, 'fairness' => 4, 'classroom_climate' => 4, 'support' => 3, 'influence' => 4, 'discipline' => 2, 'class_group' => 'X DKV 1', 'strength_story' => 'Contoh praktik menarik dan membuat kami lebih berani mencoba.', 'improvement_story' => 'Guru kadang terlambat dan kami perlu contoh tambahan.' ),
);
$analysis = QAF_Core_Teacher_Evaluation::analyze_responses( $rows );
verify_teacher_evaluation( 2 === $analysis['count'], 'response count' );
verify_teacher_evaluation( 4.5 === $analysis['averages']['satisfaction'], 'satisfaction average' );
verify_teacher_evaluation( 2.5 === $analysis['averages']['discipline'], 'discipline average' );
verify_teacher_evaluation( 'satisfaction' === $analysis['best_key'], 'stable strongest aspect' );
verify_teacher_evaluation( 'feedback' === $analysis['low_key'], 'stable lowest aspect' );
verify_teacher_evaluation( false !== strpos( $analysis['conclusion'], '2 respons' ), 'transparent conclusion' );
verify_teacher_evaluation( isset( $analysis['themes']['Penjelasan jelas'] ), 'positive theme extraction' );
verify_teacher_evaluation( isset( $analysis['themes']['Perlu lebih disiplin'] ), 'improvement theme extraction' );
verify_teacher_evaluation( isset( $analysis['themes']['Butuh umpan balik'] ), 'feedback theme extraction' );
verify_teacher_evaluation( isset( $analysis['class_breakdown']['X DKV 1'] ) && 2 === $analysis['class_breakdown']['X DKV 1'], 'class coverage analysis' );
verify_teacher_evaluation( 3 === count( $analysis['recommendations'] ), 'three transparent improvement recommendations' );

$legacy = QAF_Core_Teacher_Evaluation::analyze_responses(
	array( array( 'satisfaction' => 4, 'understanding' => 3, 'influence' => 4, 'discipline' => 3, 'story' => 'Data lama tetap dapat dianalisis.' ) )
);
verify_teacher_evaluation( 3.0 === $legacy['averages']['relevance'], 'legacy response falls back safely for new metrics' );

$empty = QAF_Core_Teacher_Evaluation::analyze_responses( array() );
verify_teacher_evaluation( 0 === $empty['count'] && 0.0 === $empty['overall'], 'empty analysis' );

$source = file_get_contents( dirname( __DIR__ ) . '/queen-alfalah-core/includes/class-qaf-core-teacher-evaluation.php' );
verify_teacher_evaluation( false !== strpos( $source, 'student_name varchar(191)' ), 'schema stores accountable student name' );
verify_teacher_evaluation( false !== strpos( $source, 'UNIQUE KEY student_teacher_period' ), 'schema blocks repeat identity per teacher and semester' );
verify_teacher_evaluation( false !== strpos( $source, 'Nama lengkap siswa' ), 'form requires student name' );
verify_teacher_evaluation( false !== strpos( $source, 'strength_story' ) && false !== strpos( $source, 'improvement_story' ), 'form and schema store structured reflections' );
verify_teacher_evaluation( false !== strpos( $source, 'tidak dikirim ke layanan AI eksternal' ), 'privacy disclosure explains local rule-based analysis' );

echo "Teacher evaluation contracts passed with accountable identity, 10 reflective aspects, narrative prompts, duplicate prevention, and local recommendations.\n";
