<?php
namespace Jira\Controller;
class ScoreController extends WebInfoController
{
    public function index()
    {
        $table='tp_my_score';
        $data = getList($table, array(),'ctime desc');
        $saiJi = array();
        foreach ($data as $da) {
            if (!in_array($da['quarter'], $saiJi)) {
                $saiJi[] = $da['quarter'];
            }
        }
        $this->assign("saiJi", $saiJi);

        $quarter = I('quarter', $saiJi[0]);
        $this->assign("quarter", $quarter);

        $where = array('quarter' => $quarter);
        $user = array_diff(C('QA_TESTER'), array('ylh'));
        $where['user'] = array('in', $user);
        $table='tp_score_list';
        $this->assign("data", getList($table,$where,'score desc'));

        $this->display();
    }

    //更多
    public function gengduo()
    {
        $table='tp_my_score';
        $quarter = I('quarter', C('KH_QUARTER'));
        $this->assign("quarter", $quarter);
        $user = I('user');
        $this->assign("user", $user);
        $where = array('user' => $user, 'quarter' => $quarter);
        $this->assign("data", getList($table,$where,'ctime desc'));
        $this->assign("score", sumScore($user, $quarter));

        $this->display();
    }

    //申诉
    public function appeal()
    {
        $table='tp_my_score';
        $arr =find($table,I('id'));
        if ($arr['dissent']) {
            $this->assign("arr", $arr);
            $shix = $arr['ctime'] + 7 * 24 * 3600;
            $this->assign("shix", date('Y-m-d H:i:s', $shix));
        } else {
            $this->error('考核不允许申诉');
        }
        $this->display();
    }

}