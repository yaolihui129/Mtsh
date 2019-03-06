<?php
namespace Customer\Controller;
use Think\Controller;
class BaseController extends Controller
{

    function _empty()
    {
        $this->display('index');
    }

    function isLogin()
    {
        if (!isset($_SESSION['isLogin']) || $_SESSION['user'] == '') {
            $this->redirect('User/Login/index');
        }
    }
    


    public function order()
    {
        $num = 0;
        foreach ($_POST['sn'] as $id => $sn) {
            $num += D(I('table'))->save(array("id" => $id, "sn" => $sn));
        }
        if ($num) {
            $this->success("排序成功!");
        } else {
            $this->error("排序失败...");
        }
    }










}