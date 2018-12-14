<?php

namespace Jira\Controller;
class WeeklyController extends WebInfoController
{
    public function index()
    {
        $id=I('id');
        $where=array('deleted'=>'0');
        $map=array('deleted'=>'0');
        $user=cookie(C(PRODUCT).'_user');
        $user=jie_mi($user);
        if(in_array($user,array_diff(C(QA_TESTER),['ylh']))){
            $map['draw']=$user;
        }
        $weekly=M('tp_weekly')->where($where)->order('end desc')->select();
        $this->assign('weekly', $weekly);

        if($id){//查历史
            $week=M('tp_weekly')->find($id);
            $this->assign('week', $week);
            $data=M('tp_weekly_detail')->where($map)->select();
        }else{
            $this->assign('week', $weekly[0]);
            $m=M('tp_project_pending');
            //本周工作
            $map['status']=array('in',['0','1']);
            $map['issuestatus']=array('not in',['1','10000','10015','10016']);
            $list=$this->get_dict_list('test_group');
            foreach ($list as $vo){
                $map['pgroup']=$vo['key'];
                $data=$m->where($map)->order('status desc,issuestatus')->select();
                $this->assign('data'.$vo['key'], $data);
            }
            //下周计划
            $map['status']='0';
            $map['issuestatus']=array('not in',['1','10002','10015']);
            foreach ($list as $vo){
                $map['pgroup']=$vo['key'];
                $data=$m->where($map)->order('status desc,issuestatus')->select();
                $this->assign('doing'.$vo['key'], $data);
            }
        }
        $this->display();
    }

}