<?php
namespace Jira\Controller;
class LoginController extends BaseController
{
    public function index()
    {
        layout(false);
        $this->display();
    }

    public function login()
    {
        $user = I('username');
        $arr = doJiraLogin($user,I('password'));
        if ($arr['session']) {
            setCache('user',jia_mi($user));
            $url=getCache('url');
            if (!$url) {
                $url = '/' . C('PRODUCT') . '/Index/index';
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
        clearSession();
        $_SESSION=array();
        clearCookie();
        $this->success($username . ",再见!", U(C(PRODUCT) . '/Index/index'));

    }


}