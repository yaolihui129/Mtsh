<?php
namespace Jira\Controller;
class BugController extends WebInfoController
{
    public function index()
    {
        $table= 'tp_jira_issue';
        $map = array('status'=>'0');
        $IssueID=filterID($table,$map);
        $where['ID']=array('not in',$IssueID);

        $where['PROJECT'] = intval($_SESSION['project']);
        $where['issuetype'] = '10008';
        $where['issuestatus'] = '1';
        $datum = date("Y-m-d", time() - 24 * 3600);
        $datum = strtotime($datum);//将日期转化为时间戳
        $datum = date("Y-m-d H:i", $datum + 17.5 * 3600);
        $where['CREATED'] = array('lt', $datum);
        //同步到本地库
        $this->synchJiraIssue(postIssue($where));

        $where['project'] = intval($_SESSION['project']);
        $where['status'] = '1';
        $where['created'] = array('lt', $datum);
        $order='issuestatus desc,assignee';
        $this->assign('data', getList($table,$where,$order));

        $this->display();
    }

    public function fault()
    {
        $table= 'tp_jira_issue';
        $map = array('status'=>'0');
        $IssueID=filterID($table,$map);
        $where['ID']=array('not in',$IssueID);
        $where['PROJECT'] = intval($_SESSION['project']);
        $where['issuetype'] = '10008';
        $where['PRIORITY'] = array('in', '1,2');

        //同步到本地库
        $this->synchJiraIssue(postIssue($where));

        $where['project'] = intval($_SESSION['project']);
        $where['priority'] = array('in', '1,2');
        $where['status'] = '1';
        $order='issuestatus desc,assignee';
        $this->assign('data', getList($table,$where,$order));

        $this->display();
    }

    public function noclosed()
    {
        $table= 'tp_jira_issue';
        $map = array('status'=>'0');
        $IssueID=filterID($table,$map);
        $where['ID']=array('not in',$IssueID);
        $where['PROJECT'] = intval($_SESSION['project']);
        $where['issuetype'] = '10008';
        $where['issuestatus'] = array('not in', '10011,6');
        //同步到本地库
        $this->synchJiraIssue(postIssue($where));

        $where['project'] = intval($_SESSION['project']);
        $where['status'] = '1';
        $order='created desc';
        $this->assign('data', getList($table,$where,$order));

        $this->display();
    }

    public function bug_list()
    {
        $table= 'tp_jira_issue';
        $map = array('status'=>'0');
        $IssueID=filterID($table,$map);
        $where['ID']=array('not in',$IssueID);
        $data = getTestRunBug(I('run_id'), I('step_id'));
        $arr = array();
        foreach ($data as $da) {
            $arr[] = $da['bug_id'];
        }
        $where['ID'] = array('in', $arr);
        $Issue=postIssue($where);
        //同步到本地库
        $this->synchJiraIssue($Issue);

        $this->assign('data', $Issue);
        $this->display();
    }

    public function mine()
    {
        $url = '/Jira/Bug/mine/';
        $this->isLogin($url);
        $table= 'tp_jira_issue';
        $map = array('status'=>'0');
        $IssueID=filterID($table,$map);
        $where['ID']=array('not in',$IssueID);
        $where['issuetype'] = '10008';
        $where['issuestatus'] = array('not in', '10011,6');
        $where['ASSIGNEE'] = getLoginUser();
        //同步到本地库
        $this->synchJiraIssue(postIssue($where));

        $where['assignee'] = getLoginUser();
        $where['status'] = '1';
        $order='issuestatus desc,assignee';
        $this->assign('data', getList($table,$where,$order));

        $this->display();
    }

    function setStatus(){
        $user=getLoginUser();
        if($user=='ylh'){
            $_GET['status']='0';
            $_GET['table']='tp_jira_issue';
            $this->update();
        }else{
            $this->error('不允许操作');
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