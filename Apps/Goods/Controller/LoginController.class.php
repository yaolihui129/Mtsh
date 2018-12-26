<?php
namespace Goods\Controller;
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


    }

    public function logout()
    {


    }


}