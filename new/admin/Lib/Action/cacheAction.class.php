<?php

class cacheAction extends baseAction {

    public function _initialize() {
        parent::_initialize();
        $this->mod = D('cache');
    }

    function index() {
        $this->update_data();
        $this->display();
    }

    function clearCache() {
        $i = intval($_REQUEST['id']);
        if (!$i) {
            $this->error('操作失败');
        } else {
            import("ORG.Io.Dir");
            $dir = new Dir;
            switch ($i) {
                case 1:
                    //更新全站缓存
                    is_dir(CACHE_PATH) && $dir->del(CACHE_PATH);
                    is_dir(DATA_PATH . '_fields/') && $dir->del(DATA_PATH . '_fields/');
                    is_dir(TEMP_PATH) && $dir->del(TEMP_PATH);
                    is_dir("./index/Runtime/Cache/") && $dir->del("./index/Runtime/Cache/");
                    is_dir("./index/Runtime/Data/_fields/") && $dir->del("./index/Runtime/Data/_fields/");
                    is_dir("./index/Runtime/Temp/") && $dir->del("./index/Runtime/Temp/");
                    is_dir("./index/Runtime/") && $dir->del("./index/Runtime/");
                    break;
                case 2:
                    //后台模版缓存
                    is_dir(CACHE_PATH) && $dir->del(CACHE_PATH);
                    break;
                case 3:
                    //前台模版缓存
                    is_dir("./index/Runtime/Cache/") && $dir->del("./index/Runtime/Cache/");
                    is_dir("./index/Html/") && $dir->del("./index/Html/");
                    break;
                case 4:
                    //数据库缓存
                    is_dir(DATA_PATH . '_fields/') && $dir->del(DATA_PATH . '_fields/');
                    is_dir("./index/Runtime/Data/_fields/") && $dir->del("./index/Runtime/Data/_fields/");
                    break;
                default:break;
            }
            $runtime = defined('MODE_NAME') ? '~' . strtolower(MODE_NAME) . '_runtime.php' : '~runtime.php';
            $runtime_file_admin = RUNTIME_PATH . $runtime;
            $runtime_file_front = ROOT_PATH . '/index/Runtime/' . $runtime;
            is_file($runtime_file_admin) && @unlink($runtime_file_admin);
            is_file($runtime_file_front) && @unlink($runtime_file_front);
            $this->success('更新完成', U('cache/index'));
        }
    }
    function update_data() {
        $items_mod = D('items');
        $items_cate_mod = D('items_cate');
        $items_comments_mod = D('user_comments');
        
        //更新商品分类的数量
        $items_cate_mod->where('1=1')->save(array('item_nums' => 0));
        $items_nums = $items_mod->field('cid,count(id) as cate_nums')->group('cid')->select();
        foreach( $items_nums as $val ){
            $items_cate_mod->save(array('id'=>$val['cid'],'item_nums'=>$val['cate_nums']));
        }
        
        //更新商品喜欢的数量
        $items_nums = $items_mod->field('cid,sum(likes) as total')->group('cid')->select();
        foreach( $items_nums as $val ){
            $items_cate_mod->save(array('id'=>$val['cid'],'item_likes'=>$val['total']));
        }
        
        //更新商品的评论
        $items_mod->where('1=1')->save(array('comments'=> '0'));
        $comments_nums = $items_comments_mod->field('pid,count(pid) as total')->where("type='item' and status='1'")->group('pid')->select();
        foreach( $comments_nums as $val ){
            $items_mod->save(array('id'=>$val['pid'],'comments'=>$val['total']));
        }       
    }
}
?>