<?php
namespace Jira\Controller;
class LoginController extends WebInfoController
{
    public function index()
    {
        layout(false); // 临时关闭当前模板的布局功能
        $this->display();
    }

    public function login()
    {
        $user = I('username');
        $password = I('password');
        $time=7*24*3600;
        S($user, $password);
        $arr = $this->jiraLogin($user,$password);
        if ($arr['session']) {
            setCookieKey('user',jia_mi($user),$time);
            setCookieKey('isLogin',C(PRODUCT),$time);
            $url=cookie(C(PRODUCT).'_url');
            $url=getCookieKey('url');
            if (!$url) {
                $url = '/' . C(PRODUCT) . '/Index/index';
            }
            $this->redirect($url);
        } else {
            $this->error('用户或密码错误，请重新登陆！', "index");
        }

    }

    public function logout()
    {
        $username=getLoginUser();
        $username=getJiraName($username);
        cookie(null,C(PRODUCT).'_'); //  清空指定前缀的所有cookie值
        $this->success($username . ",再见!", U(C(PRODUCT) . '/Login'));

    }


}