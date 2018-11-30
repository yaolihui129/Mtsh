<?php
namespace Tuocai\Controller;
use Think\Controller;
class WechatController extends Controller{
    public function msg(){//验证消息接口
//        header("Content-Type:text/plain;charset=utf-8");
        $nonce      = I('nonce');
        $token      = "wechat";
        $timestamp  = I('timestamp');
        $echostr    = I('echostr');
        $signature  = I('signature');
        $array = array($nonce,$timestamp,$token);               //形成数组,
        sort($array);                                           //按字典排序
        $str =sha1(implode($array));                            //拼接成字符串，sha1加密,然后与signature进行校验
        if($str == $signature){                     //第一次接入Weixin api 接口的时候
            echo $echostr;
            exit;
        }
//        else {
//            $this->reponseMsg();
//        }
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
            "appId"     => C(appID),
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
        $redirect_uri = urlencode(C(WEBSITE).'/'.C(PRODUCT).'/Wechat/getUserOpenId');
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

    function qrCodeTime($id,$day=30){//getTimeQrCode($wxId,$scene_id,$expire=30)
        $url=$this->getTimeQrCode($this->getAccessToken(),$id,$day);
        echo "临时二维码";
        echo "<img src='".$url."'/>";
    }

    function qrCodeForever($id){//getForeverQrCode($wxId,$scene_id);
        $url=$this->getForeverQrCode($this->getAccessToken(),$id);
        echo "用久二维码";
        echo "<img src='".$url."'/>";
    }

    function sendTemplateMsg($touser,$template_id,$call_url,$data){
//        $touser='oZQWOxPB-cAH37NlpBsB3CuRIwYU';
//        $template_id='H5Xu84_YhAT0-IpaYdzWcNFAKb2V6P7-7f0EMA2TYcI';
//        $call_url='http://www.zhihuixinda.com';
//        $data=array(
//            'name'=>array('value'=>'微信号申请','color'=>"#173177"),
//            'money'=>array('value'=>100,'color'=>"#173177"),
//            'date'=>array('value'=>date('Y-m-d H:i:s')),'color'=>"#173177"
//        );
        $res = wxSendTemplateMsg($this->getAccessToken(),$touser,$template_id,$call_url,$data);
        $this->ajaxReturn($res);
    }


    //换取微信短链接
    function getShortUrl($long_url){
        $url='https://api.weixin.qq.com/cgi-bin/shorturl?access_token='.$this->getAccessToken();
        $array=array('action'=>'long2short','long_url'=>$long_url );                        //组装数组
        $postJson = json_encode($array);                                                    //封装json
        $res = httpPost($url, $postJson);
        $res = json_decode($res,true);
        return $res['short_url'];
    }

    //获取临时二维码
    function getTimeQrCode($scene_id,$expire=30){
        $url='https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->getAccessToken();
        $expire = $expire*24*60*60;
        $postArr =array(                                                                //组装数组
            'expire_seconds'=>$expire,
            'action_name'=>"QR_SCENE",
            'action_info'=>array('scene'=>array('scene_id'=>$scene_id) )
        );
        $postJson = json_encode($postArr);                                              //封装json
        $res = httpPost($url, $postJson);                                               //获取 $ticket
        $res = json_decode($res,true);                                          //转换成数组
        $long_url='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$res['ticket']; //使用$ticket换去二维码图片
        return $this->getShortUrl($long_url);
    }

    //获取永久二维码
    function getForeverQrCode($scene_id){
        $url='https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->getAccessToken();
        $postArr =array(                                                                //1.组装数组
            'action_name'=>"QR_LIMIT_SCENE",
            'action_info'=>array('scene'=>array('scene_id'=>$scene_id))
        );
        $postJson = json_encode($postArr);                                              //2.封装json
        $res = httpPost($url, $postJson);
        $res = json_decode($res,true);
        $long_url='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$res['ticket']; //3.使用$ticket换去二维码图片
        return $this->getShortUrl($long_url);
    }


    //微信纯文本回复
    function wxReplyText($toUser,$fromUser,$content){
        //回复用户消息(纯文本格式)
        $msgType   = 'text';
        $time      = time();
        $template  ="<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                    </xml>";
        echo sprintf($template,$toUser,$fromUser,$time,$msgType,$content);
    }

    //微信图文回复
    function wxReplyNews($toUser,$fromUser,$arr){
        $msgType   = 'news';
        $time      = time();
        $template = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <ArticleCount>".count($arr)."</ArticleCount>
            <Articles>";
        foreach($arr as $v){
            $template .="<item>
                    <Title><![CDATA[".$v['title']."]]></Title>
                    <Description><![CDATA[".$v['description']."]]></Description>
                    <PicUrl><![CDATA[".$v['picUrl']."]]></PicUrl>
                    <Url><![CDATA[".$v['url']."]]></Url>
                    </item>";
        }
        $template .="
            </Articles>
        </xml> ";
        echo sprintf($template, $toUser, $fromUser, $time, $msgType);
    }

    //微信图片回复
    function wxReplyImage($toUser,$fromUser,$mediaId){
        $msgType   = 'image';
        $time      = time();
        $template  = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <Image><MediaId><![CDATA[%s]]></MediaId></Image>
        </xml>";
        echo sprintf($template, $toUser, $fromUser, $time, $msgType,$mediaId);
    }

    //微信语音回复
    function wxReplyVoice($toUser,$fromUser,$mediaId){
        $msgType   = 'voice';
        $time      = time();
        $template  = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <Voice><MediaId><![CDATA[%s]]></MediaId></Voice>
        </xml>";
        echo sprintf($template, $toUser, $fromUser, $time, $msgType,$mediaId);
    }

    //微信视频回复
    function wxReplyVideo($toUser,$fromUser,$mediaId,$title,$description){
        $msgType   = 'video';
        $time      = time();
        $template = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <Video>
                <MediaId><![CDATA[%s]]></MediaId>
                <Title><![CDATA[%s]]></Title>
                <Description><![CDATA[%s]]></Description>
            </Video>                
        </xml>";
        echo sprintf($template, $toUser, $fromUser, $time, $msgType,$mediaId,$title,$description);
    }

    //微信音乐回复
    function wxReplyMusic($toUser,$fromUser,$mediaId,$title,$description,$musicUrl,$HQMusicUrl,$thumbMediaId){
        $msgType   = 'music';
        $time      = time();
        $template = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <Music>
                <Title><![CDATA[%s]]></Title>
                <Description><![CDATA[%s]]></Description>
                <MusicUrl><![CDATA[%s]]></MusicUrl>
                <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
                <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
            </Music>
        </xml>";
        echo sprintf($template, $toUser, $fromUser, $time, $msgType,$title,$description,$musicUrl,$HQMusicUrl,$thumbMediaId);
    }

    function wxNewsArr($size){
        $str=array();
        for ($x=0; $x<=$size; $x++) {
            $str.="array(
            'title'        =>  ".'$data['.$x."]['name'],
            'description'  =>  ".'$data['.$x."]['content'],
            'picUrl'       =>  C(WEBSERVER).'/Upload/'.".'$data['.$x."]['productimg'],
            'url'          =>  C(WEBSERVER).'/index.php/'.C(PRODUCT).'/Service/index/id/'.".'$data['.$x."]['productid']".".'/wxOpenId/'".'.$toUser.'."'/wxAppId/'".'.$fromUser,'."
            ),";
        }
        return $str;
    }

    //群发接口
    function wxSendMsgAll($token,$array,$type='preview'){
        if($type=='preview'){           //预览接口
            $url='https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token='.$token;
            $postJson =urldecode(json_encode($array));
            $res = httpPost($url, $postJson);
            return $res;
        }elseif ($type=='send'){        //群发接口
            $url='https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$token;
            $postJson =urldecode(json_encode($array));
            $res = httpPost($url, $postJson);
            return $res;
        }else {
            return '发送类型不合规！';
        }
    }

    //获取微信服务器IP
    function wxGetServerIp($token){
        if(!$_SESSION['wx_ip_list']){
            $url = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=".$token;
            $res = httpGet($url);
            $_SESSION['wx_ip_list'] =json_decode($res,true);
        }
        return $_SESSION['wx_ip_list'];
    }

    //发送微信模板消息
    function wxSendTemplateMsg($token,$touser,$template_id,$call_url,$data){
        $url      = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$token;
        $Meg      = array('touser'=>$touser,'template_id'=>$template_id,'url'=>$call_url,'data'=>$data);   //2.组装数组
        $postJson = json_encode($Meg);      //将数组转化成json
        $res      = httpPost($url, $postJson);
        return $res;
    }


}