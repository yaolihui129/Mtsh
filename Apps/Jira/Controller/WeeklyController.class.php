<?php
namespace Jira\Controller;
class WeeklyController extends WebInfoController
{
    public function index()
    {
        $id = I('id');
        $user = getLoginUser();
        if (in_array($user, array_diff(C('QA_TESTER'), ['ylh']))) {
            $map['draw'] = $user;
        }
        $weekly = M('tp_weekly')->where(array())->order('end desc')->select();
        $this->assign('weekly', $weekly);

        if ($id) {//查历史
            $week = M('tp_weekly')->find($id);
            $this->assign('week', $week);
            $data = M('tp_weekly_detail')->where(array())->select();
        } else {
            $this->assign('week', $weekly[0]);
            $table = 'tp_project_pending';
            //本周工作
            $map = array('deleted' => '0');
            $map['status'] = array('in', ['0', '1']);
            $map['issuestatus'] = array('not in', ['1', '10000', '10015', '10016']);
            $list = $this->getDictList('test_group');
            foreach ($list as $vo) {
                $map['pgroup'] = $vo['key'];
                $data = getList($table, $map, 'status desc,issuestatus');
                $this->assign('data' . $vo['key'], $data);
            }
            //下周计划
            $map['status'] = '0';
            $map['issuestatus'] = array('not in', ['1', '10002', '10015']);
            foreach ($list as $vo) {
                $map['pgroup'] = $vo['key'];
                $data = getList($table, $map, 'status desc,issuestatus');
                $this->assign('doing' . $vo['key'], $data);
            }
        }
        $this->display();
    }

}