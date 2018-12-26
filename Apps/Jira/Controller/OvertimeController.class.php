<?php
namespace Jira\Controller;
class OvertimeController extends WebInfoController
{
    public function index()
    {
        $data = getList('tp_overtime',array());
        $var = array();
        foreach ($data as $da) {
            if (!in_array(trim($da['user']), $var)) {
                $var[] = trim($da['user']);
            }
        }
        $this->assign('data', $var);
        $this->display();
    }

    public function mine()
    {
        $_SESSION['url'] = '/' . C('PRODUCT') . '/Overtime/mine/';
        $this->isLogin();
        $table='tp_overtime';
        $user=getLoginUser();
        if ($user) {
            $where = array('user' => $user);
            $where['type'] = '1';
            $jiab = getList($table,$where,'riqi desc','',1,10);
            $jiabNum = countId($table,$where);
            $this->assign('arr', $jiab);

            $jiabHour = sum($table,$where,'hourlong');
            $where['type'] = '2';
            $tiaox = getList($table,$where,'riqi desc','',1,10);
            $tiaoxNum = countId($table,$where);
            $this->assign('tiaox', $tiaox);

            $tiaoxHour = sum($table,$where,'hourlong');
            $keyHour = $jiabHour - $tiaoxHour;
            $hour = array($jiabHour, $tiaoxHour, $keyHour, $jiabNum, $tiaoxNum);
            $this->assign('hour', $hour);


            $riqi = date("Y-m-d", time());
            $this->assign('riqi', $riqi);

            $begin = mktime(19, 00);//mktime(hour,minute,second,month,day,year)
            $begin = date('H:i', $begin);
            $this->assign('begin', $begin);


            $tbegin = mktime(9, 00);//mktime(hour,minute,second,month,day,year)
            $tbegin = date('H:i', $tbegin);
            $this->assign('tbegin', $tbegin);

            $end = mktime(21, 00);
            $end = date('H:i', $end);
            $this->assign('end', $end);

            $tend = mktime(18, 00);
            $tend = date('H:i', $tend);
            $this->assign('tend', $tend);

            $shif = array(
                array('key' => 0, 'value' => '否'),
                array('key' => 1, 'value' => '是'),
            );
            //封装下拉列表
            $meals = select($shif, 'meals', 1);
            $this->assign("meals", $meals);
            $taxi = select($shif, 'taxi', 0);
            $this->assign("taxi", $taxi);
        } else {
            $this->assign('data', C('QA_TESTER'));
        }

        $this->display();
    }

    public function xiangq()
    {
        $type = I('type');
        $this->assign('type', $type);
        $user = I('user', getLoginUser());
        $this->assign('user', $user);
        $where = array('user' => $user, 'type' => $type);
        $this->assign('data', getList('tp_overtime',$where,'riqi desc'));

        $this->display();
    }

    public function detailed()
    {
        $user = I('user');
        $this->assign('user', $user);
        $where = array('user' => $user);
        $where['riqi'] = array('gt', '2018-4-10');
        $this->assign('data', getList('tp_overtime',$where,'riqi desc'));

        $this->display();
    }

    //添加记录
    function add()
    {
        if (!I('hourlong')) {//工时必填
            $this->error('时长不能为空');
        } elseif (!I('remark')) {//备注必填
            $this->error('原因不能为空');
        } else {
            $_POST['table']='tp_overtime';
            $this->insert();
        }
    }

}