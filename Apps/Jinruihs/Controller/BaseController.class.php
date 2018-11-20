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




    function insert()
    {
        $m = D(I('table'));
        if (IS_GET) {
            $_GET['adder'] = cookie(C(appID).'_isLogin');
            $_GET['moder'] = cookie(C(appID).'_isLogin');
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
            $_POST['adder'] = cookie(C(appID).'_isLogin');
            $_POST['moder'] = cookie(C(appID).'_isLogin');
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
            $_GET['moder'] = cookie(C(appID).'_isLogin');
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
            $_POST['moder'] = cookie(C(appID).'_isLogin');
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
        $_POST['id'] = I('id');
        $_POST['moder'] = cookie(C(appID).'_isLogin');
        $_POST['deleted'] = 1;
        if (D(I('table'))->save($_POST)) {
            $this->success($msg);
        } else {
            $this->error("失败！");
        }
    }






}