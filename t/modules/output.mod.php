<?php

/**
 *
 * 微博输出模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id$
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}


class ModuleObject extends MasterObject
{
	var $in_ajax = 0;


	
	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->in_ajax = $this->_input('in_ajax');


		$this->Execute();

	}

	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'url_js':
				$this->UrlJs();
				break;
			case 'url_iframe':
				$this->UrlIframe();
				break;
			case 'url_iframe_post':
				$this->UrlIframePost();
				break;
				
			case 'js':
				$this->js();
				break;
			case 'css':
				$this->css();
				break;

			default :
				{
					$this->Main();
					break;
				}
		}
		$val = ob_get_clean();

		$this->_output($val);
	}

	function Main() {
		;
			}

	function UrlJs() {
		$hash = '';
		$info = array();
		$hash_verify = 0;
		$id = (int) $this->_input('id', 0, 0);
		$per_page_num = (int) $this->_input('per_page_num', 0, 0);
		$content_default = get_safe_code($this->_input('content_default', 0, ''));
		if($id > 0) {
			$info = DB::fetch_first("select * from ".DB::table('output')." where `id`='$id'");
			if($info) {
				$hash = trim($this->_input('hash', 0, ''));
				if($info['hash'] == $hash) {
					$hash_verify = 1;
					DB::query("update ".DB::table('output')." set `open_times`=`open_times`+1 where `id`='$id'");
				}
				
				if($per_page_num < 1) $per_page_num = $info['per_page_num'];
				if(!$content_default) $content_default = $info['content_default'];
			}
		}
		if($per_page_num < 1) $per_page_num = 10;

		$target_id = $this->_input('target_id', 1, 'jishigou_div');
		
		
		rewriteDisable();
		include template('output_url_js');
	}

	function UrlIframe() {
		$hash = '';
		$info = array();
		$hash_verify = 0;
		$id = (int) $this->_input('id', 0, 0);
		$per_page_num = (int) $this->_input('per_page_num', 0, 0);
		$content_default = get_safe_code($this->_input('content_default', 0, ''));
		if($id > 0) {
			$info = DB::fetch_first("select * from ".DB::table('output')." where `id`='$id'");
			if($info) {
				$hash = trim($this->_input('hash', 0, ''));
				if($info['hash'] == $hash) {
					$hash_verify = 1;
									}
			}
		}
		if(!$hash_verify) {
			if(true===DEBUG && get_param('debug')) {
				;
			} else {
				exit('id or hash is invalid');
			}
		}
		$info['per_page_num'] = ($per_page_num > 0 ? $per_page_num : ($info['per_page_num'] > 0 ? $info['per_page_num'] : 10));
		$info['content_default'] = ($content_default ? $content_default : $info['content_default']);

		$url_info = array();
		$item = 'url';
		$item_id = (int) $this->_input('item_id', 0, 0);
		if($item_id  < 1) {
			$url = $this->_input('url', 1);
			$title = $this->_input('title', 1);
				
			$url_info = get_url_info($url, $title);
		} else {
			Load::logic('url');
			$UrlLogic = new UrlLogic();

			$url_info = $UrlLogic->get_info_by_id($item_id);
			$url = $url_info['url'];
			$title = $url_info['title'];
		}
		if(!$url_info) {
			exit('url is invalid');
		}


		if($info['lock_host']) {
			$host_verify = 0;
			$lock_hosts = explode("\n", $info['lock_host']);
			foreach($lock_hosts as $v) {
				$v = trim($v);
				if(false !== strpos($url, $v)) {
					$host_verify = 1;
					break;
				}
			}
			if(!$host_verify) {
				exit('host is invalid');
			}
		}


			
		$item_id = $url_info['id'];
		if($item_id < 1) {
			exit('item_id is invalid');
		}

		Load::logic("topic_list");
		$TopicListLogic = new TopicListLogic();

		$perpage = (int) $info['per_page_num'];
		$page_url = "index.php?mod=output&code=url_iframe&id=$id&hash=$hash&item_id=$item_id&per_page_num=$per_page_num&content_default=".urlencode($content_default);		
		$param = array(
				'perpage' => ($perpage < 1 ? 20 : $perpage),
				'page_url' => $page_url,
				'page_extra' => ' target="_self" ',
				'where' => " item='$item' AND item_id='$item_id' ",
		);
		$get_datas = $TopicListLogic->get_data($param);
		if (!empty($get_datas)) {
			$total_record = $get_datas['count'];
			$topic_list = $get_datas['list'];
			$page_arr = $get_datas['page'];
				
			if($topic_list) {
				
				$TopicLogic = Load::logic('topic', 1);

				$parent_list = $TopicLogic->GetParentTopic($topic_list);
			}
		}
			

		$url_encode = urlencode($url);
		$this->Title = $title;
		rewriteDisable();
		include template('output_url_iframe');
	}

	function UrlIframePost() {
		$content = $this->_input('content', $this->in_ajax);
		$item_id = max(0, (int) $this->_input('item_id'));
		$item = '';
		if($item_id > 0) {
			$item = 'url';
		}
		$totid = max(0, (int) $this->_input('totid'));
		$type = $this->_input('type');

		
		$TopicLogic = Load::logic('topic', 1);

		$datas = array(
			'item' => $item,
			'item_id' => $item_id,
			'totid' => $totid,
			'type' => $type,
			'content' => $content,		
		);
		$rets = $TopicLogic->Add($datas);

		$error = 0;
		$message = '';
		if(is_array($rets)) {
			$message = "【发布成功】";
			if($rets['tid'] < 1) {
				if($rets['msg']) {
					$message .= $rets['msg'];
				} else {
					$message .= implode(',', $rets);
				}
			}
		} else {
			$error = 1;
			$message = $rets ? $rets : "发布失败";
		}

		if($this->in_ajax) {
			if($error) {
				json_error($message);
			} else {
				json_result($message, $rets);
			}
		}

		$this->_message($message, '', 0);
	}

	function _message($message='', $redirect_to='', $stop_time=5) {
		if($message && !$redirect_to) {
			$redirect_to = referer();
		}

		if(!$message && $redirect_to) {
			header("Location: $redirect_to");
		}

		$stop_time = max(0, (int) $stop_time);


		include template('output_message');
	}

	function _input($var=null, $is_utf8=0, $ifemptyval=null) {
		$val = get_param($var);

		if($is_utf8) {
						$val = get_safe_code($val);
		}

		if(!$val) {
			$val = $ifemptyval;
		}

		return $val;
	}

	function _output($val, $to_utf8=0, $halt=1) {
		if($to_utf8) {
			$val = array_iconv($this->Config['charset'], 'utf-8', $val);
		}

		echo $val;

		if($halt) {
			exit;
		}
	}
	
	
	function js() {
		header('Content-type: application/x-javascript');
		$f = get_param('f');
		$cache_id = 'js/' . ($f ? md5($f) : 'default');
		if(false === ($js = cache_file('get', $cache_id))) {
			if($f) {
				$fs = explode(',', $f);
			} else {
								$fs = array(
					'js/min.js',
					'js/common.js',
					'js/topicManage.js',
					'js/rotate.js',
					'js/dialog.js',
					'js/lang.js',
					'images/uploadify/jquery.uploadify.v2.1.4.min.js',
				);
			}
			$js = '';
			foreach($fs as $_f) {
				$js .= $this->_js($_f) . "\n";
			}
			cache_file('set', $cache_id, $js);
		} else {
			header('HTTP/1.0 304 Not Modified');
		}
		
		exit($js);
	}
	function _js($f) {
		$f = (is_file($f) ? $f : ROOT_PATH . 'templates/default/' . $f);
		$js = Load::lib('io', 1)->ReadFile($f);
		$js = Load::model('jsmin')->mini($js);
		return $js;
	}
	
	function css() {
		;
	}

}

?>
