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
            $_SESSION['user'] = $user;
            setCookieKey('user',jia_mi($user),7*24*3600);
            if ($_SESSION['url']) {
                $url = $_SESSION['url'];
            }elseif(getCookieKey('url')){
                $url=getCookieKey('url');
            }else {
                $url = '/' . C('PRODUCT') . '/Index/index/project/' . $_SESSION['project'];;
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
        $_SESSION = array();
        cookie(null,C('PRODUCT').'_');
        $this->success($username . ",再见!", U(C(PRODUCT) . '/Index/index'));

    }


}