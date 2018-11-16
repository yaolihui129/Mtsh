<?php
namespace Jinruihs\Controller;
class IndexController extends BaseController
{
    public function index()
    {
        if(!I('openid')){
            $_SESSION['uri'] =C(WEBSITE). '/'.C(PRODUCT).'/Index/index';
            $scope='snsapi_base';
//            $scope='snsapi_userinfo';
            $this->getBaseInfo($scope);
        }
        $this->display();
    }


  

}