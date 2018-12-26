<?php
namespace Admin\Controller;
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
        $time=2*7*24*3600;
        $where=array('username'=>$user,'password'=>md5($password),'status'=>'1');
        $data=findOne('user',$where);
        if ($data) {
            setCookieKey('user',jia_mi($user),$time);
            setCookieKey('user_id',jia_mi($data['id']),$time);
            setCookieKey('isLogin',C('PRODUCT'),$time);
            $url=getCookieKey('url');
            if(!$url){
                $url = '/' . C('PRODUCT') . '/Index/index';
            }
            $this->redirect($url);
        } else {

            $this->error('用户或密码错误，请重新登陆！', "index");
        }

    }

    public function logout()
    {
        $username=getLoginUserID();
        $username = getName('user',$username,'real_name');
        clearCookie();
        $this->success($username . ",再见!", U(C('PRODUCT') . '/Login/index'));

    }


}