<?php
namespace Jira\Controller;
class RiskController extends WebInfoController
{
    public function index()
    {
        $table='tp_risk';
        $where = array('project' => I('project', getCache('project')));
        $this->assign("data", getList($table,$where));

        $this->display();
    }

    public function risk()
    {
        $table='tp_risk';
        $where = array('project' => I('project', getCache('project')));
        $this->assign("risks", getList($table,$where));

        $count = countId($table,$where);
        $this->assign('c', $count+1);
        $this->assign("state", select("打开", "state", "rstate"));
        $this->assign("level", select("C", "level", "risklevel"));
        $this->assign("tamethod", PublicController::editor("amethod", "暂无方案"));

        $this->display();
    }

    public function mod1()
    {
        $table='tp_risk';
        $where = array('project' => I('project', getCache('project')));
        $this->assign("data", getList($table,$where));

        $risk = find($table,I('id'));
        $this->assign("risk", $risk);
        $this->assign("level", select($risk['level'], "level", "risklevel"));
        $this->assign("state", select($risk['state'], "state", "rstate"));
        $this->assign("tamethod", PublicController::editor("amethod", $risk['amethod']));


        $this->display();
    }



    //未提测
    public function unSubm(){
        $planid=I('planid');
        $this->assign('planid', $planid);
        $where=array('planid'=>$planid,'type'=>'2');
        $data = findOne('tp_risk',$where);
        $this->assign('data', $data);

        //5.提测打回邮件
        $sendTo = $_SESSION['testPlan']['assignee'] . '@zhidaoauto.com';
        $this->assign('sendTo', $sendTo);
        $cc = 'ylh@zhidaoauto.com;cf@zhidaoauto.com';
        $this->assign('cc', $cc);
        $subject = $_SESSION['testPlan']['summary'] . '，未按计划时间提交测试，会造成项目可能延期的风险';
        $this->assign('subject', $subject);
        $body = '原计划XX月XX日（上午上班前\下午下班前）提测<br>';

        $body.= '截止到  XXX  仍未收到提测信息，因此会造成项目可能延期的风险!<br>
                <br>
                请相关责任人务必回复邮件说明原因，并明确给出可以提测的时间节点。<br>';
        if($data){
            switch ($data['mod'])
            {
                case 1:
                    $this->assign("body",PublicController::editor("body",$data['body']));
                    break;
                default:
                    $this->assign("body",PublicController::editor("body",$data['body']));
            }
        }else{
            $this->assign("body",PublicController::editor("body",$body));
        }
        $this->display();
    }
    //提测打回
    public function repulse()
    {
        $planid=I('planid');
        $this->assign('planid', $planid);
        $where=array('planid'=>$planid,'type'=>'0');
        $data = findOne('tp_risk',$where);
        $this->assign('data', $data);

        //2.获取测试关联的测试用例
        $case = getPlanCase($planid);
        $case_id_arr=array();
        foreach ($case as $ca) {
            $case_id_arr[] = $ca['tc_id'];
        }
        //3.获取测试功能点（测试范围）
        $where['ID'] = array('in', $case_id_arr);
        $where['PRIORITY'] = '1';
        $func =postIssue($where);
        $this->assign('func', $func);
        $str='';
        foreach ($func as $f){
            $str.='<li><small><span class="label label-danger">未通过</span>【'.$_SESSION['pkey'].'-'.$f['issuenum'].'】'.$f['summary'].'</small></li>';
        }
        //5.提测打回邮件
        $sendTo = $_SESSION['testPlan']['assignee'] . '@zhidaoauto.com';
        $this->assign('sendTo', $sendTo);
        $cc = 'ylh@zhidaoauto.com;cf@zhidaoauto.com';
        $this->assign('cc', $cc);
        $subject = $_SESSION['testPlan']['summary'] . '，测试准入验收未通过，提测版本打回，有项目可能延期的风险';
        $this->assign('subject', $subject);
        $body = '在按照给出的冒烟测试标准进行测试准入验收时，发现有部分冒烟用例测试未通过<br>
                附上冒烟用例测试结果：<br>
                ';
        if(!$data['mod']){
            $body.= '<ol>'.$str.'</ol>';
        }else{
            $body.='<small>（请从右侧复制冒烟用例结果到FoxMail的邮件正文中）</small><br>';
        }
        $body.= '主要功能点未全部通过测试，因此无法按照计划进入测试环节，会有项目可能延期的风险<br>
                <br>
                请相关责任人务必回复邮件说明原因，并明确给出可以重新提测的时间节点。<br>';
        if($data){
            switch ($data['mod'])
            {
                case 1:
                    $this->assign("body",PublicController::editor("body",$body));
                    break;
                default:
                    $this->assign("body",PublicController::editor("body",$data['body']));
            }
        }else{
            $this->assign("body",PublicController::editor("body",$body));
        }
        $this->display();
    }
    //编辑状态更新
    function mod(){
        $_GET['mod']='2';
        $_GET['table']='tp_risk';
        $this->update();
    }
    //延期预警
    public function warning()
    {
        $tp=I('planid');
        $this->assign('planid', $tp);
        $where=array('planid'=>$tp,'type'=>'1');
        $data = find('tp_risk',$where);
        $this->assign('data', $data);
        if (!$_SESSION['testPlan']) {
            $_SESSION['testPlan'] = getIssue($tp);;
        }
        if(!$_SESSION['testYiliu']){
            $where = array();
            $where['tp']=$tp;
            $where['issuestatus'] = array('not in', '6');
            $where['order']='PRIORITY';
            $_SESSION['testYiliu'] = postPlanBug($where);
        }
        $this->assign('yiliu', $_SESSION['testYiliu']);
        $str='';
        foreach ($_SESSION['testYiliu'] as $f){
            $str.='<li><small><span class="badge">'
                .getIssueStatus($f['issuestatus'])
                .'</span>【'.$_SESSION['pkey'].'-'.$f['issuenum'].'】'.$f['summary']
                .'</small></li>';
        }
        $mail = $_SESSION['testPlan']['assignee'] . '@zhidaoauto.com';
        $this->assign('sendTo', $mail);
        $cc = 'ylh@zhidaoauto.com;cf@zhidaoauto.com';
        $this->assign('cc', $cc);
        $subject = $_SESSION['testPlan']['summary'] . '，未达到最基本的上线标准，项目已经延期';
        $this->assign('subject', $subject);
        $body = $_SESSION['testPlan']['summary'] . ',<br>
                原计划XX月XX日上线，截止到XX时间<br>'
            . 'Jira中还有BUG没有关闭:<br>';
        if(!$data['mod']){
            $body.= '<ol>'.$str.'</ol>';
        }else{
            $body.='<small>（请从右侧复制遗留BUG列表到FoxMail的邮件正文中）</small><br>';
        }
        $body.='<br>
                完全达不到上线的最基本要求，项目已经延期<br><br>
                请产品经理或其他负责人紧急召开项目会议，商议下一步的解决方案';
        if($data){
            switch ($data['mod'])
            {
                case 1:
                    $this->assign("body",PublicController::editor("body",$body));
                    break;
                default:
                    $this->assign("body",PublicController::editor("body",$data['body']));
            }
        }else{
            $this->assign("body",PublicController::editor("body",$body));
        }
        $this->display();

    }

}