<?php
    //获取字典列表
    function get_dict_list($type,$lim=''){
        $where=array('type'=>$type,'deleted'=>'0');
        if($lim){
            $where['key']=array('in',$lim);
        }
        $data=M('admin_dict')->where($where)->order('sn')->select();
        return $data;
    }

    //获取字典信息
    function get_dict_info($type,$key,$what='value'){
        $where=array('type'=>$type,'key'=>$key,'deleted'=>'0');
        $data=M('admin_dict')->where($where)->find();
        if($what=='value'){
            return $data['value'];
        }else{
            return $data;
        }
    }