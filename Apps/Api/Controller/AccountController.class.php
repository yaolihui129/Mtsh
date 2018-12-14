<?php
/*
 * Api：用户中心接口
 *
 */
namespace Api\Controller;
class AccountController extends BasicController
{
    function index()
    {
        S('1k1GNBCLty1bwccFM4YzTow49nMCiUua1rCwPPIWj3m0KKne18u8CyEhfoJ3m8IQ', 18801043607, 300);
        $s_code = S('1k1GNBCLty1bwccFM4YzTow49nMCiUua1rCwPPIWj3m0KKne18u8CyEhfoJ3m8IQ');
        dump($s_code);

    }

    //获取用户信息
    function queryById()
    {
        $result = M('uc_tp_user')->find(I('id'));
        if ($result) {
            $data = array(
                'returncode' => 0,
                'message' => 'ok',
                'result' => $result,
            );
        } else {
            $data = array(
                'returncode' => 123,
                'message' => 'ERROR',
            );
        }
        $this->ajaxReturn($data);
    }

    //通过第三方用户id(车机用户id)获取用户信息
    function queryByThirdUserId()
    {
        $where = array('cheji_user_id' => I('id'));
        $result = M('uc_tp_user')->where($where)->find();
        if ($result) {//成功
            $data = array(
                'returncode' => 0,
                'message' => 'ok',
                'result' => $result,
            );
        } else {//失败
            $data = array(
                'returncode' => 123,
                'message' => 'ERROR',
            );
        }
        $this->ajaxReturn($data);
    }

    //通过手机号查询用户信息
    function queryByPhone()
    {
        $where = array('mobile' => I('phone'));
        $result = M('uc_tp_user')->where($where)->find();
        if ($result) {//成功
            $data = array(
                'returncode' => 0,
                'message' => 'ok',
                'result' => $result,
            );
        } else {//失败
            $data = array(
                'returncode' => 123,
                'message' => 'ERROR',
            );
        }
        $this->ajaxReturn($data);
    }

    //通过token获取用户信息
    function queryByToken()
    {
        $where = array('token' => I('token'));

        //todo

        $result = M('uc_tp_user')->find();
        if ($result) {//成功
            $data = array(
                'returncode' => 0,
                'message' => 'ok',
                'result' => $result,
            );
        } else {//失败
            $data = array(
                'returncode' => 123,
                'message' => 'ERROR',
            );
        }
        $this->ajaxReturn($data);
    }


    //发送手机验证码
    function send()
    {
        $json = file_get_contents('php://input');
        $array = json_decode($json, true);

        if ($array['code'] == '1234') {//图形验证码暂时写死为1234
            $value = rand(1000, 9999);
            S($array['phone'], $value, 600);
            $data = array(
                'returncode' => 0,
                'message' => 'ok',
            );
        } else {
            $data = array(
                'returncode' => 123,
                'message' => 'ERROR',
            );
        }
        $this->ajaxReturn($data);
    }

    function chaxun()
    {
        $phone = I('phone');
        $result = array(
            'code' => S($phone)
        );
        if ($result) {//成功
            $data = array(
                'returncode' => 0,
                'message' => 'ok',
                'result' => $result,
            );
        } else {//失败
            $data = array(
                'returncode' => 123,
                'message' => 'ERROR',
            );
        }
        $this->ajaxReturn($data);
    }

    function doLogin()
    {
        $json = file_get_contents('php://input');
        $array = json_decode($json, true);
        $phone = $array['phone'];
        $s_code = S($phone);
        if ($array['code'] == $s_code) {
            S($phone . 'token', getRandCode(64), 7200);
            $token = S($phone . 'token');
            //写入待查token
            S($token, $phone, 7200);
            $data = array(
                'returncode' => 0,
                'message' => 'ok',
                'data' => array(
                    'token' => $token,
                    'expire_time' => 7200,
                ),
            );
        } else {
            $data = array(
                'returncode' => 123,
                'message' => 'ERROR',
            );
        }
        $this->ajaxReturn($data);
    }


}