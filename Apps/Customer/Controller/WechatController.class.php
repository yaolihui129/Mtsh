<?php
namespace Customer\Controller;
class WechatController extends BaseController{


  function getBaseInfo($scope='snsapi_userinfo',$state='123'){
        //1.获取code $scope='snsapi_base';snsapi_userinfo
        $appid = C(appID);
        $redirect_uri = urlencode('https://xiuliguanggao.com/Jinruihs/Wechat/getUserOpenId');
        $url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid
              .'&redirect_uri='.$redirect_uri.'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
        header('Location:'.$url);
  }

  function getUserOpenId(){
      $appid     = C(appID);
      $appsecret = C(appsecret);
      $code = $_GET['code'];
      $state= $_GET['state'];
      $url= 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code';
      $res = httpGet($url);
      $arr = json_decode($res,true);
      $access_token=$arr['access_token'];
      cookie(C(PRODUCT).'_openid',$arr['openid']);
      //用openID登陆
      $this->openidLogin($appid,$arr['openid']);
      cookie(C(PRODUCT).'_refresh_token',$arr['refresh_token']);
      cookie(C(PRODUCT).'_userToken='.$arr['openid'],$access_token,7200);
      $scope=$arr['scope'];
      if($scope=='snsapi_userinfo'){
          $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$arr['openid'].'&lang=zh_CN';
          $userinfo=httpGet($url);
          cookie(C(PRODUCT).'_userinfo', $userinfo);
          //更新用户信息
          $this->updateCustomerThirdParty($userinfo,$state);
      }

      redirect($_SESSION['uri'].'/openid/'.$arr['openid']);

  }

  function openidLogin($appid,$openid){
      $where=array('app_id'=>$appid,'openid'=>$openid,'deleted'=>'0');
      $data=M('tp_customer_third_party')->where($where)->find();
      if(!$data){
          //插入数据
          $data['app_id']=$appid;
          $data['openid']=$openid;
          $data['id']=insert('tp_customer_third_party',$data);
      }
      //设置登录态
      cookie(C(PRODUCT).'_isLogin',$data['id']);
//      cookie(C(PRODUCT).'_token',$openid);
      return true;

  }
  //更新用户信息
  function updateCustomerThirdParty($userinfo,$state){
      $userinfo=json_decode($userinfo,true);
      $userinfo['id']= cookie(C(PRODUCT).'_isLogin');
      $userinfo['source']= $state;
      $userinfo['flag']= '0';
      update('tp_customer_third_party',$userinfo);
  }

  //拉取公众号用户信息
    function getWechatUsersList($token,$next_openid=''){
      $url='https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$token.'&next_openid='.$next_openid;
      $res=httpGet($url);
      $this->ajaxReturn($res);
    }

}