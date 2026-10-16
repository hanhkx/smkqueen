<?php
/** Dependency-free negative permission checks. */
define( 'ABSPATH', __DIR__ );
class WP_User { public $ID; public $roles; public function __construct( $id, $roles ) { $this->ID=$id; $this->roles=$roles; } }
$users = array( 1 => new WP_User(1,array('administrator')), 2 => new WP_User(2,array('qaf_journalism')) );
$posts = array(
 10 => (object) array('post_type'=>'post','post_author'=>2,'post_status'=>'publish'),
 11 => (object) array('post_type'=>'qaf_work','post_author'=>2,'post_status'=>'draft'),
 12 => (object) array('post_type'=>'post','post_author'=>1,'post_status'=>'publish'),
 13 => (object) array('post_type'=>'qaf_teacher','post_author'=>2,'post_status'=>'draft'),
 14 => (object) array('post_type'=>'attachment','post_author'=>1,'post_status'=>'inherit'),
);
function get_userdata($id){return $GLOBALS['users'][$id]??null;}
function get_post($id){return $GLOBALS['posts'][$id]??null;}
function get_post_status($post){return $post->post_status;}
function get_post_status_object($status){return (object)array('public'=>'publish'===$status);}
require dirname(__DIR__).'/queen-alfalah-core/includes/class-qaf-core-journalism.php';
function verify($ok,$label){if(!$ok){fwrite(STDERR,"FAIL: $label\n");exit(1);}}
$class='QAF_Core_Journalism';
verify(array('edit_published_posts')===$class::map_capabilities(array('edit_published_posts'),'edit_post',2,array(10)),'own published news');
verify(array('edit_qaf_works')===$class::map_capabilities(array('edit_qaf_works'),'edit_post',2,array(11)),'own draft work');
foreach(array(12,13,14,999) as $id){verify(array('do_not_allow')===$class::map_capabilities(array('edit_posts'),'edit_post',2,array($id)),'deny other or forbidden object '.$id);}
verify(array('do_not_allow')===$class::map_capabilities(array('delete_posts'),'delete_post',2,array(10)),'no deletion');
verify(array('do_not_allow')===$class::map_capabilities(array('manage_options'),'manage_options',2,array()),'no administration');
verify(array('do_not_allow')===$class::map_capabilities(array('qaf_school_edit_posts'),'qaf_school_edit_posts',2,array()),'no school CPT create');
verify(array('edit_posts')===$class::map_capabilities(array('qaf_school_edit_posts'),'qaf_school_edit_posts',1,array()),'existing admin bridge');
$args=$class::isolate_school_capabilities(array('capability_type'=>'post'),'qaf_teacher');
verify('qaf_school_edit_posts'===$args['capabilities']['create_posts'],'REST create isolated');
verify(array('capability_type'=>'post')===$class::isolate_school_capabilities(array('capability_type'=>'post'),'post'),'news unchanged');
$all=$class::limit_capabilities(array('edit_posts'=>true,'manage_options'=>true,'edit_others_posts'=>true),array(),array(),$users[2]);
verify($all['edit_posts']&&!$all['manage_options']&&!$all['edit_others_posts'],'no extra role leakage');
verify(2===count($class::account_definitions()),'two named supervisors');
echo "Journalism permission checks passed.\n";
function current_user_can($cap){return !empty($GLOBALS['provision_allowed']);}
function get_role($name){return new class {public $capabilities=array();public function add_cap($cap,$grant=true){}public function remove_cap($cap){}};}
function sanitize_email($value){return $value;}
function is_email($value){return false!==filter_var($value,FILTER_VALIDATE_EMAIL);}
function username_exists($name){return isset($GLOBALS['created_accounts'][$name])?$GLOBALS['created_accounts'][$name]['ID']:false;}
function email_exists($email){return false;}
function get_users($args){return array();}
function remove_accents($text){return $text;}
function wp_generate_password($length,$special=true,$extra=false){return str_pad('unique-'.(++$GLOBALS['password_counter']),$length,'x');}
function wp_insert_user($data){$data['ID']=100+count($GLOBALS['created_accounts']);$GLOBALS['created_accounts'][$data['user_login']]=$data;return $data['ID'];}
function is_wp_error($value){return false;}
function update_user_meta($id,$key,$value){}
function wp_new_user_notification($id,$unused,$who){$GLOBALS['notifications']++;}
$GLOBALS['provision_allowed']=true;$GLOBALS['created_accounts']=array();$GLOBALS['notifications']=0;$GLOBALS['password_counter']=0;
$result=$class::provision_accounts(array());verify(0===count($GLOBALS['created_accounts']),'blank emails remain opt-in');
$result=$class::provision_accounts(array(),true);
verify(2===count($GLOBALS['created_accounts'])&&0===$GLOBALS['notifications'],'two no-email users and no mail');
verify($result[0]['initial_password']!==$result[1]['initial_password'],'distinct passwords');
foreach($GLOBALS['created_accounts'] as $created){verify(''===$created['user_email']&&'qaf_journalism'===$created['role']&&32===strlen($created['user_pass']),'empty email and restricted role');}
$repeat=$class::provision_accounts(array(),true);verify(2===count($GLOBALS['created_accounts'])&&''===$repeat[0]['initial_password']&&''===$repeat[1]['initial_password'],'repeat preserves accounts and secrets');
$GLOBALS['provision_allowed']=false;verify('Akses ditolak.'===$class::provision_accounts(array(),true)[0]['message'],'unauthorized creation denied');
echo "No-email provisioning checks passed.\n";
