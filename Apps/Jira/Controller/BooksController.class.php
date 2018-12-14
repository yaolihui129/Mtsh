<?php

namespace Jira\Controller;
class BooksController extends WebInfoController
{
    public function index()
    {
        $search = I('search');
        $this->assign('search', $search);
        $where=array('type'=>'1','deleted'=>'0');
        $where['brand|ts|serial|asset_no|sys_version'] = array('like', '%' . $search . '%');
        $data = M('tp_device')->where($where)->select();
        $this->assign('data', $data);

        $this->display();
    }



    public function books()
    {
        $search = I('search');
        $this->assign('search', $search);
        $where=array('type'=>'3','deleted'=>'0');
        $where['brand|ts|serial|name|sys_version'] = array('like', '%' . $search . '%');
        $m = M('tp_device');
        $data = $m->where($where)->select();
        $this->assign('data', $data);

        $this->display();
    }

    public function history()
    {
        $id=I('id');
        $arr=M('tp_device')->find($id);
        $this->assign('arr', $arr);
        $this->assign('source', I('source'));
        $this->assign('search', I('search'));
        $riqi = date("Y-m-d", time());
        //预约中
        $where=array('device'=>$id,'type'=>'1');
        $where['start_time']=array('egt',$riqi);
        $m = M('tp_device_loaning_record');
        $data = $m->where($where)->order('start_time desc')->select();
        $this->assign('data1', $data);
        $where['deleted']='0';
        $where['start_time']=array('elt',$riqi);
        $where['type']='2';
        $data = $m->where($where)->order('start_time desc')->select();
        $this->assign('data2', $data);
        //借用中
        $where=array('device'=>$id,'type'=>'0');
        $data=M('tp_device_loaning_record')->where($where)->order('start_time desc')->select();
        $this->assign('data0', $data);

        $this->display();
    }

    public function rules()
    {
        $data=$this->book_rules();
        $this->assign('data', $data);

        $this->display();
    }

    public function yuqi(){
        $where=array("deleted"=>"0");
        $data=M("tp_device_overdue")->where($where)->order('end_time desc')->select();

        $user=array();
        foreach ($data as $k=>$da){
            if(in_array($da['borrower'],$user)){

            }else{
                $user[$k]=$da['borrower'];
            }
        }
        $this->assign('user', $user);
        $where['borrower']=I('borrower',$user[0]);
        $data=M("tp_device_overdue")->where($where)->order('end_time desc')->select();
        $this->assign('borrower',$where['borrower']);
        $this->assign('data',$data);

        $this->display();
    }

}