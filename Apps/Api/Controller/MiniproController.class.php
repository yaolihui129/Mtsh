<?php
/*
 * Api：微信小程序
 *
 */

namespace Api\Controller;
class MiniproController extends BasicController
{
    function index()
    {

    }

    // 登录地址，用于建立会话
    function login()
    {
        $appid = trim('wxe6f24ce3395cb972');
        $secret = trim('1eb6356e949c6ce165e2cf7ff085e782');
        $code = trim(I('code'));
        $url = trim('https://api.weixin.qq.com/sns/jscode2session?appid=' . $appid . '&secret=' . $secret . '&js_code=' . $code . '&grant_type=authorization_code');
        $arr = json_decode(httpGet($url), true);
        $arr = arrDate($arr);
        $this->ajaxReturn($arr);
    }

    // 测试的请求地址，用于测试会话
    function testRequest()
    {
        $this->ajaxReturn();
    }

    // 用code换取openId
    function openid()
    {
        $this->ajaxReturn();
    }

    // 测试的信道服务接口
    function tunnel()
    {
        $this->ajaxReturn();
    }

    // 生成支付订单的接口
    function payment()
    {
        $this->ajaxReturn();
    }

    // 发送模板消息接口
    function templateMessage()
    {
        $this->ajaxReturn();
    }

    // 上传文件接口
    function upload()
    {
        $this->ajaxReturn();
    }


}