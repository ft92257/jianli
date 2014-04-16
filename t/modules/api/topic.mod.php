<?php
if(!defined('IN_JISHIGOU'))
{
exit('invalid request');
}
class ModuleObject extends MasterObject
{
function ModuleObject($config)
{
$this->MasterObject($config);
$this->Execute();
}
function Execute()
{
switch($this->Code)
{
case 'do_add':
case 'add':
{
$this->DoAdd();
break;
}
case 'delete':
case 'del':
{
$this->Delete();
break;
}
case 'edit':
case 'compile':				
case 'modify':
{
$this->DoModify();
break;
}
case 'view':
case 'show':
{
$this->Show();
break;
}
case 'list':
{
$this->DoList();
break;
}
case 'comment':
{
$this->Comment();
break;
}
case 'favorite':
{
$this->Favorite();
break;
}
default :
{
$this->Main();
break;
}
}
}
function Main()
{
api_output('topic api is ok');
}
function DoAdd() {
$content = trim(strip_tags($this->Inputs['content']));
if(!$content) {
api_error('content is empty',104);
}
$totid = max(0,(int) $this->Inputs['totid']);
$from = "api";
$item = $this->Inputs['item'];
$item_id = 0;
if($item &&in_array($item,array('qun',))) {
$item_id = max(0,(int) $this->Inputs['item_id']);
Load::functions('app');
if(false == (app_check($item,$item_id))) {
$item_id = 0;
}
}
if($item_id <1) {
$item = '';
}
$type = 'first';
$_type = trim(strtolower($this->Inputs['type']));
if($item_id >0 &&in_array($_type,array('qun',))) {
$type = $_type;
}elseif ($totid >0 &&in_array($_type,array('both','forward',))) {
$type = $_type;
}elseif(in_array($_type,array('reply',))) {
$type = 'reply';
}else {
$type = 'first';
}
$p = array();
if($_FILES['pic']) {
$p['pic_field'] = 'pic';
}else {
$p['pic_url'] = $this->Inputs['pic_url'];
}
$imageid = 0;
if($p) {
$rets = Load::logic('image',1)->upload($p);
if(!$rets['error']) {
$imageid = max(0,(int) $rets['id']);
}
}
$datas = array(
'content'=>$content,
'totid'=>$totid,
'from'=>$from,
'type'=>$type,
'uid'=>MEMBER_ID,
'item'=>($item ?$item : 'api'),
'item_id'=>($item_id >0 ?$item_id : $this->app['id']),
'imageid'=>$imageid,
);
$add_result = $this->TopicLogic->Add($datas);
if(is_array($add_result) &&count($add_result)) {
api_output($this->_topic($add_result['tid']));
}else {
api_error(($add_result ?$add_result : '銆愰€氱煡銆戝唴瀹瑰彂甯冨け璐�'),105);
}
}
function Delete()
{
$id = max(0,(int) $this->Inputs['id']);
if(!$id) {
api_error('id is empty',102);
}
$topic = $this->_topic($id);
if(!$topic) {
api_error('id is invalid',103);
}
if($topic['uid']!=$this->user['uid']) {
api_error('id is invalid',103);
}
$return = $this->TopicLogic->DeleteToBox($id);
if($return) {
api_error($return,106);
}
api_output('delete is ok');
}
function DoModify() {
$id = max(0,(int) $this->Inputs['id']);
if(!$id) {
api_error('id is empty',102);
}
$content = trim(strip_tags($this->Inputs['content']));
if(!$content) {
api_error('content is empty',104);
}
$topic = $this->_topic($id);
if(!$topic) {
api_error('id is invalid',103);
}
if('admin'!= $this->user['role_type']) {
if($topic['uid'] != $this->user['uid']) {
api_error('You do not have permission to edit',115);
}
if($topic['replys'] >0 ||$topic['forwards'] >0) {
api_error('Has been comments or forwarding, can\'t edit',116);
}
if($this->Config['topic_modify_time'] &&(($topic['addtime'] ?$topic['addtime'] : $topic['dateline']) +($this->Config['topic_modify_time'] * 60)) <TIMESTAMP) {
api_error('Beyond can edit time',117);
}
}
$this->TopicLogic->Modify($id,$content);
api_output('modify is ok');
}
function Show() {
$id = max(0,(int) $this->Inputs['id']);
if(!$id) {
api_error('id is empty',102);
}
$topic = $this->_topic($id);
if(!$topic) {
api_error('id is invalid',103);
}
if($topic['longtextid'] >0) {
$longtext_info = Load::logic('longtext',1)->get_info($topic['longtextid'],1);
if($longtext_info &&$longtext_info['tid']==$id) {
$topic['longtext'] = nl2br($longtext_info['longtext']);
}
}
api_output($topic);
}
function DoList() {
$this->_topic_list();
}
function Comment()
{
$id = max(0,(int) $this->Inputs['id']);
if(!$id)
{
api_error('id is empty',102);
}
$topic = $this->TopicLogic->Get($id);
if(!$topic)
{
api_error('id is invalid',103);
}
$return = array();
$reply_ids = $this->TopicLogic->GetReplyIds($topic['tid']);
$reply_ids_count = count($reply_ids);
if($reply_ids &&$reply_ids_count)
{
$return = $this->_page($reply_ids_count);
$sql_where = " where `tid` in ('".implode("','",$reply_ids)."') order by `tid` desc limit {$return[offset]},{$return[count]}";
$return['comments'] = $this->_topic($sql_where);
}
api_output($return);
}
function Favorite() {
$id = max(0,(int) $this->Inputs['id']);
if(!$id) {
api_error('id is empty',102);
}
$topic = $this->_topic($id);
if(!$topic) {
api_error('id is invalid',103);
}
$act = (in_array($this->Inputs['act'],array('check','add','delete')) ?$this->Inputs['act'] : 'check');
$ret = Load::logic('other',1)->TopicFavorite(MEMBER_ID,$id,$act);
$ret = ('check'==$act ?($ret ?1 : 0) : 1);
api_output($ret);
}
}
?>