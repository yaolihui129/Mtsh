<?php
namespace Jinruihs\Controller;
use Think\Controller;
class LoginController extends Controller
{
    public function _initialize()
    {
        if (ismobile()) {//设置默认默认主题为 Amaze
            C('DEFAULT_V_LAYER', 'Amaze');
        }
    }

    public function index()
    {
        layout(false); // 临时关闭当前模板的布局功能
        $this->display();
    }

    public function login()
    {
        $user = I('username');
        $password = I('password');
        S($user, $password);
        $url = C(JIRAURL) . '/rest/auth/1/session';
        $json = json_encode(array('username' => $user, 'password' => $password));
        $json = httpJsonPost($url, $json);
        $arr = json_decode($json, true);
        if ($arr['session']) {
            $_SESSION['user'] = $user;
            $_SESSION['isLogin'] = C(PRODUCT);
            if ($_SESSION['url']) {
                $url = $_SESSION['url'];
            } else {
                $url = '/' . C(PRODUCT) . '/Index/index/project/' . $_SESSION['project'];;
            }
            $this->redirect($url);
        } else {

            $this->error('用户或密码错误，请重新登陆！', "index");
        }

    }

    public function logout()
    {
        $username = $_SESSION['user'];
        $_SESSION = array();
        session_destroy();
        $this->success($username . ",再见!", U(C(PRODUCT) . '/Index/index'));

    }


}