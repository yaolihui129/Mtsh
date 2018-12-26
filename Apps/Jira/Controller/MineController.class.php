<?php
namespace Jira\Controller;
class MineController extends WebInfoController
{
    public function index()
    {
        $table='tp_device_loaning_record';
        //预约中
        $where=array('borrower'=>getLoginUser(),'type'=>'1');
        $riqi = date("Y-m-d", time());
        $where['start_time']=array('egt',$riqi);
        $this->assign('data1', getList($table,$where,'start_time desc'));
        //借用历史
        $where=array();
        $where['start_time']=array('lt',$riqi);
        $where['type']='2';
        $this->assign('data2', getList($table,$where,'start_time desc'));
        //借用中
        $where=array('borrower'=>getLoginUser(),'type'=>'0');
        $this->assign('data0', getList($table,$where,'start_time desc'));
        $where=array('borrower'=>getLoginUser());
        $table='tp_device_overdue';
        $this->assign('data3', getList($table,$where,'end_time desc'));

        $this->display();
    }

    public function books(){
        $table='tp_device_loaning_record';
        //预约中
        $where=array('borrower'=>getLoginUser(),'leibie'=>'3','type'=>'1');
        $riqi = date("Y-m-d", time());
        $where['start_time']=array('egt',$riqi);
        $this->assign('data1', getList($table,$where,'start_time desc'));
        //借用历史
        $where['deleted']='0';
        $where['start_time']=array('lt',$riqi);
        $where['type']='2';
        $this->assign('data2', getList($table,$where,'start_time desc'));
        //借用中
        $where=array('borrower'=>getLoginUser(),'leibie'=>'3','type'=>'0');
        $this->assign('data0', getList($table,$where,'start_time desc'));

        $this->display();
    }
    //指派给我的任务
    public function task(){
        $_SESSION['url'] = '/' . C('PRODUCT') . '/Mine/task/';
        $this->isLogin();
        $where['issuetype'] = array('in','10005,10006,10007');
        $where['issuestatus'] = array('not in', '10011,6,10002');
        $where['ASSIGNEE'] = getLoginUser();
        $this->assign('data', postIssue($where));

        $this->display();
    }
    //指派给我的Bug
    public function bug(){
        $_SESSION['url'] = '/' . C('PRODUCT') . '/Mine/bug/';
        $this->isLogin();
        $where['issuetype'] = '10008';
        $where['issuestatus'] = array('not in', '10011,6');
        $where['ASSIGNEE'] = getLoginUser();
        $this->assign('data', postIssue($where));

        $this->display();
    }
    //我创建的Bug
    public function created(){
        $_SESSION['url'] = '/' . C('PRODUCT') . '/Bug/created/';
        $this->isLogin();
        $where['issuetype'] = '10008';
        $where['issuestatus'] = array('not in', '10011,6');
        $where['REPORTER'] =getLoginUser();
        $this->assign('data', postIssue($where));

        $this->display();
    }
    //主动取消
    function cancel(){
        $_GET['deleted']='1';
        $_GET['reject']='1';
        $_GET['table']='tp_device_loaning_record';
        $this->update();
        $this->msgQXYuDing($_GET['id']);
    }
    //续期
    function renewal(){
        $table='tp_device_loaning_record';
        $_GET['renewal']='1';
        $arr=find($table,I('id'));
        $time = strtotime($arr['end_time']);
        $start_time=date('Y-m-d', $time);
        $week = date('w', $time);
        if ($week == 5) {//3天后
            $time=$time + 3*24 * 60 * 60;
        } elseif ($week == 6) {//+2天后
            $_GET['end_time'] = date('Y-m-d H:i:s', $time + 2 * 24 * 60 * 60);
            $time=$time + 2*24 * 60 * 60;
        } else {//+1天后
            $time=$time + 24 * 60 * 60;
        }
        $_GET['end_time']=date('Y-m-d H:i:s', $time);
        //当前日期有预约不可以续期
        $where=array('start_time'=>$start_time,'device'=>$arr['device']);
        $var=getList($table,$where);
        if($var){
            $this->error($start_time.'有'.getJiraName($var[0]['borrower']).'的预订,不能续期');
        }else{
            $_GET['table']=$table;
            $this->update();
            //发送企业微信$borrower,$manager,$device,$end_time
            $this->msgYanQi($arr['borrower'],$arr['manager'],$arr['device'],$_GET['end_time']);
        }

    }
}