<?php

namespace Jira\Controller;
class SetController extends WebInfoController
{
    public function index()
    {

        $this->display();
    }
    //Api项目权限
    public function eo_conn_project(){
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
        $data = M('eo_conn_project')->where($where)->order('projectID')->select();
        $this->assign('data', $data);
        //查询eoLinker用户
        $where=array();
        foreach ($data as $d){
            $userid[]=$d['userid'];
        }
        $where['userID']=array('not in',$userid);
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['userName|userNickName'] = array('like', '%' . $search . '%');
        $user=M('eo_user')->where($where)->select();
        $this->assign('user', $user);

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
}