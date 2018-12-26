<?php
namespace Jira\Controller;
use Think\Controller;
class BaseController extends Controller
{
    /**
     * 发送企业微信消息
     */
    function sendMessage($who,$content){
        /**
         * 消息样例
         * {
         *     "type": 1,
         *     "data": {
         *           "msgtype": "text",
         *           "touser": "yyj",
         *           "usertype": 2,
         *           "text": {
         *                 "content": "Test TextMessage2, Test TextMessage1"
         *            }
         *     }
         *  }
         */
        $str='';
        foreach ($who as $w){
            $str.= $w.'||';
        }
        $data=array(
            "type"=>1,
            "data"=>array(
                'msgtype' => 'text',
                'touser'  => $str,
                'usertype'=> 2,
                'text'=>array(
                    'content'=> $content
                )
            )
        );
        $data=json_encode($data);
        $url = "http://open.yxyongche.cn/jcfw/message/send/corpwechat";
        $msg = httpJsonPost($url, $data);
        return $msg;
    }
}