<?php 

/**
 * 	装修公司后台登陆
 */
class UserAction extends BaseAction {
  
	public function __construct() {
		parent::__construct();
	
		$this->model = D('User');
	}

	/*public function test() {
		$where = array(
				'type in' => '1,2,3,4',//array(1,2,3),
				'qq like' => "%r'rr%",
				'money not between' => array(1,2),
				"`account` = 'ffff'",
				'id > | uid < | fid =' => array(1,2,3),
				);
		$this->model->w(array('id >=' => 2));
		$this->model->w($where, false);
		//$this->model->w();
		$this->model->select();
		echo $this->model->getLastSql();
	}*/
	
	/*
	 * 注册 TODO 装修公司和设计师注册
	*/
	public function register() {
		if (!$this->isPost()) {
			$this->_display_form();
		} else {
			$database = array(
				'type' => 1,//普通用户
				'reg_ip' => get_client_ip(),
			);
			$uid = $this->_add($database, '', true);
			if ($uid) {
				//TODO同步添加旧版数据
				
				$this->model->login();
				$this->success('注册成功！', U('/Index/index'));
			} else {
				$this->error($this->model->getError());
			}
		}
	
	}
	
	/*
	 * 登录
	*/
	public function login() {
		if ($this->isLogin()) {
			$this->error('您已经登录了', U('/Index/index'));
		}
	
		if (!$this->isPost()) {
			$this->display();
			die;
		}
	
		if ($this->model->login()) {
			$this->success('登录成功！', U('/Index/index'));
		} else {
			$this->error($this->model->getError());
		}
	}
	
	/*
	 * 退出
	*/
	public function logout() {
		D('Session')->destroy($this->oUser->id);
		$this->success('退出成功！');
	}
	
	public function qq_login() {
		require_once "./public/Library/qqAuth/qqConnectAPI.php";
		$qc = new QC();
		$qc->qq_login();
	}
	
	public function qq_callback() {
		require_once "./public/Library/qqAuth/qqConnectAPI.php";
		$qc = new QC();
		//echo $qc->qq_callback();
		$openid = $qc->get_openid();
		$arr = $qc->get_user_info();
		$nickname = $arr["nickname"];
		
		//TODO 注册登录操作
	}
	
	public function sina_login() {
		$dir = './Public/Library/sinaAuth/';
		include_once( $dir . 'config.php' );
		include_once( $dir . 'saetv2.ex.class.php' );
		
		$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
		$code_url = $o->getAuthorizeURL( WB_CALLBACK_URL );
		
		echo '<a href="'.$code_url.'">点击进入授权页面</a>';
	}
	
	public function sina_callback() {
		$dir = './Public/Library/sinaAuth/';
		include_once( $dir . 'config.php' );
		include_once( $dir . 'saetv2.ex.class.php' );

		$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );

		if (isset($_REQUEST['code'])) {
			$keys = array();
			$keys['code'] = $_REQUEST['code'];
			$keys['redirect_uri'] = WB_CALLBACK_URL;
			try {
				$token = $o->getAccessToken( 'code', $keys ) ;
			} catch (OAuthException $e) {
			}
		}

		if ($token) {
			$_SESSION['token'] = $token;
			setcookie( 'weibojs_'.$o->client_id, http_build_query($token) );

			$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );
			//$ms  = $c->home_timeline(); // done
			$uid_get = $c->get_uid();
			$uid = $uid_get['uid'];
			$user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息

			$openid = $user_message['idstr'];
			$nickname = $user_message['name'];
			//TODO 注册登录
		} else {
			echo '授权失败。';
		}
	}
	
}
?>