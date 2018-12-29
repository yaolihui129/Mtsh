<?php
namespace Jira\Controller;
class CaseController extends WebInfoController
{
    public function index()
    {
        $project=getCache('project');
        $where['PROJECT'] =$project;
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['SUMMARY|issuenum'] = array('like', '%' . $search . '%');
        $this->assign('data', postIssue($where));

        $this->display();
    }

    public function func(){
        $where['PROJECT'] = getCache('project');
        $where['issuetype'] = '10101';
        $where['PRIORITY'] = '1';
        $search = trim(I('search','呼叫中心'));
        $this->assign('search', $search);
        $where['SUMMARY|issuenum'] = array('like', '%' . $search . '%');
        $this->assign('data', postIssue($where));

        $this->display();
    }
}