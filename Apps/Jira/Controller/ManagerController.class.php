<?php

namespace Jira\Controller;
class ManagerController extends WebInfoController
{
    public function index()
    {
        $url = '/' . C(PRODUCT) . '/Manager/index';
        cookie('url',$url,array('prefix'=>C(PRODUCT).'_'));
        $this->isLogin();
        $search = I('search');
        $this->assign('search', $search);
        $type=I('type','1');
        $this->assign('$type', $type);
        $fenlei=I('fenlei','0');
        $this->assign('fenlei', $fenlei);
        $user=$project=cookie(C(PRODUCT).'_user');
        $user=jie_mi($user);
        if($fenlei=='0'){
            //全部
            $where = array('type' => $type, 'manager' => $user, 'deleted' => '0');
            $where['brand|ts|serial|asset_no'] = array('like', '%' . $search . '%');
            $data = M('tp_device')->where($where)->select();
            $this->assign('data', $data);
            $this->assign('riqi', date("Y-m-d", time()));


        }elseif ($fenlei=='1'){
            //已预定
            $m = M('tp_device_loaning_record');
            $riqi = date("Y-m-d", time());
            $this->assign('riqi', $riqi);
            $where = array('manager' => $user, 'start_time' => $riqi, 'type' => '1', 'deleted' => '0');
            $data = $m->where($where)->order('start_time,ctime')->select();
            $this->assign('data', $data);
            $where['deleted'] = '1';
            $data = $m->where($where)->order('start_time,ctime')->select();
            $this->assign('data1', $data);
        }else{
            //已借出
            $where = array('manager' => $user, 'type' => '0', 'deleted' => '0');
            $data = M('tp_device_loaning_record')->where($where)->order('end_time')->select();
            $this->assign('data', $data);
        }

        $this->display();
    }

    public function add()
    {
        $source = I('source', 'index');
        $this->assign('source', $source);
        $this->assign('type', I('type', '1'));

        $search = I('search');
        $this->assign('search', $search);

        $url = 'Device/Manager/' . $source . '?search=' . I('search');
        $this->assign('url', $url);

        $user=jie_mi(cookie(C(PRODUCT).'_user'));
        $this->assign('user', $user);

        $this->display();
    }

    public function mod()
    {
        $source = I('source', 'index');
        $this->assign('source', $source);

        $search = I('search');
        $this->assign('search', $search);

        $url = 'Jira/Manager/' . $source . '?search=' . I('search');

        $this->assign('url', $url);

        $arr = M('tp_device')->find(I('id'));
        $this->assign('arr', $arr);

        $where=array('deleted'=>'0');
        $user=M('tp_jira_user')->where($where)->order('username')->select();
        foreach ($user as $k => $v){
            $user[$k]['key'] = $v['username'];
            $user[$k]['value'] = '【'.countId('tp_device_overdue','borrower',$v['username']).'】'.$v['name'].'('.$v['username'].')';
        }
        //封装下拉列表
        $user = $this->select($user, 'manager',$_SESSION['user']);
        $this->assign('user', $user);

        $this->display();
    }

    public function img()
    {
        $source = I('source', 'index');
        $this->assign('source', $source);

        $search = I('search');
        $this->assign('search', $search);

        $url = 'Jira/Manager/' . $source . '?search=' . I('search');

        $this->assign('url', $url);

        $arr = M('tp_device')->find(I('id'));
        $this->assign('arr', $arr);
        $this->display();
    }

    function img_update()
    {
        $this->dataUpdate('tp_device', 'Device', $_POST);
    }

    public function books()
    {
        $search = I('search');
        $this->assign('search', $search);
        $where = array('type' => '3', 'manager' => $_SESSION['account'], 'deleted' => '0');
        $where['brand|ts|serial|asset_no'] = array('like', '%' . $search . '%');
        $data = M('tp_device')->where($where)->select();
        $this->assign('data', $data);
        $this->assign('riqi', date("Y-m-d", time()));

        $this->display();
    }

    //预订列表页面
    public function yuding()
    {
        $m = M('tp_device_loaning_record');
        $riqi = date("Y-m-d", time());
        $this->assign('riqi', $riqi);
        $device=I('device');
        if($device){
            $user=$project=cookie(C(PRODUCT).'_user');
            $user=jie_mi($user);
            $where = array('device'=>$device,'manager' => $user, 'start_time' => $riqi, 'type' => '1', 'deleted' => '0');
        }else{
            $where = array('manager' => $_SESSION['user'], 'start_time' => $riqi, 'type' => '1', 'deleted' => '0');
        }
        $data = $m->where($where)->order('start_time,ctime')->select();
        $this->assign('data', $data);
        $where['deleted'] = '1';
        $data = $m->where($where)->order('start_time,ctime')->select();
        $this->assign('data1', $data);

        $this->display();
    }

    //管理员驳回
    function reject(){
        $_GET['deleted']='1';
        $_GET['reject']='2';
        $_GET['table']='tp_device_loaning_record';
        $this->update();
        //发送驳回消息
        $this->msgBoHui($_GET['id']);
    }

    //设备OR图书借出
    function lend()
    {
        $m = D('tp_device_loaning_record');
        $arr = $m->find(I('id'));

        //0判断设备状态如果硬借出直接驳回
        $t = D('tp_device');
        $var = $t->where(array('id' => $arr['device'], 'loaning' => '1', 'deleted' => '0'))->find();
        if ($var) {
            $this->error("已借给" . getJiraName($var['borrower']) . '了，他归还了吗？');
        } else {
            //1.更新预约状态
            $_GET['type'] = '0';
            $time = strtotime($arr['start_time']);
            $week = date('w', $time);
            if($arr['leibie']=='1'){
                if ($week == 5) {//3天后的9:15
                    $_GET['end_time'] = date('Y-m-d H:i:s', $time + 3 * 24 * 60 * 60 + 9 * 60 * 60 + 15 * 60);
                } elseif ($week == 6) {//+2天后的9:15
                    $_GET['end_time'] = date('Y-m-d H:i:s', $time + 2 * 24 * 60 * 60 + 9 * 60 * 60 + 15 * 60);
                } else {//+1天后的9:15
                    $_GET['end_time'] = date('Y-m-d H:i:s', $time + 24 * 60 * 60 + 9 * 60 * 60 + 15 * 60);
                }
            }elseif ($arr['leibie']=='3'){
                if ($week == 5) {//10天后的9:15
                    $_GET['end_time'] = date('Y-m-d H:i:s', $time + 17 * 24 * 60 * 60 + 9 * 60 * 60 + 15 * 60);
                } elseif ($week == 6) {//+9天后的9:15
                    $_GET['end_time'] = date('Y-m-d H:i:s', $time + 16 * 24 * 60 * 60 + 9 * 60 * 60 + 15 * 60);
                } else {//+8天后的9:15
                    $_GET['end_time'] = date('Y-m-d H:i:s', $time + 15 *24 * 60 * 60 + 9 * 60 * 60 + 15 * 60);
                }
            }

            $_GET['moder'] = $_SESSION['user'];
            if ($m->save($_GET)) {
                //2.更新设备信息
                $_POST['id'] = $arr['device'];
                $_POST['loaning'] = '1';
                $_POST['borrower'] = $arr['borrower'];
                if (D('tp_device')->save($_POST)) {
                    //发送企业微信消息$borrower,$manager,$device,$end_time,$remark
                    $this->msgJieChu($arr['borrower'],$arr['manager'],$arr['device'],$_GET['end_time'],$arr['remark']);
                    $this->success("成功");
                } else {
                    // 回滚数据
                    $_GET['id'] = $arr['id'];
                    $_GET['type'] = '1';
                    $_GET['end_time'] = $arr['end_time'];
                    $m->save($_GET);
                    $this->error("失败！");
                }
            }
        }
    }

    //借出设备&图书待归还列表
    public function guihuan()
    {
        $user=$project=cookie(C(PRODUCT).'_user');
        $user=jie_mi($user);
        $where = array('manager' => $user, 'type' => '0', 'deleted' => '0');
        $data = M('tp_device_loaning_record')->where($where)->order('end_time')->select();
        $this->assign('data', $data);

        $this->display();
    }


    //管理员收回
    function hui_shou(){
        $_GET['loaning']='0';
        if (D('tp_device')->save($_GET)) {
            $m = D('tp_device_loaning_record');
            $where=array('device'=>I('id'),'type'=>'0','deleted'=>'0');
            $arr=$m->where($where)->find();
            $_POST['id']=$arr['id'];
            $_POST['type']='2';
            $_POST['end_time']=date('Y-m-d H:i:s', time());
            if ($m->save($_POST)){
                //发送企业微信消息$borrow,$manager,$device
                $this->msgGuiHuan($arr['borrower'],$arr['manager'],$arr['device']);
                $this->success("成功");
            }else{
                $this->error("失败！");
            }
        }else{
            $this->error("失败！");
        }
    }
    //管理员借出管理页面
    function loan(){
        $device=I('device');
        $source=I('source','index');
        $search=I('search');
        $m = D('tp_device_loaning_record');
        //今天的预订
        $where=array('device'=>$device,'type'=>'1','start_time'=>date('Y-m-d',time()),'deleted'=>'0');
        $arr = $m->where($where)->find();
        if($arr){//如果有预订，跳转至预订页面
            $this->redirect('Jira/Manager/yuding?device='.$device);
        }else{//如果没有预订，跳转至借出页面
            if($search){
                $url='Jira/Borrow/index/id/'.$device.'/source/'.$source.'/search/'.$search;
            }else{
                $url='Jira/Borrow/index/id/'.$device.'/source/'.$source;
            }
            $this->redirect($url);
        }

    }


}