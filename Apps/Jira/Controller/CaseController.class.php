<?php
namespace Jira\Controller;
class CaseController extends WebInfoController
{
    public function index()
    {

        if (I('project')) {
            $_SESSION['project'] = I('project');
        }

        $where['PROJECT'] = intval($_SESSION['project']);

        $search = trim(I('search'));
        $_SESSION['search']['index'] = $search;
        $this->assign('search', $search);

        $where['SUMMARY|issuenum'] = array('like', '%' . $search . '%');
        $this->assign('data', postIssue($where));

        $this->assign('project', $this->projectDict());

        $this->display();
    }

    public function func(){
        $this->isLogin();
        $where['PROJECT'] = intval($_SESSION['project']);
        $where['issuetype'] = '10101';
        $where['PRIORITY'] = '1';
        $search = trim(I('search','呼叫中心'));
        $_SESSION['search']['index'] = $search;
        $this->assign('search', $search);

        $where['SUMMARY|issuenum'] = array('like', '%' . $search . '%');

        $this->assign('data', postIssue($where));

        $this->display();
    }
}