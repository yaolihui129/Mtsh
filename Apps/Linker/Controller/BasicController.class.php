<?php

namespace Linker\Controller;

use Think\Controller;

class BasicController extends Controller
{
    public function _initialize()
    {
        if(!$_SESSION['isLogin']== C(PRODUCT)){// 初始化的时候检查用户权限
            if($_SESSION['realname'] == ''){
                $this->redirect('Device/Login/index');
            }
        }
        if (ismobile()) {
            //设置默认默认主题为 Amaze
            C('DEFAULT_V_LAYER','Amaze');
        }
    }

    public function _empty()
    { //错误网页重新定向到首页
        $this->redirect('public/404');
    }

    function select($data, $name, $value)
    {
        $html = '<select name="' . $name . '" class="form-control">';
        foreach ($data as $v) {
            $selected = ($v['key'] == $value) ? "selected" : "";
            $html .= '<option ' . $selected . ' value="' . $v['key'] . '">' . $v['value'] . '</option>';
        }
        $html .= '<select>';
        return $html;
    }


    function insert()
    {
        $m = D(I('table'));
        if (IS_GET) {
            $_GET['adder'] = $_SESSION['account'];
            $_GET['moder'] = $_SESSION['account'];
            $_GET['ctime'] = time();
            if (!$m->create($_GET)) {
                $this->error($m->getError());
            }
            if ($m->add($_GET)) {
                if ($_GET['url']){
                    $this->success("成功",U($_GET['url']));
                }else{
                    $this->success("成功");
                }
            } else {
                $this->error("失败");
            }
        } else {
            $_POST['adder'] = $_SESSION['account'];
            $_POST['moder'] = $_SESSION['account'];
            $_POST['ctime'] = time();
            if (!$m->create()) {
                $this->error($m->getError());
            }
            if ($m->add()) {
                if ($_POST['url']){
                    $this->success("成功",U($_POST['url']));
                }else{
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
            $_GET['moder'] = $_SESSION['account'];
            if (D(I('table'))->save($_GET)) {
                if ($_GET['url']){
                    $this->success("成功",U($_GET['url']));
                }else{
                    $this->success("成功");
                }
            } else {
                $this->error("失败！");
            }
        } else {
            $_POST['moder'] = $_SESSION['account'];
            if (D(I('table'))->save($_POST)) {
                if ($_POST['url']){
                    $this->success("成功",U($_POST['url']));
                }else{
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
        $_POST[I('name')] = I('id');
        $_POST['deleted'] = 1;
        if (D(I('table'))->save($_POST)) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }

    //物理删除
    public function realdel()
    {
        $count = D(I('table'))->delete(I('id'));
        if ($count > 0) {
            $this->success('成功');
        } else {
            $this->error('失败');
        }
    }

    function projectList($where = array())
    {
        $where['projectName'] = array('neq', '示例接口');
        $data = M('project')->where($where)->select();
        return $data;
    }

    function projectID()
    {
        $project = $this->projectList();
        $pro = '';
        foreach ($project as $p) {
            $pro[] = $p['projectid'];
        }
        return $pro;
    }



}