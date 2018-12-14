<?php

namespace Jira\Controller;
class OvertimeController extends WebInfoController
{
    public function index()
    {
        $where = array('deleted' => '0');
        $data = M('tp_overtime')->where($where)->select();
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
        $url = '/' . C(PRODUCT) . '/Overtime/mine/';
        cookie('url',$url,array('prefix'=>C(PRODUCT).'_'));
        $this->isLogin();

        $user=cookie(C(PRODUCT).'_user');
        $user=jie_mi($user);
        $this->assign('user', $user);

        $m = M('tp_overtime');
        $where = array('user' => $user);
        $where['type'] = '1';
        $jiab = $m->where($where)->order('riqi desc')->limit(10)->select();
        $jiabNum = $m->where($where)->count();
        $this->assign('arr', $jiab);

        $jiabHour = $m->where($where)->sum('hourlong');

        $where['type'] = '2';
        $tiaox = $m->where($where)->order('riqi desc')->limit(10)->select();
        $tiaoxNum = $m->where($where)->count();
        $this->assign('tiaox', $tiaox);

        $tiaoxHour = $m->where($where)->sum('hourlong');
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
        $meals = $this->select($shif, 'meals', 1);
        $this->assign("meals", $meals);
        $taxi = $this->select($shif, 'taxi', 0);
        $this->assign("taxi", $taxi);


        $this->display();
    }

    public function xiangq()
    {
        $type = I('type');
        $this->assign('type', $type);
        $user=cookie(C(PRODUCT).'_user');
        $user=jie_mi($user);
        $user = I('user', $user);
        $this->assign('user', $user);
        $where = array('user' => $user, 'type' => I('type'));
        $data = M('tp_overtime')->where($where)->order('riqi desc')->select();
        $this->assign('data', $data);

        $this->display();
    }

    public function detailed()
    {
        $user = I('user');
        $this->assign('user', $user);
        $where = array('user' => $user);
        $where['riqi'] = array('gt', '2018-4-10');
        $data = M('tp_overtime')->where($where)->order('riqi desc')->select();
        $this->assign('data', $data);

        $this->display();
    }

    //添加记录
    function add()
    {
        //判断必填
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