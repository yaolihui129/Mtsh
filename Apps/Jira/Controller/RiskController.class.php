<?php

namespace Jira\Controller;
class RiskController extends WebInfoController
{
    public function index()
    {
        $project = I('project', '10006');
        $where = array('project' => $project, 'deleted' => '0');
        $data = M("tp_risk")->where($where)->order('ctime desc')->select();
        $this->assign("data", $data);

        $this->display();

    }

    public function risk()
    {
        $_SESSION['project'] = I('project', '10006');
        $m = D("tp_risk");
        $where = array("proid" => $_SESSION['proid']);
        $risks = $m->where($where)->select();
        $this->assign("risks", $risks);

        $count = $m->where($where)->count() + 1;
        $this->assign('c', $count);
        $this->assign("state", formselect("打开", "state", "rstate"));
        $this->assign("level", formselect("C", "level", "risklevel"));
        $this->assign("tamethod", PublicController::editor("amethod", "暂无方案"));

        $this->display();
    }


    public function mod()
    {
        $m = D("tp_risk");
        $where = array("proid" => $_SESSION['proid']);
        $data = $m->where($where)->select();

        $this->assign("data", $data);

        $risk = $m->find(I('id'));
        $this->assign("risk", $risk);
        $this->assign("level", formselect($risk['level'], "level", "risklevel"));
        $this->assign("state", formselect($risk['state'], "state", "rstate"));
        $this->assign("tamethod", PublicController::editor("amethod", $risk['amethod']));


        $this->display();
    }

}