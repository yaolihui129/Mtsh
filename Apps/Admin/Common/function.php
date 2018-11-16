<?php
    //封装下啦菜单
    function select($data, $name, $value)
    {
        $html = '<select name="' . $name . '" class="form-control">';
        foreach ($data as $v) {
            $selected = ($v['key'] == $value) ? "selected" : "";
            $html .= '<option ' . $selected . ' value="' . $v['key'] . '">' . $v['value'] . '</option>';
        }
        $html .= '<select>';
        return $html;
    }
    //获取列表
    function get_list($table,$where,$name='name',$order='id'){
        $data=M($table)->where($where)->order($order)->select();
        $list=array();
        foreach ($data as $k=>$da){
            $list[$k]['key']=$da['id'];
            $list[$k]['value']=$da[$name];
        }
        return $list;
    }

    //获取字典列表
    function get_dict_list($type,$lim=''){
        $where=array('type'=>$type,'deleted'=>'0');
        if($lim){
            $where['key']=array('in',$lim);
        }
        $data=M('dict')->where($where)->order('sn')->select();
        return $data;
    }



    //获取字典信息
    function get_dict_info($type,$key,$what='value'){
        $where=array('type'=>$type,'key'=>$key,'deleted'=>'0');
        $data=M('dict')->where($where)->find();
        if($what=='value'){
            return $data['value'];
        }else{
            return $data;
        }
    }

    //封装字典为下拉菜单
    function dict_list($type,$field,$default='0',$lim=''){
        $data=$this->get_dict_list($type,$lim);
        $var=$this->select($data,$field,$default);
        return $var;
    }