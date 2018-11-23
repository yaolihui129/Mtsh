<?php
namespace Jinruihs\Controller;
use Think\Controller;
class WechatController extends Controller{
    //验证消息接口
    public function msg(){
        header("Content-Type:text/plain;charset=utf-8");
        $nonce      = $_GET['nonce'];
        $token      = "123weixin";
        $timestamp  = $_GET['timestamp'];
        $echostr    = $_GET['echostr'];
        $signature  = $_GET['signature'];
        $array = array();
        $array = array($nonce,$timestamp,$token);               //形成数组,
        sort($array,SORT_REGULAR );                                           //按字典排序
        $str =sha1(implode($array));                            //拼接成字符串，sha1加密,然后与signature进行校验
        if($str == $signature && $echostr){                     //第一次接入Weixin api 接口的时候
            echo $echostr;
            exit;
        }else {
            $this->reponseMsg();
        }
    }

    public function reponseMsg()
    {   //接收事件推送并回复
        $postArr = $GLOBALS['HTTP_RAW_POST_DATA'];    //1.获取到微信推送过来的post数据（xml格式）
        $postObj = simplexml_load_string($postArr);   //2.处理消息类型，并设置回复类型和内容
        $toUser = $postObj->FromUserName;
        $fromUser = $postObj->ToUserName;
        if(strtolower($postObj->MsgType) == 'event'){

        }
    }

    function getAccessToken() {
        $appID=C(appID);
        $appsecret=C(appsecret);
        $access_token= S($appID.'access_token');
        if (!$access_token) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appID."&secret=".$appsecret;
            $res = json_decode(httpGet($url));
            $access_token = $res->access_token;
            if($access_token){
                S($appID.'access_token',$access_token,7000);
            }
        }
        return $access_token;
    }



    function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function getJsApiTicket() {
        $appID=C(appID);
        $ticket=S($appID.'ticket');
        if (!$ticket) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode(httpGet($url));
            $ticket = $res->ticket;
            if ($ticket) {
                S($appID.'ticket',$ticket,7000);
            }
        }

        return $ticket;
    }


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

        //用openID登陆
        $this->openidLogin($appid,$arr['openid']);
        cookie($appid.'_refresh_token',$arr['refresh_token']);
        $scope=$arr['scope'];
        if($scope=='snsapi_userinfo'){
            $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$arr['openid'].'&lang=zh_CN';
            $userinfo=httpGet($url);
            cookie($appid.'_userinfo', $userinfo);
            //更新用户信息
            $this->updateCustomerThirdParty($userinfo,$state);
        }
        redirect($_SESSION['uri'].'/openid/'.$arr['openid']);
    }

    function openidLogin($appid,$openid){
        $where=array('app_id'=>$appid,'openid'=>$openid,'deleted'=>'0');
        $data=M('customer_third_party')->where($where)->find();
        if(!$data){
            //插入数据
            if($openid){
                $data['app_id']=$appid;
                $data['merchant_id']=C(MERCHANTID);
                $data['openid']=$openid;
                $data['id']=insert('customer_third_party',$data);
            }
        }
        //设置登录态
        cookie($appid.'_isLogin',$data['id']);
        cookie($appid.'_openid',$openid);
        return true;

    }
    //更新用户信息
    function updateCustomerThirdParty($userinfo,$state){
        $userinfo=json_decode($userinfo,true);
        $userinfo['id']= cookie(C(appID).'_isLogin');
        $userinfo['source']= $state;
        $userinfo['flag']= '0';
        update('customer_third_party',$userinfo);
    }

}