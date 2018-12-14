<?php
namespace Jira\Controller;
class CaseController extends WebInfoController
{
    public function index()
    {

        if (I('project')) {
            cookie('project',I('project'),array('prefix'=>C(PRODUCT).'_'));
        }
        $project=cookie(C(PRODUCT).'_project');
        $where['PROJECT'] = intval($project);

        $search = trim(I('search'));
        $_SESSION['search']['index'] = $search;
        $this->assign('search', $search);

        $where['SUMMARY|issuenum'] = array('like', '%' . $search . '%');
        $_SESSION['map']['plan'] = $where;
        $url = C(JIRAPI) . "/Jirapi/issue";
        $data = httpJsonPost($url, json_encode($where));
        $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);
        $this->assign('data', $data);

        $project = $this->projectDict();
        $this->assign('project', $project);

        $this->display();
    }

    public function func(){
        $url= '/' . C(PRODUCT) . '/Case/func';
        cookie('url',$url,array('prefix'=>C(PRODUCT).'_'));
        $this->isLogin();
        $project=cookie(C(PRODUCT).'_project');
        $where['PROJECT'] = intval($project);
        $where['issuetype'] = '10101';
        $where['PRIORITY'] = '1';
        $search = trim(I('search','呼叫中心'));
        $_SESSION['search']['index'] = $search;
        $this->assign('search', $search);

        $where['SUMMARY|issuenum'] = array('like', '%' . $search . '%');
        $_SESSION['map']['plan'] = $where;
        $url = C(JIRAPI) . "/Jirapi/issue";
        $data = httpJsonPost($url, json_encode($where));
        $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);
        $this->assign('data', $data);

        $this->display();
    }
}