<?php
namespace Back\Controller;
class IndexController extends BaseController
{
    public function index()
    {
        $time=2*7*24*3600;
        $appList=getAppList();
        $this->assign('appList', $appList);
        $app=I('app',$appList[0]['key']);
        setCookieKey('app',$app,$time);

        $merchantList=getMerchantList();
        $this->assign('merchantList', $merchantList);
        $merchant=I('merchant',$merchantList[0]['key']);
        setCookieKey('merchant',$merchant,$time);

        $publicNumberList=getPublicNumberList($merchant);
        $this->assign('publicNumberList', $publicNumberList);
        $publicNumber=I('publicNumber',$publicNumberList[0]['key']);
        setCookieKey('publicNumber',$publicNumber,$time);

        $this->display();
    }


  

}