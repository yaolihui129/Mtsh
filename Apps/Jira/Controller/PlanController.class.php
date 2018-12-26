<?php
namespace Jira\Controller;
class PlanController extends WebInfoController
{
    //测试计划列表
    public function index()
    {
        cookie('type_plan',I('type','doing'),array('prefix'=>C('PRODUCT').'_'));
        $tpye=cookie(C('PRODUCT').'_type_plan');
        if ($tpye == 'online') {
            $where['issuestatus'] = array('in', '3,10002,10011,6');
            $where['order']='UPDATED desc';
            $where['page']=I('page',1);
            $where['size']=30;
        }elseif ($tpye == 'done'){
            $where['issuestatus'] = array('in','10004,10107');
        } else {
            $where['issuestatus'] = array('not in', '3,10002,10011,10004,10107,6');
            $where['order']='issuestatus desc,ASSIGNEE';
        }

        $project=cookie(C('PRODUCT').'_project');
        if($project ==10006){
            cookie('testGroup',I('testGroup','one'),array('prefix'=>C('PRODUCT').'_'));
            $testGroup=cookie(C('PRODUCT').'_testGroup');
            $this->assign('testGroup', $testGroup);

            if($testGroup=='one'){
                $where['CREATOR'] =array('in',C('QA_TESTER'));
            }else{
                $where['CREATOR'] =array('in','qinzhenxia,congtianyue,zhangaonan');
            }
        }
        $where['PROJECT'] = intval($project);
        $where['issuetype'] = '10102';
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['SUMMARY|issuenum'] = array('like', '%' . $search . '%');
        //同步到本地库
        $this->synchJiraIssue(postIssue($where));


        //从本地库查询
        $where['project'] = intval($project);
        $testGroup=cookie(C('PRODUCT').'_testGroup');
        if($testGroup=='one'){
            $where['reporter'] =array('in',C('QA_TESTER'));
        }else{
            $where['reporter'] =array('in','qinzhenxia,congtianyue,zhangaonan');
        }
        $where['summary|pkey'] = array('like', '%' . $search . '%');
        $table= 'tp_jira_issue';
        $order='issuestatus desc,assignee';
        $this->assign('data', getList($table,$where,$order));


        $this->display();
    }
    //更改测试计划类型
    function change_type(){
        $project=cookie(C('PRODUCT') .'project');
        $testGroup=cookie(C('PRODUCT') .'testGroup');
        $url = '/' . C('PRODUCT') . '/Plan/index/project/'.$project.'/testGroup/'.$testGroup;
        cookie('url',$url,array('prefix'=>C('PRODUCT').'_'));
        $this->isLogin();
        $_GET['plantype']=I('plantype');
        $_GET['table']='tp_jira_issue';
        $this->update();
    }
    //更改测试计划类型
    function change_type1(){
        $table='tp_project_plan_extend';
        $_GET['ptype']=I('ptype');
        $extend = find($table,I('id'));
        $_GET['table']=$table;
        if($extend){
            $this->update();
        }else{
            $this->insert();
        }
    }

    public function gongneng(){
        $tp = I('tp');
        //获取计划详情
        $this->getPlanInfo($tp);
        //获取计划周期
        $cycle=getCyclePlan($tp);
        $this->assign('cycle', $cycle);

        $this->display();

    }

    public function api(){
        $tp = I('tp');
        //获取计划详情
        $this->getPlanInfo($tp);
        //获取计划周期
        $cycle=getCyclePlan($tp);
        $this->assign('cycle', $cycle);

        $map=array('deleted'=>'0','planid'=>$tp);
        $api=M('tp_project_plan_api')->where($map)->select();
        $this->assign('api', $api);

        $this->display();
    }

    public function xingneng(){
        $tp = I('tp');
        //获取计划详情
        $this->getPlanInfo($tp);
        //获取计划周期
        $cycle=getCyclePlan($tp);
        $this->assign('cycle', $cycle);

        $topology='<p>压力产生器连接服务端系统，客户端发送请求到服务端，服务端响应并处理后将结果返回到客户端。
            本次测试环境的网络环境为1000Mbps局域网（腾讯云内部），使用独立的网段，忽略防火墙延迟，
            交易请求一级结果返回的网络传输时间可以忽略不计。<br>
            简图如下：
            </p>';
        $target='<p>通过对XXX系统的性能测试实施，在测试范围内可以达到如下目的：
                    <ul>
                        <li>了解XXX系统在各个业务场景下的性能表现；</li>
                        <li>了解XXX业务系统的稳定性；</li>
                        <li>通过各个业务场景的测试实施，为系统调优提供数据参考；</li>
                        <li>通过性能测试发现系统瓶颈，并进行优化</li>
                    </ul>
                </p>';
        $range=' XXX系统说明记忆系统业务介绍和需要测试的业务模块，业务逻辑图如下：';
        $response_time='50-200-500ms';
        $success_rate='99.9%';
        $throughput='';
        $resource_utilization_rate='80%';
        $begin='';
        $end='';
        $per_scheme=$this->dictList('per_scheme','per_scheme','2');

        $map=array('deleted'=>'0','planid'=>$tp);
        $api=M('tp_project_plan_api')->where($map)->select();
        $this->assign('api', $api);
        $map=array('type'=>'1','deleted'=>'0','project'=>$tp);
        $tools=M('tp_project_tool_term')->where($map)->select();
        $this->assign('tools', $tools);
        $map=array('type'=>'0','deleted'=>'0','project'=>$tp);
        $term=M('tp_project_tool_term')->where($map)->select();
        $this->assign('term', $term);

//        if($extend['status']){
//            $topology=trim($extend['topology']);
//            $target=$extend['target'];
//            $range=$extend['range'];
//            $response_time=$extend['response_time'];
//            $success_rate=$extend['success_rate'];
//            $throughput=$extend['throughput'];
//            $resource_utilization_rate=$extend['resource_utilization_rate'];
//            $begin=$extend['begin'];
//            $end=$extend['end'];
//            $per_scheme=$this->dict_list('per_scheme','per_scheme',$extend['per_scheme']);
//        }
        $this->assign("topology", $topology);
        $this->assign("target",PublicController::editor("target",$target,'desc',120));
        $this->assign("range", $range);
        $this->assign('response_time', $response_time);
        $this->assign('success_rate', $success_rate);
        $this->assign('throughput', $throughput);
        $this->assign('resource_utilization_rate', $resource_utilization_rate);
        $this->assign('begin', $begin);
        $this->assign('end', $end);
        $this->assign('per_scheme', $per_scheme);

        $this->display('pressure');
    }
    //考核积分
    public function appraisal(){
        $user = ['ylh', 'cf'];
        $dissent = array(
            array('key' => '1', 'value' => '允许申诉'),
            array('key' => '0', 'value' => '不允许申诉'),
        );
        $tester = C('QA_TESTER');
        $_SESSION['Appraisal']['tester'] = I('tester', $tester[0]);
        $this->assign("tester", $tester);
        if (in_array(getLoginUser(), $user)) {
            $project=array();
            $map['status']=array('in','1,2');
            $pending=getList('tp_project_pending',$map);
            foreach ($pending as $k => $pend){
                $project[$k]['key']=$pend['id'];
                $project[$k]['value']='【'.$pend['pkey'].'】'.$pend['pname'].'('.$pend['id'].')';
            }
            $this->assign("project", $project);
            $tp=I('project');
            $this->assign("p", $tp);

            if($tp){
                $data=getIssue($tp);
                $_SESSION['Appraisal']['project'] = '【'.$_SESSION['pkey']. $data['issuenum'] .'】'.$data['summary'] ;
                $case = getPlanCase($tp);
                $case_id_arr=array();
                if ($case) {
                    foreach ($case as $ca) {
                        $case_id_arr[] = $ca['tc_id'];
                    }
                    $where['ID'] = array('in', $case_id_arr);
                    $where['PRIORITY'] = '1';
                    $func = postIssue($where);
                    $this->assign('c', sizeof($func));
                } else {
                    $this->assign('c', 0);
                }
                //查询测试人员
                $data=getCyclePlan($tp);
                $ceshiren=array();
                foreach ($data as $da){
                    $case = getCycleTestRun($da['id']);
                    foreach ($case as $ca){
                        if($ca['executed_by']){
                            if(!in_array($ca['executed_by'], $ceshiren)){
                                $ceshiren[]=$ca['executed_by'];
                            }
                        }
                    }
                }
                $this->assign("ceshiren", $ceshiren);
            }else{
                $_SESSION['Appraisal']['project'] = '【未关联】迭代或项目';
            }
            $this->assign("score", sumScore($_SESSION['Appraisal']['tester'], C('KH_QUARTER')));
            $this->assign("quarter", C('KH_QUARTER'));

            //封装加分项下拉
            $where = array('project' => '0', 'type' => '1');
            $map  = array('project' => '0', 'type' => '2' );
            $dissent = select($dissent, 'dissent', '0');
            $var = array(
                'quarter' => C('KH_QUARTER'),
                'user' => $_SESSION['Appraisal']['tester']
            );
            if ($tp) {
                $where['project'] = '1';
                $map['project'] = '1';
                $dissent = select($dissent, 'dissent', '1');
                $var['issueid'] =$tp;
            }
            //人员积分明细
            $table='tp_my_score';
            $this->assign("data", getList($table,$var,'ctime desc'));
            $this->assign("count", countId($table,array('status' => '1')));
            $table='tp_score_rules';
            $jiaF = getList($table,$where);
            $jiaFen=array();
            foreach ($jiaF as $jia) {
                $jiaFen[] = array(
                    'key' => $jia['id'],
                    'value' => '【' . $jia['cate'] . '】' . $jia['name'] . ' +' . $jia['score']
                );
            }
            $this->assign("jiaFen", select($jiaFen, 'rules','0'));
            //封装减分项下拉
            $jianF = getList($table,$map);
            $jianFen=array();
            foreach ($jianF as $jian) {
                $jianFen[] = array(
                    'key' => $jian['id'],
                    'value' => '【' . $jian['cate'] . '】' . $jian['name'] . ' -' . $jian['score']
                );
            }
            $this->assign("jianFen", select($jianFen, 'rules','0'));
            //封装允许申诉下拉
            $this->assign("dissent", $dissent);
        } else {
            $this->error('你没有权限访问此功能!');
        }

        $this->display();
    }
    //申诉列表
    public function appeal()
    {
        $table='tp_my_score';
        $where = array('status' => '1');
        $this->assign("data", getList($table,$where));
        $this->assign("acount", countId($table,$where));
        $where = array('quarter' => C('KH_QUARTER'), 'status' => '2');
        $this->assign("dcount", countId($table,$where));
        $where = array('quarter' => C('KH_QUARTER'), 'status' => '3');
        $this->assign("rcount", countId($table,$where));

        $this->display();
    }
    //申诉已完成
    public function done()
    {
        $table='tp_my_score';
        $where = array('quarter' => C('KH_QUARTER'), 'status' => '2');
        $this->assign("data", getList($table,$where));
        $this->assign("dcount", countId($table,$where));
        $where = array('status' => '1', 'deleted' => '0');
        $this->assign("acount", countId($table,$where));
        $where = array('quarter' => C('KH_QUARTER'), 'status' => '3');
        $this->assign("rcount", countId($table,$where));

        $this->display();
    }
    //申诉被驳回
    public function reject()
    {
        $table='tp_my_score';
        $where = array('quarter' => C('KH_QUARTER'), 'status' => '3');
        $this->assign("data", getList($table,$where));
        $this->assign("rcount", countId($table,$where));
        $this->assign("dcount", countId($table,$where));
        $where = array('status' => '1');
        $this->assign("acount", countId($table,$where));

        $this->display();
    }
    //插入数据
    function charu()
    {
        if (!I('score')) {
            $data = find('tp_score_rules',I('rules'));
            $_POST['score'] = $data['score'];
        }
        $m = D('tp_my_score');
        $_POST['adder'] = getLoginUser();
        $_POST['moder'] = getLoginUser();
        $_POST['ctime'] = time();
        if (!$m->create()) {
            $this->error($m->getError());
        }
        if ($m->add()) {
            if ($this->updateList($_POST['user'], $_POST['quarter'])) {
                $this->success("成功");
            } else {
                $this->error("排行榜更新失败");
            }
        } else {
            $this->error("失败");
        }
    }
    //驳回
    function bohui()
    {
        $_GET['status'] = 3;
        $_GET['table']='tp_my_score';
        $this->update();
    }
    //更新积分排行
    function updateList($user, $quarter)
    {
        $table='tp_score_list';
        $score = sumScore($user, $quarter);
        $where = array('user' => $user, 'quarter' => $quarter);
        $arr= getList($this,$where);
        //2.判断是否有值
        if ($arr) {
            //3.有值更新人员积分
            $_GET['id'] = $arr[0]['id'];
            $_GET['score'] = $score;
            $id=update($table,$_GET);
        } else {
            //4.无值插入人员积分
            $_GET['user'] = $user;
            $_GET['score'] = $score;
            $_GET['quarter'] = $quarter;
            $id=insert($table,$_GET);
        }
        return $id;
    }

    //计划详情
    public function detail(){
        $tp = I('tp');
        //1.获取测试计划详情
        $_SESSION['testPlan'] = getIssue($tp);
        if($_SESSION['testPlan']['assignee']==getLoginUser()){
            $editable=1;
        }else{
            $editable=0;
        }
        $this->assign('editable', $editable);
        $this->assign('plan', $_SESSION['testPlan']);

        $extend = find('tp_project_plan_extend',$tp);
        $this->assign('extend', $extend);

        $var=$this->getDictInfo('per_scheme',$extend['per_scheme']);
        $per_scheme=json_decode($var['json']);
        $list=$this->getDictList('press_type');

        foreach ($list as $key=>$vo){
            if (in_array($key,$per_scheme)){
                $where=array('press_type'=>$key,'selected'=>'1','project'=>$tp);
                $scene = getList('tp_api_scene_pressure',$where,'api');
                $a=true;
            }else{
                $scene=array();
                $a=false;
            }
            $this->assign('extend_scheme'.$key, $a);
            $this->assign('scene'.$key, $scene);
        }
        //4.获取测试计划下的测试周期
        $this->assign('data', getCyclePlan($tp));
        if($extend['ptype']=='2'){
            //性能测试
            $this->assign('api', getList('tp_project_plan_api',array('type'=>'1','planid'=>$tp)));
            $this->assign('tools', getList('tp_project_tool_term',array('type'=>'1','planid'=>$tp)));
            $this->assign('term', getList('tp_project_tool_term',array('type'=>'0','planid'=>$tp)));
            if($extend['status']){
                $topology=trim($extend['topology']);
                $target=$extend['target'];
                $range=$extend['range'];
                $response_time=$extend['response_time'];
                $success_rate=$extend['success_rate'];
                $throughput=$extend['throughput'];
                $resource_utilization_rate=$extend['resource_utilization_rate'];
                $begin=$extend['begin'];
                $end=$extend['end'];
                $per_scheme=dictList('per_scheme','per_scheme',$extend['per_scheme']);
            }else{
                $topology='<p>压力产生器连接服务端系统，客户端发送请求到服务端，服务端响应并处理后将结果返回到客户端。
            本次测试环境的网络环境为1000Mbps局域网（腾讯云内部），使用独立的网段，忽略防火墙延迟，
            交易请求一级结果返回的网络传输时间可以忽略不计。<br>
            简图如下：
            </p>';
                $target='<p>通过对XXX系统的性能测试实施，在测试范围内可以达到如下目的：
                    <ul>
                        <li>了解XXX系统在各个业务场景下的性能表现；</li>
                        <li>了解XXX业务系统的稳定性；</li>
                        <li>通过各个业务场景的测试实施，为系统调优提供数据参考；</li>
                        <li>通过性能测试发现系统瓶颈，并进行优化</li>
                    </ul>
                </p>';
                $per_scheme=dictList('per_scheme','per_scheme','2');
                $range=' XXX系统说明记忆系统业务介绍和需要测试的业务模块，业务逻辑图如下：';
                $response_time='50-200-500ms';
                $success_rate='99.9%';
                $throughput='';
                $resource_utilization_rate='80%';
                $begin='';
                $end='';
            }
            $this->assign("topology", $topology);
            $this->assign("target",PublicController::editor("target",$target,'desc',120));
            $this->assign("range", $range);
            $this->assign('response_time', $response_time);
            $this->assign('success_rate', $success_rate);
            $this->assign('throughput', $throughput);
            $this->assign('resource_utilization_rate', $resource_utilization_rate);
            $this->assign('begin', $begin);
            $this->assign('end', $end);
            $this->assign('per_scheme', $per_scheme);

            $this->display('pressure');

        }elseif ($extend['ptype']=='1'){
            //Api测试
            $this->assign('api', getList('tp_project_plan_api',array('type'=>'0','planid'=>$tp)));

            $this->display('api');
        }else{
            //2.获取测试关联的测试用例
            $_SESSION['testCase'] = getPlanCase($tp);
            $this->assign('case', $_SESSION['testCase']);
            if ($_SESSION['testCase']) {
                $count = sizeof($_SESSION['testCase']);
                $case_id_arr=array();
                foreach ($_SESSION['testCase'] as $ca) {
                    $case_id_arr[] = $ca['tc_id'];
                }
                //3.获取测试功能点（测试范围）
                $where['ID'] = array('in', $case_id_arr);
                $where['PRIORITY'] = '1';
                $_SESSION['testfunc']=postIssue($where);
                $this->assign('func', $_SESSION['testfunc']);
                $this->assign('c', sizeof($_SESSION['testfunc']));
            } else {
                $count = 0;
                $this->assign('c', 0);
            }
            $this->assign('count', $count);
            //5.获取本次迭代的BUG
            $_SESSION['testBug'] = getPlanBug($tp);
            $_SESSION['bug_num']=sizeof($_SESSION['testBug']);
            if ($_SESSION['testBug']) {
                foreach ($_SESSION['testBug'] as $b) {
                    $bug_id[] = $b['id'];
                }
            }
            //6.获取遗留BUG
            $where = array();
            $where['tp']=$tp;
            $where['issuestatus'] = array('not in', '6');
            $_SESSION['testYiliu']=postPlanBug($where);
            $_SESSION['bug_yiliu']=sizeof($_SESSION['testYiliu']);
            $xiufl_num=($_SESSION['bug_num']-$_SESSION['bug_yiliu'])/$_SESSION['bug_num'];
            $xiufl_num=round($xiufl_num,4)*100;
            $_SESSION['xiufl_num']=$xiufl_num;

            //7.P0级别的bug的数量
            $where = array();
            $where['tp']=$tp;
            $where['PRIORITY'] = '1';
            $_SESSION['p0_num']=sizeof(postPlanBug($where));

            //8.P1级别的bug的数量
            $where['PRIORITY'] = '2';
            $_SESSION['p1_num']=sizeof(postPlanBug($where));

            //9.P2级别的遗留BUG数量
            $where['PRIORITY'] = '3';
            $where['issuestatus'] = array('not in', '6');
            $_SESSION['p2_num']=sizeof(postPlanBug($where));

            //10.P3级别的遗留BUG数量
            $where['PRIORITY'] = '4';
            $_SESSION['p3_num']=sizeof(postPlanBug($where));

            //11.P4级别的遗留BUG数量
            $where['PRIORITY'] = '5';
            $_SESSION['p4_num']=sizeof(postPlanBug($where));

            $this->display();
        }
    }

    public function tcase()
    {
        $tp = I('tp');
        if (!$_SESSION['testPlan']) {
            $_SESSION['testPlan'] = getIssue($tp);
        }
        $this->assign('plan', $_SESSION['testPlan']);

        if(!$_SESSION['testCase']){
            $_SESSION['testCase'] = getPlanCase($tp);
        }
        $this->assign('case', $_SESSION['testCase']);

        $this->display();
    }

    public function bug()
    {
        $tp = I('tp');
        if (!$_SESSION['testPlan']) {
            $_SESSION['testPlan'] = getIssue($tp);
        }
        $this->assign('plan', $_SESSION['testPlan']);

        if(!$_SESSION['testBug']){
            $_SESSION['testBug'] = getPlanBug($tp);
        }
        $this->assign('bug', $_SESSION['testBug']);

        $this->display();
    }

    public function yiliu()
    {
        $tp=I('tp');
        if (!$_SESSION['testPlan']) {
            $_SESSION['testPlan'] = getIssue($tp);
        }
        $this->assign('plan', $_SESSION['testPlan']);

        if(!$_SESSION['testYiliu']){
            $where = array();
            $where['tp']=$tp;
            $where['issuestatus'] = array('not in', '6');
            $where['order']='PRIORITY';
            $_SESSION['testYiliu'] = postPlanBug($where);
        }
        $this->assign('yiliu', $_SESSION['testYiliu']);

        $this->display();
    }

    //未提测
    public function unsubm(){
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


    function upload_range(){
        $table='tp_project_plan_extend';
        $this->dataUpdate($table,'TestPlan',$_POST,'range_image');
    }

    function upload_topology(){
        $table='tp_project_plan_extend';
        $this->dataUpdate($table,'TestPlan',$_POST,'topology_image');
    }

    function set_scheme(){
        $_POST['status']='1';
        $_POST['table']='tp_project_plan_extend';
        $this->update();
    }

    //选择被测接口
    public function choice_api(){
        $table='tp_project_plan_api';
        $map=array('type'=>'1','planid'=>$_SESSION['testPlan']['id']);
        $api = getList($table,$map);
        $this->assign('api', $api);

        $search = I('search');
        $this->assign('search', $search);
        $where = array();
        $where['apiName|apiURI'] = array('like', '%' . $search . '%');
        $where['apiName'] = array('neq', '示例接口');
        if($api){
            $arrApi=array();
            foreach ($api as $a){
                $arrApi[]=$a['api'];
            }
            $where['apiID']  = array('not in',$arrApi);
        }
        $this->assign('project', $this->projectID());
        $where['projectID']=I('branch','2');
        $this->assign('branch', $where['projectID']);
        $table='eo_api';
        $this->assign('data', getList($table,$where));

        $this->display();
    }
    //编辑被测接口
    public function mod_api(){
        $table='tp_project_plan_api';
        $this->assign('api', find($table,I('id')));
        $where=array('planid'=>$_SESSION['testPlan']['id'],'type'=>'1');
        $this->assign('data', getList($table,$where));

        $this->display();
    }

    //标记性能
    function pressure(){
        $_SESSION['url'] = '/' . C('PRODUCT') . '/Plan/choice_api/branch/'.$_GET['branch'];
        $this->isLogin();
        $_GET['table']='eo_api';
        if($_GET['pressure']=='0'){
            $_GET['pressure']='1';
            $this->update();
        }elseif ($_GET['pressure']=='1'){
            $_GET['pressure']='0';
            $this->update();
        }else{
            $this->error('性能标识非法');
        }
    }

    //撤销选择
    function chexiao(){
        $_GET['deleted']='1';
        $_GET['table']='tp_project_plan_api';
        $this->update();
    }

    //标记性能
    function jiaru(){
        if($_GET['pressure']){
            $where=$_GET;
            $_GET['table']='tp_project_plan_api';
            $data= findOne($_GET['table'],$where);
            if($data){
                $_GET['id']=$data['id'];
                $_GET['deleted']='0';
                $this->update();
            }else{
                $this->insert();
            }
        }else{
            $this->error('请先标记性能（Y）');
        }

    }

    //添加工具和术语
    function add_term(){
        $_POST['table']='tp_project_tool_term';
        $this->insert();
    }

    //移除工具和术语
    function del_term(){
        $_GET['table']='tp_project_tool_term';
        $this->del();
    }

    //测试执行记录
    public function run(){
        $cyc = I('cyc');
        //1.获取测试周期详情
        $this->assign('cycle', getCycle($cyc));
        //2.获取测试关联的测试用例
        $this->assign('case', getCycleRun($cyc));

        $this->display();
    }


}