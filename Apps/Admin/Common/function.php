<?php
    function dictInfo($type,$key){
        $res=getDictInfo($type,$key,'dict');
        return $res;
    }
    function getJieMiName($user_id){
        $user=jie_mi($user_id);
        $arr=find('user',$user);
        return $arr['real_name'];
    }
