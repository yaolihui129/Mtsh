<?php
namespace Jinruihs\Controller;
class BaseController extends WechatController
{
    public function _initialize()
    {
        if (ismobile()) {//设置默认默认主题为 Amaze
            C('DEFAULT_V_LAYER', 'Amaze');
        }
    }

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



    function order()
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

    function insert()
    {
        $m = D(I('table'));
        if (IS_GET) {
            $_GET['adder'] = $_SESSION['user'];
            $_GET['moder'] = $_SESSION['user'];
            $_GET['ctime'] = time();
            if (!$m->create($_GET)) {
                $this->error($m->getError());
            }
            if ($m->add($_GET)) {
                if ($_GET['url']) {
                    $this->success("成功", U($_GET['url']));
                } else {
                    $this->success("成功");
                }
            } else {
                $this->error("失败");
            }
        } else {
            $_POST['adder'] = $_SESSION['user'];
            $_POST['moder'] = $_SESSION['user'];
            $_POST['ctime'] = time();
            if (!$m->create()) {
                $this->error($m->getError());
            }
            if ($m->add()) {
                if ($_POST['url']) {
                    $this->success("成功", U($_POST['url']));
                } else {
                    $this->success("成功");
                }
            } else {
                $this->error("失败");
            }
        }
    }



    //修改
    function update()
    {
        if (IS_GET) {
            $_GET['moder'] = $_SESSION['user'];
            if (D(I('table'))->save($_GET)) {
                if ($_GET['url']) {
                    $this->success("成功", U($_GET['url']));
                } else {
                    $this->success("成功");
                }
            } else {
                $this->error("失败！");
            }
        } else {
            $_POST['moder'] = $_SESSION['user'];
            if (D(I('table'))->save($_POST)) {
                if ($_POST['url']) {
                    $this->success("成功", U($_POST['url']));
                } else {
                    $this->success("成功");
                }
            } else {
                $this->error("失败！");
            }
        }
    }



    //逻辑删除
    function del($msg='成功')
    {
        $_POST[id] = I('id');
        $_POST['moder'] = $_SESSION['user'];
        $_POST['deleted'] = 1;
        if (D(I('table'))->save($_POST)) {
            $this->success($msg);
        } else {
            $this->error("失败！");
        }
    }




}