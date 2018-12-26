<?php
namespace Jinruihs\Controller;
use Think\Controller;
class WechatController extends Controller{
    public function msg(){//验证消息接口
//        $echoStr = $_GET["echostr"];
//        if($this->checkSignature()){
//            header("content-type:text;charset=utf-8");
//            ob_clean();
//            echo $echoStr;
//            exit;
//        }else {
//            $this->reponseMsg();
//        }
        $this->reponseMsg();
    }
    private function checkSignature()
    {
        $signature = $_GET["signature"];//从用户端获取签名赋予变量signature
        $timestamp = $_GET["timestamp"];//从用户端获取时间戳赋予变量timestamp
        $nonce = $_GET["nonce"];	//从用户端获取随机数赋予变量nonce
        $token = C('PRODUCT');//将常量token赋予变量token
        $tmpArr = array($token, $timestamp, $nonce);//简历数组变量tmpArr
        sort($tmpArr, SORT_STRING);//新建排序
        $tmpStr = implode( $tmpArr );//字典排序
        $tmpStr = sha1( $tmpStr );//shal加密
        //tmpStr与signature值相同，返回真，否则返回假
        $data['name']=json_encode($_GET);
        $data['web']=C('PRODUCT');
        $data['signature']=$tmpStr;
        insert('token',$data);
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    //接收事件推送并回复
    public function reponseMsg()
    {
        $postArr        = $GLOBALS['HTTP_RAW_POST_DATA'];
        $postObj        = simplexml_load_string($postArr);
        $msgType        = strtolower($postObj->MsgType);
        $event          = strtolower($postObj->Event);
        //事件推送
        if($msgType== 'event') {
            if ($event == 'subscribe') {
                //如果是关注subscrine事件
                $this->wxEventSubscribe($postObj);
            }elseif ($event == 'click') {
                //自定义菜单“推”事件
                $this->wxEventClick($postObj);
            }elseif($event == 'location'){
                //上传地理位置
                $this->wxEventLocation($postObj);
            }elseif ($event == 'link'){
                //上传链接
                $this->wxEventLink($postObj);
            }elseif($event == 'scan'){
                //扫描二维码
                $this->wxEventScan($postObj);
            }
//            else{
//                $toUser         = $postObj->FromUserName;
//                $fromUser       = $postObj->ToUserName;
//                $content   = '暂不支持该事件';
//                $this->wxReplyText($toUser,$fromUser,$content);
//            }
        }
        //语音回复
        if($msgType== 'voice'){
            $this->wxVoice($postObj);
        }
        //关键字回复
        if($msgType== 'text'){
            $this->wxText($postObj);
        }
        //图片信息回复
        if($msgType== 'image'){
            $this->wxImage($postObj);
        }
        //视频信息回复
        if($msgType== 'video'){
            $this->wxVideo($postObj);
        }
    }

    function getAccessToken() {
        $appID=C('appID');
        $appsecret=C('appsecret');
        $access_token= S($appID.'access_token');
        if (!$access_token) {
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
            "appId"     => C('appID'),
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
        $appID=C('appID');
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
        $appid = C('appID');
        $redirect_uri = urlencode(C('WEBSITE').'/'.C('PRODUCT').'/Wechat/getUserOpenId');
        $url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid
              .'&redirect_uri='.$redirect_uri.'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
        header('Location:'.$url);
    }

    function getUserOpenId(){
        $appid     = C('appID');
        $appsecret = C('appsecret');
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
        $where=array('app_id'=>$appid,'openid'=>$openid);
        $data=findOne('customer_third_party',$where);
        if(!$data){
            //插入数据
            if($openid){
              $data['app_id']=$appid;
              $data['merchant_id']=C('MERCHANTID');
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
      $userinfo['id']= cookie(C('appID').'_isLogin');
      $userinfo['source']= $state;
      $userinfo['flag']= '0';
      update('customer_third_party',$userinfo);
    }


    //换取微信短链接
    function getShortUrl($long_url){
        $url='https://api.weixin.qq.com/cgi-bin/shorturl?access_token='.$this->getAccessToken();
        $array=array('action'=>'long2short','long_url'=>$long_url );
        $postJson = json_encode($array);
        $res = httpPost($url, $postJson);
        $res = json_decode($res,true);
        return $res['short_url'];
    }
    //获取临时二维码
    function getTimeQrCode($scene_id,$expire=30){
        $url='https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->getAccessToken();
        $expire = $expire*24*60*60;
        $postArr =array(
            'expire_seconds'=>$expire,
            'action_name'=>"QR_SCENE",
            'action_info'=>array('scene'=>array('scene_id'=>$scene_id) )
        );
        $postJson = json_encode($postArr);
        $res = httpPost($url, $postJson);
        $res = json_decode($res,true);
        $long_url='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$res['ticket'];
        return $this->getShortUrl($long_url);
    }
    //获取永久二维码
    function getForeverQrCode($scene_id){
        $url='https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->getAccessToken();
        $postArr =array(
            'action_name'=>"QR_LIMIT_SCENE",
            'action_info'=>array('scene'=>array('scene_id'=>$scene_id))
        );
        $postJson = json_encode($postArr);
        $res = httpPost($url, $postJson);
        $res = json_decode($res,true);
        $long_url='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$res['ticket'];
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
    //微信推消息事件
    function wxEventClick($postObj){
        $toUser         = $postObj->FromUserName;
        $fromUser       = $postObj->ToUserName;
        $eventKey       = strtolower($postObj->EventKey);
        if($eventKey == 'item1'){
            $content   = '公司介绍';
            $this->wxReplyText($toUser,$fromUser,$content);
        }
        if($eventKey == 'item2'){
            $content   = '联想';
            $this->wxReplyText($toUser,$fromUser,$content);
        }
        if($eventKey == 'item3'){
            $content   = '因特尔'.$fromUser;
            $this->wxReplyText($toUser,$fromUser,$content);
        }
    }
    //微信关注事件
    function wxEventSubscribe($postObj){
        $toUser         = $postObj->FromUserName;
        $fromUser       = $postObj->ToUserName;
        //推送图文消息，并发送优惠券
    }
    //微信接收地理位置
    function wxEventLocation($postObj){
        $toUser         = $postObj->FromUserName;
        $fromUser       = $postObj->ToUserName;
    }
    //微信接收链接
    function wxEventLink($postObj){
        $toUser         = $postObj->FromUserName;
        $fromUser       = $postObj->ToUserName;
        $Title   = $postObj->Title;
        $Url     = $postObj->Url;
        $content = "<a href='". $Url."'>".$Title."</a>";
        //回复用户消息(纯文本格式)
        $this->wxReplyText($toUser,$fromUser,$content);
    }
    //微信扫码事件
    function wxEventScan($postObj){
        $toUser         = $postObj->FromUserName;
        $fromUser       = $postObj->ToUserName;
        $eventKey       = strtolower($postObj->EventKey);
        $scene=find('wechat_scene',$eventKey);
        if($scene){
            $activity=find('marketing_activity',$scene['activityid']);
            $arr=array(
                array(
                    'title'         => $activity['desc'],
                    'description'   => $activity['title'],
                    'picUrl'        => C('WEBSITE').'/Upload'.$activity['img'],
                    'url'           => $scene['url'],
                ),
            );
            $this->wxReplyNews($toUser,$fromUser,$arr);
        }else{
            //外部二维码
            $content   = '外部二维码';
            $this->wxReplyText($toUser,$fromUser,$content);
        }
    }
    //微信语音事件
    function wxVoice($postObj){
        $toUser         = $postObj->FromUserName;
        $fromUser       = $postObj->ToUserName;
        //截取最后的“。”
        $text=rtrim($postObj->Recognition,'。');
        //回复用户消息(纯文本格式)
        $content   = "您说的是：“".$text."”MediaId:".$postObj->MediaId;
        $this->wxReplyText($toUser,$fromUser,$content);
        //回复用户语音消息（语音）
        $mediaId = $postObj->MediaId;
        $this->wxReplyVoice($toUser,$fromUser,$mediaId);
    }
    //微信文本关键字事件
    function wxText($postObj){
        $toUser         = $postObj->FromUserName;
        $fromUser       = $postObj->ToUserName;
        $content   = trim($postObj->Content);
        $this->wxReplyText($toUser,$fromUser,$content);
    }
    //微信上传图片事件
    function wxImage($postObj){
        $toUser         = $postObj->FromUserName;
        $fromUser       = $postObj->ToUserName;
        $arr = array(
            array(
                'title'         => '图片上传成功',
                'description'   => "MediaId:".$postObj->MediaId,
                'picUrl'        => $postObj->PicUrl,
                'url'           => C('WEBSERVER'),
            ),
        );
        $this->wxReplyNews($toUser,$fromUser,$arr);
    }
    //微信上传视频事件
    function wxVideo($postObj){
        $toUser         = $postObj->FromUserName;
        $fromUser       = $postObj->ToUserName;
        $arr = array(
            array(
                'title'=>'视频上传成功',
                'description'=>"MediaId:".$postObj->MediaId,
                'picUrl'=>$postObj->ThumbMediaId,
                'url'=>C('WEBSERVER'),
            ),
        );
        $this->wxReplyNews($toUser,$fromUser,$arr);
    }

    function wxNewsArr($size){
        $str=array();
        for ($x=0; $x<=$size; $x++) {
            $str.="array(
            'title'        =>  ".'$data['.$x."]['name'],
            'description'  =>  ".'$data['.$x."]['content'],
            'picUrl'       =>  C(WEBSITE).'/Upload/'.".'$data['.$x."]['productimg'],
            'url'          =>  C(WEBSITE).'/index.php/'.C(PRODUCT).'/Service/index/id/'.".'$data['.$x."]['productid']".".'/wxOpenId/'".'.$toUser.'."'/wxAppId/'".'.$fromUser,'."
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
    function wxSendTemplateMsg($touser,$template_id,$call_url,$data){
        $url      = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$this->getAccessToken();
        $Meg      = array('touser'=>$touser,'template_id'=>$template_id,'url'=>$call_url,'data'=>$data);
        $postJson = json_encode($Meg);
        $res      = httpPost($url, $postJson);
        return $res;
    }


}