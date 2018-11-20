<?php
namespace Back\Controller;
use Think\Controller;
class LoginController extends Controller
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
        $time=2*7*24*3600;
        $where=array('username'=>$user,'password'=>md5($password),'status'=>'1','deleted'=>'0');
        $data=M('admin_user')->where($where)->find();
        if ($data) {
            cookie('user',$data['id'],array('expire'=>$time,'prefix'=>C(PRODUCT).'_'));
            cookie('isLogin',1,array('expire'=>$time,'prefix'=>C(PRODUCT).'_'));
            $url=cookie(C(PRODUCT).'_url');
            if(!$url){
                $url = '/' . C(PRODUCT) . '/Index/index';
            }
            $this->redirect($url);
        } else {

            $this->error('用户或密码错误，请重新登陆！', "index");
        }

    }

    public function logout()
    {
        $username = cookie(C(PRODUCT).'_user');
        $username = getName('admin_user',$username,'real_name');
        $_COOKIE[C(PRODUCT)] = array();
        session_destroy();
        $this->success($username . ",再见!", U(C(PRODUCT) . '/Login/index'));

    }


}