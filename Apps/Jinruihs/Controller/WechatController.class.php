<?php
namespace Jinruihs\Controller;
class WechatController extends BaseController
{
    public function index()
    {
//        dump(111);
        $this->display();
    }


  function getBaseInfo(){
        //1.获取code
        $appid=C(appID);
        $redirect_uri = urlencode('https://xiuliguanggao.com/index.php/Jinruihs/Wechat/getUserOpenId');
        $scope='snsapi_base ';
        $state='';
        $url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid
            .'&redirect_uri='.$redirect_uri.'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
        header('location:'.$url);
  }

  function getUserOpenId(){
      //2.获取网页授权的accesstoken
      $appid=C(appID);
      $appsecret=C(appsecret);
      $code=I['code'];
      $url='https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code';
      //3.拉取到网页的openid
  }

}