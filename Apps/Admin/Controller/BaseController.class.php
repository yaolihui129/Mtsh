<?php
namespace Admin\Controller;
use Think\Controller;
class BaseController extends Controller
{
    public function _initialize()
    {
        //判定登录态
        $isLogin=cookie(C(PRODUCT).'_isLogin');
        $user=cookie(C(PRODUCT).'_user');
        if($isLogin==''||$user==''){
            $this->redirect(C(PRODUCT).'/Login/index');
        }
        if (ismobile()) {//设置默认默认主题为 Amaze
            C('DEFAULT_V_LAYER', 'Amaze');
        }
    }

    function _empty()
    {
        $this->display('index');
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

    public function insert()
    {
        $m = D(I('table'));
        if (IS_GET) {
            $_GET['adder'] = cookie(C(PRODUCT).'_user');
            $_GET['moder'] = cookie(C(PRODUCT).'_user');
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
            $_POST['adder'] = cookie(C(PRODUCT).'_user');
            $_POST['moder'] = cookie(C(PRODUCT).'_user');
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
    public function update()
    {
        if (IS_GET) {
            $_GET['moder'] = cookie(C(PRODUCT).'_user');
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
            $_POST['moder'] = cookie(C(PRODUCT).'_user');
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
    public function del()
    {
        $_POST['id'] = I('id');
        $_POST['moder'] =cookie(C(PRODUCT).'_user');
        $_POST['deleted'] = 1;
        if (D(I('table'))->save($_POST)) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }





}