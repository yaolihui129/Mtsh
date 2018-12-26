<?php
namespace Back\Controller;
use Think\Controller;
class WechatController extends Controller{
    //获取微信Secret
    function getAppSecret($appID){
        $where=array('appid'=>$appID);
        $arr=findOne('admin_app',$where);
        return $arr['appsecret'];
     }
    //获取企业微信corpID
    function getCorpID($appsecret){
        $where=array('appsecret'=>$appsecret);
        $arr=findOne('admin_app',$where);
        return $arr['appid'];
    }
    //获取微信AccessToken
    function getAccessToken($appID) {
        $appsecret=$this->getAppSecret($appID);
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
    //获取企业微信AccessToken
    function getQyAccessToken($appsecret){
        $corpID=$this->getCorpID($appsecret);
        $access_token= S($appsecret.'access_token');
        if($access_token){
            $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$corpID&corpsecret=$appsecret";
            $res = json_decode(httpGet($url));
            $access_token = $res->access_token;
            if($access_token){
                S($appsecret.'access_token',$access_token,7000);
            }
        }
        return $access_token;
    }
    //换取微信短链接
    function getShortUrl($long_url,$appId){
        $url='https://api.weixin.qq.com/cgi-bin/shorturl?access_token='.$this->getAccessToken($appId);
        $array=array(
            'action'=>'long2short',
            'long_url'=>$long_url
        );
        $res = httpPost($url, json_encode($array));
        $res = json_decode($res);
        return $res->short_url;
    }
    /**
     * 首先创建二维码ticket
     * @param string $sceneid 场景值ID
     * $type 值为'temp'的时候生成临时二维码
     * $expire_seconds 二维码过期时间
     * @return string 二维码ticket
     */
    public function getTicket($sceneid,$appId,$type='temp',$expire_seconds=604800){
        if($type=='temp'){
            $data = '{"expire_seconds": %s, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": %s}}}';
            $data = sprintf($data,$expire_seconds,$sceneid);
        }else{
            $data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": %s}}}';
            $data = sprintf($data,$sceneid);
        }
        $curl = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->getAccessToken($appId);;
        $content =httpPost($curl, $data);
        $cont = json_decode($content);
        return $cont->ticket;
    }
    //获取临时二维码
    function getTimeQrCode($scene_id,$appId,$expire=30){
        $ticket=$this->getTicket($scene_id,$appId,'temp',$expire);
        $long_url='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticket); //使用$ticket换去二维码图片
        $shortUrl=$this->getShortUrl($long_url,$appId);
        return $shortUrl;
    }
    //获取永久二维码
    function getForeverQrCode($scene_id,$appId){
        $ticket=$this->getTicket($scene_id,$appId);
        $long_url='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
        $shortUrl=$this->getShortUrl($long_url,$appId);
        return $shortUrl;
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

    //拉取用户信息（认证后才可用）
    function getWechatUsers($appID,$nextOpenid){
        $url  = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$this->getAccessToken($appID).'&next_openid='.$nextOpenid;
        $res  = httpGet($url);
        $arr  = json_decode($res,true);
        return $arr;
    }
    //获取用户详情
    function getWechatUserInfo($appID,$openid){
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getAccessToken($appID).'&openid='.$openid;
        $res  = httpGet($url);
        $res  = json_decode($res,true);
        return $res;
    }
    //拉取用户分组
    function getWechatUserGroupList($appID){
        $url = 'https://api.weixin.qq.com/cgi-bin/groups/get?access_token='.$this->getAccessToken($appID);
        $res  = httpGet($url);
        $res  = json_decode($res,true);
        return $res;
    }


}