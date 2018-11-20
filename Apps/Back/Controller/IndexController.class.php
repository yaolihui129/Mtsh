<?php
namespace Back\Controller;
class IndexController extends BaseController
{
    public function index()
    {
        $appList=$this->getAppList();
        $this->assign('appList', $appList);
        $app=I('app',$appList[0]['key']);
        cookie('app',$app,array('expire'=>2*7*24*3600,'prefix'=>C(PRODUCT).'_'));

        $merchantList=$this->getMerchantList();
        $this->assign('merchantList', $merchantList);
        $merchant=I('merchant',$merchantList[0]['key']);
        cookie('merchant',$merchant,array('expire'=>2*7*24*3600,'prefix'=>C(PRODUCT).'_'));

        $publicNumberList=$this->getPublicNumberList($merchant);
        $this->assign('publicNumberList', $publicNumberList);
        $publicNumber=I('publicNumber',$publicNumberList[0]['key']);
        cookie('publicNumber',$publicNumber,array('expire'=>2*7*24*3600,'prefix'=>C(PRODUCT).'_'));

        $this->display();
    }


  

}