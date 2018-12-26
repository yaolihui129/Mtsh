<?php
namespace Jira\Controller;
class RiskController extends WebInfoController
{
    public function index()
    {
        $table='tp_risk';
        $where = array('project' => I('project', '10006'));
        $this->assign("data", getList($table,$where));

        $this->display();
    }

    public function risk()
    {
        $table='tp_risk';
        $where = array("proid" => I('project', '10006'));
        $this->assign("risks", getList($table,$where));

        $count = countId($table,$where);
        $this->assign('c', $count+1);
        $this->assign("state", select("打开", "state", "rstate"));
        $this->assign("level", select("C", "level", "risklevel"));
        $this->assign("tamethod", PublicController::editor("amethod", "暂无方案"));

        $this->display();
    }

    public function mod()
    {
        $table='tp_risk';
        $where = array("proid" => I('project', '10006'));
        $this->assign("data", getList($table,$where));

        $risk = find($table,I('id'));
        $this->assign("risk", $risk);
        $this->assign("level", select($risk['level'], "level", "risklevel"));
        $this->assign("state", select($risk['state'], "state", "rstate"));
        $this->assign("tamethod", PublicController::editor("amethod", $risk['amethod']));


        $this->display();
    }

}