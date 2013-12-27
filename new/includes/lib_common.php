<?php
function get_items_cate_list($id=0,$level=0){
    $items_cate_mod=D('items_cate');
    $list=array();
    $join="LEFT JOIN ".C('DB_PREFIX')."collect_taobao as ct ON ct.cate_id=".C('DB_PREFIX')."items_cate.id";

    $res=$items_cate_mod->cache(true)->order('ordid DESC')->where('pid='.$id)
        ->join($join)
        ->select();
    foreach($res as $key=>$val){
        $val['level']=$level;
        $list[$val['id']]=$val;
        //二级分类
        $arr=$items_cate_mod->cache(true)
            ->order('ordid DESC')
            ->where('pid='.$val['id'])->join($join)
            ->select();
        //三级分类
        foreach($arr as $k2=>$v2){
            $v2['level']=$level+1;
            $v2['cls']="sub_".$val['id'];
            $list[$v2['id']]=$v2;

            $res3=$arr[$k2]['items']=$items_cate_mod->cache(true)
            	->order('ordid DESC')    
            	->where('pid='.$v2['id'])
                ->join($join)->select();
            foreach($res3 as $k3=>$v3){
                $v3['level']=$level+2;
                $v3['cls']="sub_".$val['id']." sub_".$v2['id'];
                $list[$v3['id']]=$v3;
            }
        }
        $res[$key]['items']=$arr;
    }
    return array('list'=>$res,'sort_list'=>$list);
}