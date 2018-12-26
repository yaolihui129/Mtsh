<?php
namespace Jira\Controller;
class ManagerController extends WebInfoController
{
    public function index()
    {
        $_SESSION['url'] = '/' . C('PRODUCT') . '/Manager/index';
        $this->isLogin();
        $search = I('search');
        $this->assign('search', $search);
        $where = array('type' => '1', 'manager' => getLoginUser(), 'deleted' => '0');
        $where['brand|ts|serial|asset_no'] = array('like', '%' . $search . '%');
        $this->assign('data',  getList('tp_device',$where));
        $this->assign('riqi', date("Y-m-d", time()));

        $this->display();
    }

    public function add()
    {
        $source = I('source', 'index');
        $this->assign('source', $source);
        $this->assign('type', I('type', '1'));
        $this->assign('search', I('search'));

        $url = 'Device/Manager/' . $source . '?search=' . I('search');

        $this->assign('url', $url);
        $this->display();
    }

    public function mod()
    {
        $source = I('source', 'index');
        $this->assign('source', $source);
        $this->assign('search', I('search'));
        $this->assign('arr', find('tp_device',I('id')));

        $url = 'Jira/Manager/' . $source . '?search=' . I('search');
        $this->assign('url', $url);

        $user = getList('tp_jira_user',array(),'username');
        foreach ($user as $k => $v){
            $user[$k]['key'] = $v['username'];
            $user[$k]['value'] = '【'.countWithParent('tp_device_overdue','borrower',$v['username'])
                .'】'.$v['name'].'('.$v['username'].')';
        }
        //封装下拉列表
        $this->assign('user', select($user, 'manager',$_SESSION['user']));

        $this->display();
    }

    public function img()
    {
        $source = I('source', 'index');
        $this->assign('source', $source);
        $this->assign('search', I('search'));
        $this->assign('arr', find('tp_device',I('id')));

        $url = 'Jira/Manager/' . $source . '?search=' . I('search');
        $this->assign('url', $url);

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
        $where = array('type' => '3', 'manager' => getLoginUser());
        $where['brand|ts|serial|asset_no'] = array('like', '%' . $search . '%');
        $this->assign('data', getList('tp_device',$where));
        $this->assign('riqi', date("Y-m-d", time()));

        $this->display();
    }

    //预订列表页面
    public function yuding()
    {
        $table='tp_device_loaning_record';
        $riqi = date("Y-m-d", time());
        $this->assign('riqi', $riqi);
        $device=I('device');
        $where = array('manager' =>  getLoginUser(), 'start_time' => $riqi, 'type' => '1');
        if($device){
            $where['device'] = $device;
        }
        $this->assign('data', getList($table,$where,'start_time,ctime'));
        $where['deleted'] = '1';
        $this->assign('data1', getList($table,$where,'start_time,ctime'));

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
        $table='tp_device_loaning_record';
        $arr= find($table,I('id'));
        //0判断设备状态如果硬借出直接驳回
        $var = findOne('tp_device',array('id' => $arr['device'], 'loaning' => '1'));
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
            $_GET['moder'] =getLoginUser();

            if (D($table)->save($_GET)) {
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
                    D($table)->save($_GET);
                    $this->error("失败！");
                }
            }
        }
    }

    //借出设备&图书待归还列表
    public function guihuan()
    {
        $table='tp_device_loaning_record';
        $where = array('manager' => getLoginUser(), 'type' => '0');
        $this->assign('data', getList($table,$where,'end_time'));

        $this->display();
    }

    //管理员收回
    function hui_shou(){
        $_GET['loaning']='0';
        if (D('tp_device')->save($_GET)) {
            $table='tp_device_loaning_record';
            $where=array('device'=>I('id'),'type'=>'0');
            $arr = findOne($table,$where);
            $_POST['id']=$arr['id'];
            $_POST['type']='2';
            $_POST['end_time']=date('Y-m-d H:i:s', time());
            if (D($table)->save($_POST)){
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
        //今天的预订
        $where=array('device'=>$device,'type'=>'1','start_time'=>date('Y-m-d',time()));
        $arr = findOne('tp_device_loaning_record',$where);
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