<?php
namespace Jira\Controller;
class SetController extends WebInfoController
{
    //初始化数据
    function init(){
        $data = array(
            'table_dict' => 'tp_dict',
            'name'       => 'Set',
            'where'      => array(),
            'order'      => 'id'
        );
        return $data;
    }

    public function index()
    {
        $this->display();
    }
    //Api项目权限
    public function eo_conn_project(){
        $table='eo_conn_project';
        //查询所属项目权限
        $projectList = $this->projectList();
        $project = array();
        foreach ($projectList as $k => $v) {
            $project[$k]['key'] = $v['projectid'];
            $project[$k]['value'] = $v['projectname'];
        }
        $this->assign('project', $project);
        $branch=I('branch',2);
        $this->assign('branch', $branch);
        $where['projectID']=$branch;
        $data = getList($table,$where,'projectID');
        $this->assign('data', $data);
        //查询eoLinker用户
        $where=array();
        $userid=array();
        foreach ($data as $d){
            $userid[]=$d['userid'];
        }
        $where['userID']=array('not in',$userid);
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['userName|userNickName'] = array('like', '%' . $search . '%');
        $table='eo_user';
        $this->assign('user', getList($table,$where,'userID'));

        $this->display();
    }
    //更改权限
    function conn_project_change(){
        $_GET['table']='eo_conn_project';
        if($_GET['userType']==2){
            $_GET['userType']=3;
        }elseif ($_GET['userType']==3){
            $_GET['userType']=2;
        }
        $this->update();
    }
    //移除权限
    function conn_project_remove(){
        $_GET['table']='eo_conn_project';
        $this->realdel(I('connID'));
    }
    //添加权限
    function conn_project_add(){
        $_GET['table']='eo_conn_project';
        $this->insert();
    }

    //数据字典
    public function dict(){
        $info = $this->init();
        $where=$info['where'];
        $table=$info['table_dict'];
        //处理查询条件
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['type'] = array('like', '%' . $search . '%');
        //查询数据
        $data=getList($table,$where,$info['order']);
        //返回数据
        $typeList=array();
        foreach ($data as $da){
            if(!in_array($da['type'],$typeList)){
                $typeList[]=$da['type'];
            }
        }
        $this->assign("typeList", $typeList);
        $type=I('type',$typeList[0]);
        $this->assign("type", $type);
        $where['type']=$type;
        //查询数据
        $data=getList($table,$where,$info['order']);
        //返回数据
        $this->assign("data", $data);
        $this->assign("count", sizeof($data));

        $this->display();
    }
    //修改数值
    function dict_update(){
        //初始化
        $info = $this->init();
        $_POST['table']=$info['table_dict'];
        if(I('id')){
            $this->update();
        }else{
            $this->insert();
        }
    }
    //废弃数据字典值
    function shan_chu_dict(){
        //初始化
        $info = $this->init();
        $_GET['table']=$info['table_dict'];
        $this->realdel(I('id'));
    }
    //获取数据字典值
    function dict_info(){
        //初始化
        $info = $this->init();
        $res=find($info['table_dict'],I('id'));
        $res=resFormat($res);
        $this->ajaxReturn($res);
    }
}