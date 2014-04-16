<?php
if(!defined('IN_JISHIGOU'))
{
exit('invalid request');
}
class MasterObject
{
var $Config=array();
var $Inputs = array();
var $DatabaseHandler;
var $MemberHandler;
var $ip = '';
var $timestamp = '';
var $failedlogins = array();
var $Module = '';
var $Code = '';
var $__API__ = array();
var $app_key = '';
var $app_secret = '';
var $app = array();
var $user = array();
var $TopicLogic;
var $__inputs__ = array();
function MasterObject(&$config)
{
	if(!$config['api_enable']) {
	exit('api_enable is invalid');
	}else {
	require ROOT_PATH .'setting/api.php';
	}
	$this->Config=$config;
	require_once ROOT_PATH .'include/function/api.func.php';
	$this->timestamp = time();
	$this->init_inputs();
	$this->init_db();
	if('oauth2'!= $this->Module) {
	$this->init_app();
	}
	include_once ROOT_PATH .'include/lib/member.han.php';
	$this->MemberHandler = new MemberHandler();
	$mods = array('test'=>1,'public'=>1,'oauth2'=>1);
	if($this->Module &&!isset($mods[$this->Module])) {
	$this->init_user();
	}else {
	$uid = 0;$pas = '';
	if('oauth2'== $this->Module &&($cookie_auth = jsg_getcookie('auth'))) {
	list($pas,$uid) = explode("\t",authcode($cookie_auth,'DECODE'));
	}
	$this->MemberHandler->FetchMember($uid,$pas);
	}
	Obj::register("MemberHandler",$this->MemberHandler);
	$this->TopicLogic = Load::logic('topic',1);
}
function init_inputs() {
$inputs = array();
if ($_GET) {
foreach ($_GET as $_k=>$_v) {
$inputs[$_k] = $_v;
}
}
if ($_POST) {
foreach ($_POST as $_k=>$_v) {
$inputs[$_k] = $_v;
}
}
if(!$inputs) {
api_error('inputs is empty',10);
}
if('oauth2'== $inputs['mod']) {
$inputs['__API__']['output'] = 'json';
}
$this->__inputs__ = $inputs;
$charsets = array('gbk'=>1,'gb2312'=>1,'utf-8'=>1,);
$charset = trim(strtolower($inputs['__API__']['charset']));
$charset = (($charset &&isset($charsets[$charset])) ?$charset : 'utf-8');
define('API_INPUT_CHARSET',$charset);
$inputs = array_iconv($charset,$this->Config['charset'],$inputs);
$outputs = array('xml'=>1,'json'=>1,'serialize_base64'=>1,);
$output = trim(strtolower($inputs['__API__']['output']));
$output = (($output &&isset($outputs[$output])) ?$output : 'xml');
define('API_OUTPUT',$output);
$auth_types = array('jauth1'=>1,'jauth2'=>1,'oauth2'=>1,);
$auth_type = trim(strtolower($inputs['__API__']['auth_type']));
$auth_type = (($auth_type &&isset($auth_types[$auth_type])) ?$auth_type : 'jauth1');
define('API_AUTH_TYPE',$auth_type);
$this->Module = $inputs['mod'];
$this->Code = $inputs['code'];
$this->__API__ = $inputs['__API__'];
$this->Inputs = $inputs;
unset($inputs);
}
function init_db() {
include_once ROOT_PATH .'include/db/database.db.php';
include_once ROOT_PATH .'include/db/mysql.db.php';
$this->DatabaseHandler = new MySqlHandler($this->Config['db_host'],$this->Config['db_port']);
$this->DatabaseHandler->Charset($this->Config['charset']);
$this->DatabaseHandler->doConnect($this->Config['db_user'],$this->Config['db_pass'],$this->Config['db_name'],$this->Config['db_persist']);
if(!$this->DatabaseHandler) {
api_error('db is invalid',11);
}
Obj::register('DatabaseHandler',$this->DatabaseHandler);
}
function init_app() {
$this->ip = client_ip();
$this->failedlogins = $this->DatabaseHandler->FetchFirst("select * from ".TABLE_PREFIX."failedlogins where `ip`='{$this->ip}' ");
if($this->failedlogins) {
if(($this->failedlogins['lastupdate'] +1800) >$this->timestamp) {
if($this->failedlogins['count'] >30) {
api_error('ip is invalid',12);
}
}else {
$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."failedlogins where `ip`='{$this->ip}'");
}
}
$error = '';
$app = array();
$this->app_key = ($this->__API__['app_key'] ?$this->__API__['app_key'] : get_param('client_id'));
if($this->app_key) {
$app = $this->DatabaseHandler->FetchFirst("select * from ".TABLE_PREFIX."app where `app_key`='{$this->app_key}'");
}
if(!$app) {
$error = 'app_key is invalid';
}else {
if('jauth2'== API_AUTH_TYPE) {
$auth_sign = $this->__API__['auth_sign'];
$sign = $this->_sign($this->__inputs__,$app['app_secret'],'auth_sign');
if($sign != $auth_sign) {
$error = 'auth_sign is invalid';
}
}elseif('oauth2'== API_AUTH_TYPE) {
$access_token = ($this->__API__['access_token'] ?$this->__API__['access_token'] : get_param('access_token'));
if($access_token) {
$token_info = DB::fetch_first("SELECT * FROM ".DB::table('api_oauth2_token')." WHERE `client_id`='{$this->app_key}' AND `access_token`='$access_token'");
if($token_info) {
if($token_info['expires'] &&$token_info['expires'] <TIMESTAMP) {
$error = 'access_token is expires';
}else {
if($token_info['uid'] >0) {
$this->user = DB::fetch_first("SELECT * FROM ".DB::table('members')." WHERE `uid`='{$token_info['uid']}'");
}
}
}else {
$error = 'access_token is invalid';
}
}else {
$error = 'access_token is empty';
}
}else {
$this->app_secret = trim($this->__API__['app_secret']);
if(!$this->app_secret ||
($app['app_secret']!=$this->app_secret)) {
$error = 'app_secret is invalid';
}
}
}
unset($this->__inputs__);
if($error) {
if($this->failedlogins) {
$this->DatabaseHandler->Query("update ".TABLE_PREFIX."failedlogins set `count`=`count`+1,`lastupdate`='".$this->timestamp."' where `ip`='{$this->ip}'");
}else {
$this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."failedlogins (`ip`,`count`,`lastupdate`) values ('{$this->ip}','1','".$this->timestamp."')");
}
api_error((is_string($error) ?$error : 'app_key or app_secret is invalid'),13);
}
$this->_update_app_request($app);
if($app['status'] <1) {
api_error('app status is invalid',14);
}
if($this->Config['api']['request_times_day_limit'] >0 &&$app['request_times_day'] >$this->Config['api']['request_times_day_limit']) {
api_error('api request_times_day is invalid',16);
}
;
$this->app = $app;
}
function init_user() {
if(!$this->user) {
$username = trim($this->__API__['username']);
$password = trim($this->__API__['password']);
if(!$username ||!$password ||
!($user = $this->DatabaseHandler->FetchFirst("select * from ".TABLE_PREFIX."members where `nickname`='{$username}'")) ||
((md5($user['nickname'] .$user['password'])!=$password) &&
(md5(array_iconv($this->Config['charset'],API_INPUT_CHARSET,$user['nickname']) .$user['password'])!=$password))) {
if($this->failedlogins) {
$this->DatabaseHandler->Query("update ".TABLE_PREFIX."failedlogins set `count`=`count`+1,`lastupdate`='".$this->timestamp."' where `ip`='{$this->ip}'");
}else {
$this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."failedlogins (`ip`,`count`,`lastupdate`) values ('{$this->ip}','1','".$this->timestamp."')");
}
api_error('username or password is invalid',15);
}
$this->user = $user;
}
$this->MemberHandler->FetchMember($this->user['uid'],$this->user['password']);
}
function _page($total) {
$return  = array();
$count = max(0,min(200,(int) ($this->Inputs['count'] ?$this->Inputs['count'] : $this->Inputs['limit'])));
if(!$count) {
$count = 20;
}
$page_count = max(1,ceil($total / $count));
if($this->Config['total_page_default'] >1 &&$page_count >$this->Config['total_page_default']) {
$page_count = $this->Config['total_page_default'];
}
$page = max(1,min($page_count,(int) $this->Inputs['page']));
$page_next = min($page +1,$page_count);
$page_previous = max(1,$page -1);
$offset = max(0,(int) (($page -1) * $count));
$return = array(
'total'=>$total,
'count'=>$count,
'page_count'=>$page_count,
'page'=>$page,
'page_next'=>$page_next,
'page_previous'=>$page_previous,
'offset'=>$offset,
'limit'=>$count,
);
return $return;
}
function _topic($sql_where='') {
$topics = $this->TopicLogic->Get($sql_where);
if($topics) {
if(is_numeric($sql_where)) {
$v = $topics;
$v = $this->_process_topic($v);
$topics = $v;
}else {
foreach($topics as $k=>$v) {
if(is_array($v)) {
$v = $this->_process_topic($v);
$topics[$k] = $v;
}
}
$topics = array_values($topics);
}
}
return $topics;
}
function _process_topic($v) {
if($v['totid']) {
$row = $this->TopicLogic->Get($v['totid'],'*','Make','','tid',1);
if($row) {
if($row['image_list']) {
$row['image_list'] = array_values($row['image_list']);
}
$v['to_topics'] = $row;
}
$row = $this->TopicLogic->Get($v['roottid'],'*','Make','','tid',1);
if($row) {
if($row['image_list']) {
$row['image_list'] = array_values($row['image_list']);
}
$v['root_topics'] = $row;
}
}
if($v['image_list']) {
$v['image_list'] = array_values($v['image_list']);
}
if($unsets) {
foreach($unsets as $s) {
if(isset($v[$s])) {
unset($v[$s]);
}
if(isset($v['to_topics'][$s])) {
unset($v['to_topics'][$s]);
}
if(isset($v['root_topics'][$s])) {
unset($v['root_topics'][$s]);
}
}
}
return $v;
}
function _topic_list($type='',$sql_wheres=array()) {
$sql_wheres['type'] = "`type` IN('first','forward','both')";
$id_max = max(0,(int) $this->Inputs['id_max']);
if($id_max) {
$sql_wheres[] = "`tid`<='$id_max'";
}
$id_min = max(0,(int) $this->Inputs['id_min']);
if($id_min) {
$sql_wheres[] = "`tid`>'$id_min'";
}
$order = '`dateline` desc';
$type = ($type ?$type : 'new');
$dateline = 0;
if('hot_forward'== $type) {
$dateline = 1;
$sql_wheres['type'] = "`type`='first'";
$order = '`forwards` desc, `dateline` desc';
$sql_wheres[] = '`forwards`>0';
}elseif ('hot_reply'== $type) {
$dateline = 1;
$sql_wheres['type'] = "`type`='first'";
$order = '`replys` desc, `dateline` desc';
$sql_wheres[] = '`replys`>0';
}
if($dateline) {
$dateline = max(0,(int) $this->Inputs['dateline']);
$dateline = ($dateline &&in_array($dateline,array(1,7,14,30))) ?$dateline : 7;
$sql_wheres[] = "`dateline`>='".(TIMESTAMP -$dateline * 86400)."'";
}
$item = $this->Inputs['item'];
$item_id = max(0,(int) $this->Inputs['item_id']);
if($item &&in_array($item,array('qun')) &&$item_id >0) {
$sql_wheres['type'] = "`type`!='reply'";
Load::functions('app');
$tids = app_itemid2tid($item,$item_id);
$sql_wheres[] = "`tid` in (".jimplode($tids).")";
}
$where = ($sql_wheres ?" where ".implode(" and ",$sql_wheres) : "");
$rets = array();
$total = DB::result_first("select count(*) as `count` from ".TABLE_PREFIX."topic $where ");
if($total) {
$rets = $this->_page($total);
$rets['topics'] = $this->_topic(" $where order by $order limit {$rets[offset]}, {$rets[count]} ");
}
api_output($rets);
}
function _user($uids=array())
{
$is_numeric = false;
if(is_numeric($uids)) {
$is_numeric = true;
}else {
$_tmps = array();
foreach($uids as $uid) {
$uid = max(0 ,(int) $uid);
if($uid) {
$_tmps[$uid] = $uid;
}
}
$uids = $_tmps;
}
if(!$uids) {
return array();
}
$uids = (array) $uids;
$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."members M left join ".TABLE_PREFIX."memberfields MF on MF.uid=M.uid where M.`uid` in ('".implode("','",$uids)."')");
$datas = array();
$fields = array('uid','username','nickname','email','gender','face','province','city','ucuid',
'topic_count','fans_count','follow_count','topic_favorite_count',
'validate','aboutme','signature','level','vip_pic','vip_info',);
while(false != ($user = $query->GetRow())) {
$face = face_get($user,'original');
if(false===strpos($face,':/'.'/')) {
$face = $this->Config['site_url'] .'/'.$face;
}
$user['face'] = $face;
$row = array();
if($fields) {
foreach($fields as $field) {
if(isset($user[$field])) {
$row[$field] = $user[$field];
}
}
}else {
$row = $user;
}
if($is_numeric) {
$datas = $row;
}else {
$datas[] = $row;
}
}
$datas = Load::model('buddy')->follow_html($datas,'uid','follow_html',$is_numeric);
return $datas;
}
function _init_user($uid=null)
{
$uid = max(0,(int) (isset($uid) ?$uid : $this->user['uid']));
if(!$uid)
{
api_error('uid is empty',100);
}
$user = $this->_user($uid);
if(!$user)
{
api_error('uid is invalid',101);
}
return $user;
}
function _update_app_request($app = array())
{
$updates = array();
$updates['request_times'] = "`request_times`=`request_times`+1";
$updates['last_request_time'] = "`last_request_time`='{$this->timestamp}'";
$updates['request_times_day'] = "`request_times_day`=`request_times_day`+1";
$updates['request_times_week'] = "`request_times_week`=`request_times_week`+1";
$updates['request_times_month'] = "`request_times_month`=`request_times_month`+1";
$updates['request_times_year'] = "`request_times_year`=`request_times_year`+1";
if(my_date_format($this->timestamp,'Ymd')!=my_date_format($app['last_request_time'],'Ymd'))
{
$updates['request_times_day'] = "`request_times_day`=1";
$updates['request_times_last_day'] = "`request_times_last_day`='{$app['request_times_day']}'";
if(my_date_format($this->timestamp,'YW')!=my_date_format($app['last_request_time'],'YW'))
{
$updates['request_times_week'] = "`request_times_week`=1";
$updates['request_times_last_week'] = "`request_times_last_week`='{$app['request_times_week']}'";
}
if(my_date_format($this->timestamp,'Ym')!=my_date_format($app['last_request_time'],'Ym'))
{
$updates['request_times_month'] = "`request_times_month`=1";
$updates['request_times_last_month'] = "`request_times_last_month`='{$app['request_times_month']}'";
if(my_date_format($this->timestamp,'Y')!=my_date_format($app['last_request_time'],'Y'))
{
$updates['request_times_year'] = "`request_times_year`=1";
$updates['request_times_last_year'] = "`request_times_last_year`='{$app['request_times_year']}'";
}
}
}
$this->DatabaseHandler->Query("update `".TABLE_PREFIX."app` set ".implode(" , ",$updates)." where `id`='{$app['id']}'");
}
function _sign($p,$secret_key,$signk = 'auth_sign') {
unset($p['__API__'][$signk]);
$str = '';
krsort($p);
reset($p);
foreach($p as $k=>$v) {
if(is_array($v)) {
krsort($v);
reset($v);
foreach($v as $_k=>$_v) {
$str .= ("{$k}[{$_k}]={$_v}");
}
}else {
$str .= ("{$k}={$v}");
}
}
$signv = md5($str .$secret_key);
return $signv;
}
}
?>