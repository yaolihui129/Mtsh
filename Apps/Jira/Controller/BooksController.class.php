<?php
namespace Jira\Controller;
class BooksController extends WebInfoController
{
    public function index()
    {
        $search = I('search');
        $this->assign('search', $search);
        $where=array('type'=>'1');
        $where['brand|ts|serial|asset_no|sys_version'] = array('like', '%' . $search . '%');
        $this->assign('data', getList('tp_device',$where));

        $this->display();
    }

    public function books()
    {
        $search = I('search');
        $this->assign('search', $search);
        $where=array('type'=>'3');
        $where['brand|ts|serial|name|sys_version'] = array('like', '%' . $search . '%');
        $this->assign('data', getList('tp_device',$where));

        $this->display();
    }

    public function history()
    {
        $id=I('id');
        $this->assign('arr', find('tp_device',$id));
        $this->assign('source', I('source'));
        $this->assign('search', I('search'));


        $riqi = date("Y-m-d", time());
        //预约中
        $where=array('device'=>$id,'type'=>'1');
        $where['start_time']=array('egt',$riqi);
        $table='tp_device_loaning_record';
        $this->assign('data1', getList($table,$where,'start_time desc'));
        $where['deleted']='0';
        $where['start_time']=array('elt',$riqi);
        $where['type']='2';
        $this->assign('data2',  getList($table,$where,'start_time desc'));
        //借用中
        $where=array('device'=>$id,'type'=>'0');
        $this->assign('data0', getList($table,$where,'start_time desc'));

        $this->display();
    }

    public function rules()
    {
        $data=$this->book_rules();
        $this->assign('data', $data);

        $this->display();
    }

    public function yuqi(){
        $where=array();
        $table='tp_device_overdue';
        $data = getList($table,$where,'end_time desc');
        $user=array();
        foreach ($data as $k=>$da){
            if(!in_array($da['borrower'],$user)){
                $user[$k]=$da['borrower'];
            }
        }
        $this->assign('user', $user);
        $where['borrower']=I('borrower',$user[0]);
        $this->assign('borrower',$where['borrower']);
        $this->assign('data',getList($table,$where,'end_time desc'));

        $this->display();
    }

}