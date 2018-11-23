<?php
namespace Xinda\Controller;
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

    function details($table,$id){
        $this->assign('arr',M($table)->find($id));
        self::clicknum($table, $id);
    }

    function clicknum($table,$id){
        if(!$_SESSION[$table][$id]){
            $_SESSION[$table][$id]=1;
            $db=D($table);
            $arr=$db->field('id,clicknum')->find($id);
            $data['clicknum']=$arr['clicknum']+1;
            $data['id']=$id;
            $db->save($data);
        }
    }

    //数据查询
    function dataChaxun($table,$savePath,$map,$maxPageNum=10,$p=1){
        $m=M($table);
        $map['prodid']=C(PRODID);
        $map['state']=5;
        $map['isDelete']=0;
        $_SESSION[$savePath.'Page']=$p;
        $data=$m->where($map)->order('sn desc,utime desc')->page($_SESSION[$savePath.'Page'],$maxPageNum)->select();
        $this->assign('data',$data);
        $count      = $m->where($map)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$maxPageNum);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
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