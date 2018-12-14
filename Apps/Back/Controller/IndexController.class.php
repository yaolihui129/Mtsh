<?php
namespace Back\Controller;
class IndexController extends BaseController
{
    public function index()
    {
        $time=2*7*24*3600;
        //获取商户列表
        $merchantList=getMerchantList();
        $this->assign('merchantList', $merchantList);
        $merchant=I('merchant',$merchantList[0]['key']);
        setCookieKey('merchant',$merchant,$time);

        $appList=$this->getMerchantAppList($merchant);
        $this->assign('appList', $appList);
        $app=I('app',$appList[0]['key']);
        setCookieKey('app',$app,$time);

        $publicNumberList=getPublicNumberList($merchant);
        $this->assign('publicNumberList', $publicNumberList);
        $publicNumber=I('publicNumber',$publicNumberList[0]['key']);
        setCookieKey('publicNumber',$publicNumber,$time);

        $this->display();
    }


    function getMerchantAppList($merchant){
        $where=array('merchant_id'=>$merchant,'type'=>'2');
        $data=getList('admin_merchant_app',$where);
        $appList=array();
        if($data){
            foreach ($data as $k=>$da){
              $appList[$k]['key']=$da['app_id'];
              $appList[$k]['value']=getName('admin_app',$da['app_id'],'website');
            }
        }
      return $appList;
    }

}