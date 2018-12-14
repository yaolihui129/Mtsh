<?php

namespace Jira\Controller;
class BorrowController extends WebInfoController
{
    public function index()
    {
        $arr=M('tp_device')->find(I('id'));
        $this->assign('arr', $arr);
        $where=array('deleted'=>'0');
        $user=M('tp_jira_user')->where($where)->order('username')->select();
        foreach ($user as $k => $v){
            $user[$k]['key'] = $v['username'];
            $user[$k]['value'] = '【'.countId('tp_device_overdue','borrower',$v['username']).'】'.$v['name'].'('.$v['username'].')';
        }
        //封装下拉列表
        $d_user=cookie(C(PRODUCT).'_user');
        $d_user=jie_mi($d_user);
        $user = $this->select($user, 'borrower',$d_user);

        $this->assign('user', $user);


        $source=I('source');
        $this->assign('source', $source);
        $this->assign('search', I('search'));
        $this->assign('url', 'Jira/Manager/'.$source);
        $this->assign('riqi', date("Y-m-d", time()));
        if ($source=='index'){
            $this->assign('rules', $this->book_rules());
        }elseif ($source=='books'){
            $this->assign('rules', C(BOOKS_RULES));
        }else{

        }

        $this->display();
    }

    public function yuding(){
        $id=I('id');
        $source=I('source');
        $search=I('search');
        $url= '/' . C(PRODUCT) . '/Borrow/yuding/id/'.$id.'/source/'.$source.'/search/'.$search;
        cookie('url',$url,array('prefix'=>C(PRODUCT).'_'));
        $this->isLogin();
        $arr=M('tp_device')->find($id);
        $this->assign('arr', $arr);
        $where=array('device'=>$id,'type'=>'1','deleted'=>'0');
        $data=M('tp_device_loaning_record')->where($where)->order('end_time desc')->select();
        $this->assign('data', $data);

        $this->assign('source', $source);
        $this->assign('search', $search);
        $this->assign('url', 'Jira/Books/'.$source);
        $this->assign('riqi', date("Y-m-d", time()));
        $user=jie_mi(cookie(C(PRODUCT).'_user'));
        $this->assign('user', $user);
        if ($source=='index'){
            $this->assign('rules', $this->book_rules());
        }elseif ($source=='books'){
            $this->assign('rules', C(BOOKS_RULES));
        }else{

        }

        $this->display();
    }
    //借出操作
    function lend(){
        //插入记录
        $user=jie_mi(cookie(C(PRODUCT).'_user'));
        $m=D('tp_device_loaning_record');
        $time = strtotime(date("Y-m-d", time()));
        $week = date('w', $time);
        if($_POST['leibie']=='1'){//设备
            if ($week == 5) {//3天后的9:15
                $_POST['end_time'] = date('Y-m-d H:i:s', $time + 3 * 24 * 60 * 60 + 9 * 60 * 60 + 15 * 60);
            } elseif ($week == 6) {//+2天后的9:15
                $_POST['end_time'] = date('Y-m-d H:i:s', $time + 2 * 24 * 60 * 60 + 9 * 60 * 60 + 15 * 60);
            } else {//+1天后的9:15
                $_POST['end_time'] = date('Y-m-d H:i:s', $time + 24 * 60 * 60 + 9 * 60 * 60 + 15 * 60);
            }
        }elseif ($_POST['leibie']=='3'){//图书
            if ($week == 5) {//3天后的9:15
                $_POST['end_time'] = date('Y-m-d H:i:s', $time + 17 * 24 * 60 * 60 + 9 * 60 * 60 + 15 * 60);
            } elseif ($week == 6) {//+2天后的9:15
                $_POST['end_time'] = date('Y-m-d H:i:s', $time + 16 * 24 * 60 * 60 + 9 * 60 * 60 + 15 * 60);
            } else {//+1天后的9:15
                $_POST['end_time'] = date('Y-m-d H:i:s', $time + 15*24 * 60 * 60 + 9 * 60 * 60 + 15 * 60);
            }
        }
        $_POST['adder'] = $user;
        $_POST['moder'] = $user;
        $_POST['ctime'] = time();
        if (!$m->create()) {
            $this->error($m->getError());
        }
        $id=$m->add();
        if ($id) { //更新设备状态
            $var['id']=$_POST['device'];
            $var['borrower']=$_POST['borrower'];
            $var['loaning']='1';
            if(D('tp_device')->save($var)){
                //2.发送企业微信消息
                //$borrower,$manager,$device,$end_time,$remark
                $this->msgJieChu($_POST['borrower'],$_POST['manager'],$_POST['device'],$_POST['end_time'],$_POST['remark']);

                if ($_POST['url']){
                    $this->success("成功",U($_POST['url']));
                }else{
                    $this->success("成功");
                }
            }else{//回滚并删除记录数据
                $m->delete($id);
                $this->error("失败");
            }
        } else {
            $this->error("失败");
        }
    }
    //预约操作
    function bespeak(){
    //指定日期有预约或已借出，不能被预约
        if($_POST['remark']){
            $table='tp_device_loaning_record';
            $start_time=I('start_time');
            $device=I('device');
            $borrow=I('borrower');
            $manager=I('manager');
            $remark=I('remark');
            $where=array('device'=>I('device'),'start_time'=>$start_time,'deleted'=>'0');
            $var=M($table)->where($where)->select();
            if($var){//当天有预约或借用
                $this->error($start_time.'有人使用该设备');
            }else{//无记录，插入预订记录
                $_POST['table']=$table;
                $this->insert();
                //2.发送企业微信消息
                $this->msgYuDing($borrow,$manager,$device,$start_time,$remark);
            }
        }else{
            $this->error('请如实填写借用的用途');
        }
    }


}