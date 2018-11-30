<?php

    function dictInfo($type,$key){
        $res=get_dict_info($type,$key,'admin_dict');
        return $res;
    }

    function getJieMiName($user_id){
        $user=jie_mi($user_id);
        $arr=find('admin_user',$user);
        return $arr['real_name'];
    }

    function getAppList(){
        $where=array('user_id'=>getLoginUserID());
        $data=getList('admin_user_app',$where);
        $appList=array();
        if($data){
            foreach ($data as $k=>$da){
                $appList[$k]['key']=$da['app_id'];
                $appList[$k]['value']=getName('admin_app',$da['app_id'],'website');
            }
        }
        return $appList;
    }

    function getMerchantList(){
        $where=array('user_id'=>getLoginUserID());
        $data=getList('admin_merchant_user',$where);
        $merchantList=array();
        if($data){
            foreach ($data as $k=>$da){
                $merchantList[$k]['key']=$da['merchant_id'];
                $merchantList[$k]['value']=getName('admin_merchant',$da['merchant_id']);
            }
        }
        return $merchantList;
    }

    function getPublicNumberList($merchant=''){

        if(!$merchant){
            $where=array('user_id'=>getLoginUserID());
            $data=getList('admin_merchant_user',$where);
            $merchant=$data[0];
        }
        $where=array('merchant_id'=>$merchant);
        $where['type']=array('in',['0','1']);
        $data=getList('admin_merchant_app',$where);
        $publicNumberList=array();
        if($data){
            foreach ($data as $k=>$da){
                $publicNumberList[$k]['key']=$da['app_id'];
                $publicNumberList[$k]['value']=getName('admin_app',$da['app_id']);
            }
        }
        return $publicNumberList;
    }