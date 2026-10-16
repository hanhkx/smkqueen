<?php
/** Student work registration, metadata and curated source contract. */
define('ABSPATH',__DIR__);
$types=array();$taxonomies=array();
function register_post_type($name,$args){$GLOBALS['types'][$name]=$args;}
function register_taxonomy($name,$objects,$args){$GLOBALS['taxonomies'][$name]=array($objects,$args);}
function __($text){return $text;}
require dirname(__DIR__).'/queen-alfalah-core/includes/class-qaf-core-post-types.php';
require dirname(__DIR__).'/queen-alfalah-core/includes/class-qaf-core-meta.php';
require dirname(__DIR__).'/queen-alfalah-core/includes/class-qaf-core-student-works.php';
function verify_work($condition,$message){if(!$condition){fwrite(STDERR,"FAIL: $message\n");exit(1);}}
QAF_Core_Post_Types::register_post_types();QAF_Core_Post_Types::register_taxonomies();
$work=$types['qaf_work'];
verify_work($work['public']&&$work['show_in_rest']&&$work['has_archive']&&true===$work['show_in_menu'],'public archive and independent admin menu');
verify_work(array('qaf_work','qaf_works')===$work['capability_type']&&$work['map_meta_cap'],'isolated object-aware work capabilities');
verify_work(in_array('author',$work['supports'],true),'author-aware own content access');
verify_work('karya-siswa'===$work['rewrite']['slug'],'archive slug');
verify_work(array('qaf_work')===$taxonomies['qaf_work_type'][0],'taxonomy scoped to work');
verify_work('assign_qaf_work_terms'===$taxonomies['qaf_work_type'][1]['capabilities']['assign_terms'],'dedicated assign permission');
$fields=QAF_Core_Meta::get_fields()['qaf_work'];
verify_work('date'===$fields['_qaf_work_date']['type']&&'url'===$fields['_qaf_work_source_url']['type'],'validated metadata types');
$items=QAF_Core_Student_Works::items();verify_work(7===count($items),'seven reviewed gallery candidates');
foreach($items as $code=>$item){verify_work((bool)preg_match('/^[A-Za-z0-9_-]{11}$/D',$code),'canonical shortcode');verify_work(in_array($item[1],array('video-pendek','portofolio'),true)&&strlen($item[2])>50,'type and factual caption');}
$images=QAF_Core_Student_Works::image_items();verify_work(18===count($images),'eighteen source-owned work covers');
foreach($images as $image){verify_work(0===strpos($image['source'],'https://'),'https source');verify_work(!empty($image['file'])||0===strpos($image['remote'],'https://i.ytimg.com/vi/'),'bundled Instagram or official YouTube image');verify_work(strlen($image['alt'])>20&&strlen($image['caption'])>20,'descriptive image metadata');}
echo "Student works contracts passed.\n";
