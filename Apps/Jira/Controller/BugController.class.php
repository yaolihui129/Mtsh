<?php
namespace Jira\Controller;
class BugController extends WebInfoController
{
    public function index()
    {
        $project=getCache('project');
        $table= 'tp_jira_issue';
        $map = array('status'=>'0');
        $map['project'] = $project;
        $map['issuetype'] = '10008';
        $IssueID=filterID($table,$map);
        $where['PROJECT'] = $project;
        $datum = date("Y-m-d", time() - 24 * 3600);
        $datum = strtotime($datum);//将日期转化为时间戳
        $datum = date("Y-m-d H:i", $datum + 17.5 * 3600);
        $where['CREATED'] = array('lt', $datum);
        $where['issuetype'] = '10008';
        $where['issuestatus'] = array('not in', '10011,6');
        if($IssueID){
            $where['ID']=array('not in',$IssueID);
        }
        //同步到本地库
        $issue=postIssue($where);
        if($issue){
            $this->synchJiraIssue($issue);
        }
        $map['created'] = array('lt', $datum);
        $map['status'] = '1';
        $map['issuestatus'] = '1';
        $this->assign('data', getList($table,$map,'created desc'));

        $this->display();
    }

    public function fault()
    {
        $table= 'tp_jira_issue';
        $project=getCache('project');
        $map = array('status'=>'0');
        $map['project'] = $project;
        $map['issuetype'] = '10008';
        $map['priority'] = array('in', '1,2');
        $IssueID=filterID($table,$map);
        if($IssueID){
            $where['ID']=array('not in',$IssueID);
        }
        $datum = strtotime('2018-10-1');//将日期转化为时间戳
        $datum = date("Y-m-d H:i", $datum);
        $where['CREATED'] = array('gt', $datum);
        $where['PROJECT'] = $project;
        $where['issuetype'] = '10008';
        $where['PRIORITY'] = array('in', '1,2');
        $data=postIssue($where);
        if($data){
            //同步到本地库
            $this->synchJiraIssue($data);
        }
        $map['status'] = '1';
        $this->assign('data', getList($table,$map,'priority,assignee'));

        $this->display();
    }

    public function noclosed()
    {
        $table= 'tp_jira_issue';
        $project=getCache('project');
        $map = array('status'=>'0');
        $map['project'] = $project;
        $map['issuetype'] = '10008';
        $map['issuestatus'] = array('not in', '10011,6');
        $IssueID=filterID($table,$map);
        if($IssueID){
            $where['ID']=array('not in',$IssueID);
            $map['status'] = '1';
            $IssueID=filterID($table,$map);
            //删除已有的条目
            foreach ($IssueID as $vo){
                realdel($table,$vo);
            }
        }
        $where['PROJECT'] = $project;
        $where['issuetype'] = '10008';
        $where['issuestatus'] = array('not in', '6');
        $data=postIssue($where);
        if($data){
            $this->synchJiraIssue($data);
        }

        $map['status'] = '1';
        $this->assign('data', getList($table,$map,'created desc'));

        $this->display();
    }

    public function bug_list()
    {
        $table= 'tp_jira_issue';
        $map = array('status'=>'0');
        $IssueID=filterID($table,$map);
        if($IssueID){
            $where['ID']=array('not in',$IssueID);
        }
        $data = getTestRunBug(I('run_id'), I('step_id'));
        $arr = array();
        foreach ($data as $da) {
            $arr[] = $da['bug_id'];
        }
        $where['ID'] = array('in', $arr);
        $Issue=postIssue($where);
        $this->assign('data', $Issue);
        //同步到本地库
        $this->synchJiraIssue($Issue);


        $this->display();
    }

    public function mine()
    {
        $url = '/Jira/Bug/mine/';
        $this->delJiraIssueClosedBug();
        $this->isLogin($url);
        $table= 'tp_jira_issue';
        $project=getCache('project');
        $map = array('status'=>'0');
        $map['project'] = $project;
        $map['issuetype'] = '10008';
        $map['assignee'] = getLoginUser();
        $map['issuestatus'] = array('not in', '10011,6');
        $IssueID=filterID($table,$map);
        if($IssueID){
            $where['ID']=array('not in',$IssueID);
        }
        $where['PROJECT'] = $project;
        $where['issuetype'] = '10008';
        $where['issuestatus'] = array('not in', '10011,6');
        $where['ASSIGNEE'] = getLoginUser();
        $data=postIssue($where);
        if($data){
            //同步到本地库
            $this->synchJiraIssue($data);
        }
        $map['status'] = '1';
        $order='issuestatus desc,assignee';
        $this->assign('data', getList($table,$map,$order));

        $this->display();
    }

    function setStatus(){
        $issue=array(getIssue($_GET['id']));
        $res='';
        if($issue){
            $res=$this->synchJiraIssue($issue);
        }
        if(getLoginUser()=='ylh'){
            $_GET['status']='0';
            $_GET['table']='tp_jira_issue';
            $this->update();
        }else{
            if($res){
               $this->success('成功');
            }else{
                $this->error('失败');
            }
        }
    }

    function test(){
        $arr=array(
            array('人保','太平洋','阳光','大地','安盛','中华','中银'),
            array('新车,','非新车,','新能源新车,','新能源非新车','过户车'),
            array('普通地区,','江苏,','北京,'),
            array('单交强,','单商业,','综合险,'),
            array('非转保,','转保,'),
        );

        $newArr=CartesianProduct($arr);

        print_r($newArr);
    }


}