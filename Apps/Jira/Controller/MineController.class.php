<?php

namespace Jira\Controller;
class MineController extends WebInfoController
{
    public function index()
    {
        $m=M('tp_device_loaning_record');
        $riqi = date("Y-m-d", time());
        //预约中
        $user=$project=cookie(C(PRODUCT).'_user');
        $user=jie_mi($user);
        $where=array('borrower'=>$user,'type'=>'1');
        $where['start_time']=array('egt',$riqi);
        $data=$m->where($where)->order('start_time desc')->select();
        $this->assign('data1', $data);
        //借用历史
        $where['deleted']='0';
        $where['start_time']=array('lt',$riqi);
        $where['type']='2';
        $data=$m->where($where)->order('start_time desc')->select();
        $this->assign('data2', $data);
        //借用中
        $where=array('borrower'=>$user,'type'=>'0');
        $data=$m->where($where)->order('start_time desc')->select();
        $this->assign('data0', $data);

        $data=M("tp_device_overdue")->where(array('borrower'=>$user,"deleted"=>"0"))->order('end_time desc')->select();
        $this->assign('data3', $data);

        $this->display();
    }

    public function books(){
        $m=M('tp_device_loaning_record');
        $riqi = date("Y-m-d", time());
        $user=$project=cookie(C(PRODUCT).'_user');
        $user=jie_mi($user);
        //预约中
        $where=array('borrower'=>$user,'leibie'=>'3','type'=>'1');
        $where['start_time']=array('egt',$riqi);
        $data=$m->where($where)->order('start_time desc')->select();
        $this->assign('data1', $data);
        //借用历史,'deleted'=>'0'
        $where['deleted']='0';
        $where['start_time']=array('lt',$riqi);
        $where['type']='2';
        $data=$m->where($where)->order('start_time desc')->select();
        $this->assign('data2', $data);
        //借用中
        $where=array('borrower'=>$user,'leibie'=>'3','type'=>'0');
        $data=$m->where($where)->order('start_time desc')->select();
        $this->assign('data0', $data);

        $this->display();
    }
    //指派给我的任务
    public function task(){
        $url= '/' . C(PRODUCT) . '/Mine/task/';
        cookie('url',$url,array('prefix'=>C(PRODUCT).'_'));
        $this->isLogin();
        $user=$project=cookie(C(PRODUCT).'_user');
        $user=jie_mi($user);
        $where['issuetype'] = array('in','10005,10006,10007');
        $where['issuestatus'] = array('not in', '10011,6,10002');
        $where['ASSIGNEE'] = $user;
        $url = C(JIRAPI) . "/Jirapi/issue";
        $data = httpJsonPost($url, json_encode($where));
        $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);
        $this->assign('data', $data);

        $this->display();
    }
    //指派给我的Bug
    public function bug(){
        $url= '/' . C(PRODUCT) . '/Mine/bug/';
        cookie('url',$url,array('prefix'=>C(PRODUCT).'_'));
        $this->isLogin();
        $user=$project=cookie(C(PRODUCT).'_user');
        $user=jie_mi($user);
        $where['issuetype'] = '10008';
        $where['issuestatus'] = array('not in', '10011,6');
        $where['ASSIGNEE'] = $user;
        $url = C(JIRAPI) . "/Jirapi/issue";
        $data = httpJsonPost($url, json_encode($where));
        $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);
        $this->assign('data', $data);

        $this->display();
    }
    //我创建的Bug
    public function created(){
        $url = '/' . C(PRODUCT) . '/Bug/created/';
        cookie('url',$url,array('prefix'=>C(PRODUCT).'_'));
        $this->isLogin();
        $user=$project=cookie(C(PRODUCT).'_user');
        $user=jie_mi($user);
        $where['issuetype'] = '10008';
        $where['issuestatus'] = array('not in', '10011,6');
        $where['REPORTER'] = $user;
        $url = C(JIRAPI) . "/Jirapi/issue";
        $data = httpJsonPost($url, json_encode($where));
        $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);
        $this->assign('data', $data);

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
        $m=M($table);
        $arr=$m->find(I('id'));
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
        $where=array('start_time'=>$start_time,'device'=>$arr['device'],'deleted'=>'0');
        $var=$m->where($where)->select();
        if($var){
            $this->error($start_time.'有'.getZTUserName($var[0]['borrower']).'的预订,不能续期');
        }else{
            $_GET['table']=$table;
            $this->update();
            //发送企业微信$borrower,$manager,$device,$end_time
            $this->msgYanQi($arr['borrower'],$arr['manager'],$arr['device'],$_GET['end_time']);
        }

    }
}