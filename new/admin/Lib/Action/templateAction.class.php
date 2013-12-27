<?php
class templateAction extends baseAction
{
    function index()
    {
    	if (isset($_GET['dirname']) && trim($_GET['dirname'])!='') {                     
             $mod = M('setting');
                $mod->where("name='template'")->save(array('data'=>trim($_GET['dirname'])));                         
            $config_file =ROOT_PATH.'/index/Conf/theme.php';
            $content="<?php \r\n"
            ."return array('DEFAULT_THEME'=>'".trim($_GET['dirname'])."');";
            file_put_contents($config_file,$content);            
            @unlink(ROOT_PATH.'/index/Runtime/~Runtime.php');
            $this->success(L('operation_success'));
        }
        $this->assign('setting_template', $this->setting['template']);
        // 获得模板文件下的模板
        $tpl_dir = ROOT_PATH . '/index/Tpl/';
        $opdir = dir($tpl_dir);
        $template_list = array();
        while (false !== ($entry = $opdir->read())) {
            if ($entry{0} == '.') {
                continue;
            }
            if (!is_file($tpl_dir . $entry . '/info/info.php')) {
                continue;
            }
            $info = include_once($tpl_dir . $entry . '/info/info.php');
            if (!is_file($tpl_dir . $entry . '/info/preview.gif')) {
                $info['preview'] = __ROOT__ . '/static/images/template_no_preview.gif';
            } else {
                $info['preview'] = __ROOT__ . '/index/Tpl/' . $entry . '/info/preview.gif';
            }
            $info['dirname'] = $entry;
            $template_list[$entry] = $info;
        }
    	$this->assign('template_list',$template_list);
    	$this->display();
    }
}
?>