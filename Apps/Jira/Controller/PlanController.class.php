<?php
namespace Jira\Controller;
class PlanController extends WebInfoController
{
    //测试计划列表
    public function index()
    {
        cookie('type_plan',I('type','doing'),array('prefix'=>C(PRODUCT).'_'));
        $tpye=cookie(C(PRODUCT).'_type_plan');
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

        $project=cookie(C(PRODUCT).'_project');
        if($project ==10006){
            cookie('testGroup',I('testGroup','one'),array('prefix'=>C(PRODUCT).'_'));
            $testGroup=cookie(C(PRODUCT).'_testGroup');
            $this->assign('testGroup', $testGroup);

            if($testGroup=='one'){
                $where['CREATOR'] =array('in',C(QA_TESTER));
            }else{
                $where['CREATOR'] =array('in','qinzhenxia,congtianyue,zhangaonan');
            }

        }
        $where['PROJECT'] = intval($project);
        $where['issuetype'] = '10102';
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['SUMMARY|issuenum'] = array('like', '%' . $search . '%');
        $url = C(JIRAPI) . "/Jirapi/issue";
        $data = httpJsonPost($url, json_encode($where));
        //同步到本地库
        $this->synch_Jira_issue($data);


        //从本地库查询
        $where['project'] = intval($project);
        if($testGroup=='one'){
            $where['reporter'] =array('in',C(QA_TESTER));
        }else{
            $where['reporter'] =array('in','qinzhenxia,congtianyue,zhangaonan');
        }
        $where['summary|pkey'] = array('like', '%' . $search . '%');
        $data=M('tp_jira_issue')->where($where)->order('issuestatus desc,assignee')->select();

        $this->assign('data', $data);


        $this->display();
    }


    //更改测试计划类型
    function change_type(){
        $project=cookie(C(PRODUCT) .'project');
        $testGroup=cookie(C(PRODUCT) .'testGroup');
        $url = '/' . C(PRODUCT) . '/Plan/index/project/'.$project.'/testGroup/'.$testGroup;
        cookie('url',$url,array('prefix'=>C(PRODUCT).'_'));
        $this->isLogin();
        $_GET['plantype']=I('plantype');
        $_GET['table']='tp_jira_issue';
        $this->update();
    }

    public function gongneng(){
        $tp = I('tp');
        //获取计划详情
        $this->getPlanInfo($tp);
        //获取计划周期
        $this->getPlanCycle($tp);

    }

    public function api(){
        $tp = I('tp');
        //获取计划详情
        $this->getPlanInfo($tp);
        //获取计划周期
        $this->getPlanCycle($tp);

        $map=array('deleted'=>'0','planid'=>$tp);
        $api=M('tp_project_plan_api')->where($map)->select();
        $this->assign('api', $api);

        $this->display('api');
    }

    public function xingneng(){
        $tp = I('tp');
        //获取计划详情
        $this->getPlanInfo($tp);
        //获取计划周期
        $this->getPlanCycle($tp);

        $topology='<p>压力产生器连接服务端系统，客户端发送请求到服务端，服务端响应并处理后将结果返回到客户端。
            本次测试环境的网络环境为1000Mbps局域网（腾讯云内部），使用独立的网段，忽略防火墙延迟，
            交易请求一级结果返回的网络传输时间可以忽略不计。<br \>
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
        $per_scheme=$this->dict_list('per_scheme','per_scheme','2');

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
        $tester = C(QA_TESTER);
        cookie('Appraisal_tester',I('tester', $tester[0]),array('prefix'=>C(PRODUCT).'_'));
        $this->assign("tester", $tester);
        $d_user=cookie(C(PRODUCT).'_user');
        $d_user=jie_mi($d_user);
        if (in_array($d_user, $user)) {
            $project=array();
            $map['status']=array('in','1,2');
            $pending=M('tp_project_pending')->where($map)->select();
            foreach ($pending as $k => $pend){
                $project[$k]['key']=$pend['id'];
                $project[$k]['value']='【'.$pend['pkey'].'】'.$pend['pname'].'('.$pend['id'].')';
            }
            $this->assign("project", $project);
            $tp=I('project');
            $this->assign("p", $tp);

            if($tp){
                $url = C(JIRAPI) . "/Jirapi/issue/" . $tp;
                $data = httpGet($url);
                $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);

                $Appraisal_project= '【'.cookie(C(PRODUCT).'_pkey'). $data['issuenum'] .'】'.$data['summary'] ;
                cookie('Appraisal_project',$Appraisal_project,array('prefix'=>C(PRODUCT).'_'));

               //获取测试计划id
                $plan=M('tp_project_pending')->find($tp);
                //查询功能点个数
                $url = C(JIRAPI) . "/Jirapi/plancase/" . $plan['planid'] . "/plan";
                $case = httpGet($url);
                $case = json_decode(trim($case, "\xEF\xBB\xBF"), true);
                if ($case) {
                    foreach ($case as $ca) {
                        $case_id_arr[] = $ca['tc_id'];
                    }
                    //3.获取测试功能点（测试范围）
                    $where['ID'] = array('in', $case_id_arr);
                    $where['PRIORITY'] = '1';
                    $url = C(JIRAPI) . "/Jirapi/issue";
                    $func = httpJsonPost($url, json_encode($where));
                    $func = json_decode(trim($func, "\xEF\xBB\xBF"), true);
                    $this->assign('c', sizeof($func));
                } else {
                    $this->assign('c', 0);
                }
                //查询测试人员
                $url = C(JIRAPI) . "/Jirapi/cycle/" . $plan['planid'] . "/plan";
                $data = httpGet($url);
                $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);
                $ceshiren=array();
                foreach ($data as $da){
                    $url = C(JIRAPI) . "/Jirapi/testrun?cycle=" . $da['id'];
                    $case = httpGet($url);
                    $case = json_decode(trim($case, "\xEF\xBB\xBF"), true);
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
                cookie('Appraisal_project','【未关联】迭代或项目',array('prefix'=>C(PRODUCT).'_'));
            }
            $tester=cookie(C(PRODUCT).'_Appraisal_tester');
            $score = sumScore($tester, C(KH_QUARTER));
            $this->assign("score", $score);
            $this->assign("quarter", C(KH_QUARTER));

            //封装加分项下拉
            if ($tp) {
                $where = array('project' => '1', 'type' => '1', 'deleted' => '0');
                $map = array('project' => '1', 'type' => '2', 'deleted' => '0');
                $dissent = $this->select($dissent, 'dissent', '1');
                $var = array(
                    'issueid' => $tp,
                    'quarter' => C(KH_QUARTER),
                    'user' => $tester,
                    'deleted' => '0'
                );
            } else {
                $where = array('project' => '0', 'type' => '1', 'deleted' => '0');
                $map = array('project' => '0', 'type' => '2', 'deleted' => '0');
                $dissent = $this->select($dissent, 'dissent', '0');
                $var = array(
                    'quarter' => C(KH_QUARTER),
                    'user' => $tester,
                    'deleted' => '0'
                );
            }
            //人员积分明细
            $m = M('tp_my_score');
            $data = $m->where($var)->order('ctime desc')->select();
            $this->assign("data", $data);

            $count = $m->where(array('status' => '1', 'deleted' => '0'))->count();
            $this->assign("count", $count);

            $m = M('tp_score_rules');
            $jiaF = $m->where($where)->select();
            foreach ($jiaF as $jia) {
                $jiaFen[] = array(
                    'key' => $jia['id'],
                    'value' => '【' . $jia['cate'] . '】' . $jia['name'] . ' +' . $jia['score']
                );
            }
            $jiaFen = $this->select($jiaFen, 'rules');
            $this->assign("jiaFen", $jiaFen);

            //封装减分项下拉
            $jianF = $m->where($map)->select();
            foreach ($jianF as $jian) {
                $jianFen[] = array(
                    'key' => $jian['id'],
                    'value' => '【' . $jian['cate'] . '】' . $jian['name'] . ' -' . $jian['score']
                );
            }
            $jianFen = $this->select($jianFen, 'rules');
            $this->assign("jianFen", $jianFen);

            //封装允许申诉下拉
            $this->assign("dissent", $dissent);
            $this->display();
        } else {
            $this->error('你没有权限访问此功能!');
        }
    }

    //申诉列表
    public function appeal()
    {
        $where = array('status' => '1', 'deleted' => '0');
        $m = M('tp_my_score');
        $this->assign("data", $m->where($where)->select());
        $this->assign("acount", $m->where($where)->count());
        $where = array('quarter' => C(KH_QUARTER), 'status' => '2', 'deleted' => '0');
        $this->assign("dcount", $m->where($where)->count());
        $where = array('quarter' => C(KH_QUARTER), 'status' => '3', 'deleted' => '0');
        $this->assign("rcount", $m->where($where)->count());
        $this->display();
    }

    //申诉已完成
    public function done()
    {
        $where = array('quarter' => C(KH_QUARTER), 'status' => '2', 'deleted' => '0');
        $m = M('tp_my_score');
        $this->assign("data", $m->where($where)->select());
        $this->assign("dcount", $m->where($where)->count());
        $where = array('status' => '1', 'deleted' => '0');
        $this->assign("acount", $m->where($where)->count());
        $where = array('quarter' => C(KH_QUARTER), 'status' => '3', 'deleted' => '0');
        $this->assign("rcount", $m->where($where)->count());
        $this->display();
    }

    //申诉被驳回
    public function reject()
    {
        $where = array('quarter' => C(KH_QUARTER), 'status' => '3', 'deleted' => '0');
        $m = M('tp_my_score');
        $this->assign("data", $m->where($where)->select());
        $this->assign("rcount", $m->where($where)->count());
        $this->assign("dcount", $m->where($where)->count());
        $where = array('quarter' => C(KH_QUARTER), 'status' => '3', 'deleted' => '0');
        $where = array('status' => '1', 'deleted' => '0');
        $this->assign("acount", $m->where($where)->count());
        $this->display();
    }

    //插入数据
    function charu()
    {
        if (!I('score')) {
            $data = M('tp_score_rules')->find(I('rules'));
            $_POST['score'] = $data['score'];
        }
        $m = D('tp_my_score');
        $user=cookie(C(PRODUCT).'_user');
        $user=jie_mi($user);
        $_POST['adder'] = $user;
        $_POST['moder'] = $user;
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
        $_GET['table'] = 'tp_my_score';
        $this->update();
    }

    //更新积分排行
    function updateList($user, $quarter)
    {
        //1.查询list
        $score = sumScore($user, $quarter);
        $where = array('user' => $user, 'quarter' => $quarter, 'deleted' => '0');
        $m = D('tp_score_list');
        $arr = $m->where($where)->select();
        $d_user=cookie(C(PRODUCT).'_user');
        $d_user=jie_mi($d_user);
        //2.判断是否有值
        if ($arr) {
            //3.有值更新人员积分
            $_GET['id'] = $arr[0]['id'];
            $_GET['score'] = $score;
            $_GET['moder'] = $d_user;
            $id = $m->save($_GET);
        } else {
            //4.无值插入人员积分
            $_GET['user'] = $user;
            $_GET['score'] = $score;
            $_GET['quarter'] = $quarter;
            $_GET['adder'] = $d_user;
            $_GET['moder'] = $d_user;
            $_GET['ctime'] = time();
            if (!$m->create($_GET)) {
                $this->error($m->getError());
            }
            $id = $m->add($_GET);
        }
        return $id;
    }

    //计划详情
    public function detail(){
        //1.获取测试计划详情
        $tp = I('tp');
        //获取计划详情
        $this->getPlanInfo($tp);
        //获取计划周期
        $this->getPlanCycle($tp);
        //2.获取测试关联的测试用例
        $url = C(JIRAPI) . "/Jirapi/plancase/" . $tp . "/plan";
        $case = httpGet($url);
        $case = json_decode(trim($case, "\xEF\xBB\xBF"), true);
        cookie('testCase',$case,array('prefix'=>C(PRODUCT).'_'));
        $this->assign('case', $case);
        if ($case) {
            $count = sizeof($case);
            foreach ($case as $ca) {
                $case_id_arr[] = $ca['tc_id'];
            }
            //3.获取测试功能点（测试范围）
            $where['ID'] = array('in', $case_id_arr);
            $where['PRIORITY'] = '1';
            $url = C(JIRAPI) . "/Jirapi/issue";
            $func = httpJsonPost($url, json_encode($where));
            $func = json_decode(trim($func, "\xEF\xBB\xBF"), true);
            cookie('testfunc',$func,array('prefix'=>C(PRODUCT).'_'));
            $this->assign('func', $func);
            $c = sizeof($func);
            $this->assign('c', $c);
        } else {
            $count = 0;
            $this->assign('c', 0);
        }
        $this->assign('count', $count);
        //5.获取本次迭代的BUG
        $key = cookie(C(PRODUCT).'_pkey') . '-' . $plan['issuenum'];

        $url = C(JIRAURL) . "/rest/synapse/latest/public/testPlan/" . $key . "/defects";
        $bug = httpAuthGet($url, ylh, 123456);
        $bug = json_decode(trim($bug, "\xEF\xBB\xBF"), true);
        cookie('testBug',$bug,array('prefix'=>C(PRODUCT).'_'));
        cookie('bug_num',sizeof($bug),array('prefix'=>C(PRODUCT).'_'));
        if ($bug) {
            foreach ($bug as $b) {
                $bug_id[] = $b['id'];
            }
        }
        //6.获取遗留BUG
        $where = array();
        $where['ID'] = array('in', $bug_id);
        $where['issuestatus'] = array('not in', '6');
        $where['order']='PRIORITY,issuenum';
        $url = C(JIRAPI) . "/Jirapi/issue";
        $yiliu = httpJsonPost($url, json_encode($where));
        $yiliu = json_decode(trim($yiliu, "\xEF\xBB\xBF"), true);
        cookie('testYiliu',$yiliu,array('prefix'=>C(PRODUCT).'_'));
        cookie('bug_yiliu',sizeof($yiliu),array('prefix'=>C(PRODUCT).'_'));

        $xiufl_num=(cookie(C(PRODUCT).'_bug_num')-cookie(C(PRODUCT).'_bug_yiliu'))/cookie(C(PRODUCT).'_bug_num');
        cookie('xiufl_num',round($xiufl_num,4)*100,array('prefix'=>C(PRODUCT).'_'));
        //7.P0级别的bug的数量
        $where = array();
        $where['ID'] = array('in', $bug_id);
        $where['PRIORITY'] = '1';
        $url = C(JIRAPI) . "/Jirapi/issue";
        $p0 = httpJsonPost($url, json_encode($where));
        $p0 = json_decode(trim($p0, "\xEF\xBB\xBF"), true);
        cookie('p0_num',sizeof($p0),array('prefix'=>C(PRODUCT).'_'));

        //8.P1级别的bug的数量
        $where['PRIORITY'] = '2';
        $url = C(JIRAPI) . "/Jirapi/issue";
        $p1 = httpJsonPost($url, json_encode($where));
        $p1 = json_decode(trim($p1, "\xEF\xBB\xBF"), true);
        cookie('p1_num',sizeof($p1),array('prefix'=>C(PRODUCT).'_'));

        //9.P2级别的遗留BUG数量
        $where['PRIORITY'] = '3';
        $where['issuestatus'] = array('not in', '6');
        $url = C(JIRAPI) . "/Jirapi/issue";
        $p2 = httpJsonPost($url, json_encode($where));
        $p2 = json_decode(trim($p2, "\xEF\xBB\xBF"), true);
        cookie('p2_num',sizeof($p2),array('prefix'=>C(PRODUCT).'_'));

        //10.P3级别的遗留BUG数量
        $where['PRIORITY'] = '4';
        $where['issuestatus'] = array('not in', '6');
        $url = C(JIRAPI) . "/Jirapi/issue";
        $p3 = httpJsonPost($url, json_encode($where));
        $p3 = json_decode(trim($p3, "\xEF\xBB\xBF"), true);
        cookie('p3_num',sizeof($p3),array('prefix'=>C(PRODUCT).'_'));

        //11.P4级别的遗留BUG数量
        $where['PRIORITY'] = '5';
        $where['issuestatus'] = array('not in', '6');
        $url = C(JIRAPI) . "/Jirapi/issue";
        $p4 = httpJsonPost($url, json_encode($where));
        $p4 = json_decode(trim($p4, "\xEF\xBB\xBF"), true);
        cookie('p4_num',sizeof($p4),array('prefix'=>C(PRODUCT).'_'));

        $this->display();
    }

    public function tcase()
    {
        $tp = I('tp');
        $this->assign('tp', $tp);

        $plan=cookie(C(PRODUCT).'_testPlan');
        if (!$plan) {
            $url = C(JIRAPI) . "/Jirapi/issue/" . $tp;
            $plan = httpGet($url);
            $plan = json_decode(trim($plan, "\xEF\xBB\xBF"), true);
            cookie('testPlan',$plan,array('prefix'=>C(PRODUCT).'_'));
        }
        $this->assign('plan', $plan);

        //2.获取测试关联的测试用例
        $url = C(JIRAPI) . "/Jirapi/plancase/" . $plan['id'] . "/plan";
        $case = httpGet($url);
        $case = json_decode(trim($case, "\xEF\xBB\xBF"), true);
        cookie('testCase',$case,array('prefix'=>C(PRODUCT).'_'));
        $this->assign('case', $case);

        $this->display();
    }

    public function bug()
    {
        $tp = I('tp');
        $this->assign('tp', $tp);
        $plan=cookie(C(PRODUCT).'_testPlan');
        if (!$plan) {
            $url = C(JIRAPI) . "/Jirapi/issue/" . $tp;
            $plan = httpGet($url);
            $plan = json_decode(trim($plan, "\xEF\xBB\xBF"), true);
            cookie('testPlan',$plan,array('prefix'=>C(PRODUCT).'_'));
        }
        $this->assign('plan', $plan);

        $key = cookie(C(PRODUCT).'_pkey'). '-' . $plan['issuenum'];
        $url = C(JIRAURL) . "/rest/synapse/latest/public/testPlan/" . $key . "/defects";
        $bug = httpAuthGet($url, ylh, 123456);
        $bug = json_decode(trim($bug, "\xEF\xBB\xBF"), true);
        cookie('testBug',$bug,array('prefix'=>C(PRODUCT).'_'));
        $this->assign('bug', $bug);

        $this->display();
    }

    public function yiliu()
    {
        $tp = I('tp');
        $this->assign('tp', $tp);
        $plan=cookie(C(PRODUCT).'_testPlan');
        if (!$plan) {
            $url = C(JIRAPI) . "/Jirapi/issue/" . $tp;
            $plan = httpGet($url);
            $plan = json_decode(trim($plan, "\xEF\xBB\xBF"), true);
            cookie('testPlan',$plan,array('prefix'=>C(PRODUCT).'_'));
        }
        $this->assign('plan', $plan);

        $key =  cookie(C(PRODUCT).'_pkey') . '-' . $plan['issuenum'];
        $url = C(JIRAURL) . "/rest/synapse/latest/public/testPlan/" . $key . "/defects";
        $bug = httpAuthGet($url, ylh, 123456);
        $bug = json_decode(trim($bug, "\xEF\xBB\xBF"), true);
        $this->assign('bug', $bug);
        if ($bug) {
            foreach ($bug as $b) {
                $bug_id[] = $b['id'];
            }
        }
        //6.获取遗留BUG
        $where = array();
        $where['ID'] = array('in', $bug_id);
        $where['issuestatus'] = array('not in', '6');
        $where['order']='PRIORITY';
        $url = C(JIRAPI) . "/Jirapi/issue";
        $yiliu = httpJsonPost($url, json_encode($where));
        $yiliu = json_decode(trim($yiliu, "\xEF\xBB\xBF"), true);
        cookie('testYiliu',$yiliu,array('prefix'=>C(PRODUCT).'_'));
        $this->assign('yiliu', $yiliu);

        $this->display();
    }

    //未提测
    public function unsubm(){
        $planid=I('planid');
        $this->assign('planid', $planid);
        $where=array('planid'=>$planid,'type'=>'2');
        $data=M('tp_risk')->where($where)->find();
        $this->assign('data', $data);

        $plan=cookie(C(PRODUCT).'_testPlan');
        //5.提测打回邮件
        $sendTo = $plan['assignee'] . '@zhidaoauto.com';
        $this->assign('sendTo', $sendTo);
        $cc = 'ylh@zhidaoauto.com;cf@zhidaoauto.com';
        $this->assign('cc', $cc);
        $subject = $plan['summary'] . '，未按计划时间提交测试，会造成项目可能延期的风险';
        $this->assign('subject', $subject);
        $body = '原计划XX月XX日（上午上班前\下午下班前）提测<br>';

        $body.= '截止到  XXX  仍未收到提测信息，因此会造成项目可能延期的风险!<br>
                <br>
                请相关责任人务必回复邮件说明原因，并明确给出可以提测的时间节点。<br \>';
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
        $where=array('planid'=>$planid,'type'=>'0','deleted'=>'0');
        $data=M('tp_risk')->where($where)->find();
        $this->assign('data', $data);

        //2.获取测试关联的测试用例
        $url = C(JIRAPI) . "/Jirapi/plancase/" . $planid . "/plan";
        $case = httpGet($url);
        $case = json_decode(trim($case, "\xEF\xBB\xBF"), true);
        foreach ($case as $ca) {
            $case_id_arr[] = $ca['tc_id'];
        }
        //3.获取测试功能点（测试范围）
        $where['ID'] = array('in', $case_id_arr);
        $where['PRIORITY'] = '1';
        $url = C(JIRAPI) . "/Jirapi/issue";
        $func = httpJsonPost($url, json_encode($where));
        $func = json_decode(trim($func, "\xEF\xBB\xBF"), true);
        $this->assign('func', $func);
        $str='<ol>';
        foreach ($func as $f){
            $str.='<li><small><span class="label label-danger">未通过</span>【'.$_SESSION['pkey'].'-'.$f['issuenum'].'】'.$f['summary'].'</small></li>';
        }
        $str.='</ol>';

        //5.提测打回邮件
        $plan=cookie(C(PRODUCT).'_testPlan');
        $sendTo = $plan['assignee'] . '@zhidaoauto.com';
        $this->assign('sendTo', $sendTo);
        $cc = 'ylh@zhidaoauto.com;cf@zhidaoauto.com';
        $this->assign('cc', $cc);
        $subject = $plan['summary'] . '，测试准入验收未通过，提测版本打回，有项目可能延期的风险';
        $this->assign('subject', $subject);
        $body = '在按照给出的冒烟测试标准进行测试准入验收时，发现有部分冒烟用例测试未通过<br>
                附上冒烟用例测试结果：<br>
                ';
        if(!$data['mod']){
            $body.= $str;
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
        $planid=I('planid');
        $this->assign('planid', $planid);
        $where=array('planid'=>$planid,'type'=>'1');
        $data=M('tp_risk')->where($where)->find();
        $this->assign('data', $data);

        $plan=cookie(C(PRODUCT).'_testPlan');
        if (!$plan) {
            $tp = I('tp');
            //1.获取测试计划详情
            $url = C(JIRAPI) . "/Jirapi/issue/" . $tp;
            $plan = httpGet($url);
            $plan = json_decode(trim($plan, "\xEF\xBB\xBF"), true);
            $_SESSION['testPlan'] = $plan;
        }

        $yiliu=cookie(C(PRODUCT).'_testYiliu');
        if(!$yiliu){
            //5.获取本次迭代的BUG
            $key = cookie(C(PRODUCT).'_pkey'). '-' . $plan['issuenum'];
            $url = C(JIRAURL) . "/rest/synapse/latest/public/testPlan/" . $key . "/defects";
            $bug = httpAuthGet($url, ylh, 123456);
            $bug = json_decode(trim($bug, "\xEF\xBB\xBF"), true);
            $this->assign('bug', $bug);
            if ($bug) {
                foreach ($bug as $b) {
                    $bug_id[] = $b['id'];
                }
            }
            //6.获取遗留BUG
            $where = array();
            $where['ID'] = array('in', $bug_id);
            $where['issuestatus'] = array('not in', '6');
            $where['order']='PRIORITY';
            $url = C(JIRAPI) . "/Jirapi/issue";
            $yiliu = httpJsonPost($url, json_encode($where));
            $yiliu = json_decode(trim($yiliu, "\xEF\xBB\xBF"), true);
            cookie('testYiliu',$yiliu,array('prefix'=>C(PRODUCT).'_'));
        }
        $this->assign('yiliu', $yiliu);

        $str='<ol>';
        foreach ($yiliu as $f){
            $str.='<li><small><span class="badge">'
                .getIssueStatus($f['issuestatus'])
                .'</span>【'.cookie(C(PRODUCT).'_pkey').'-'.$f['issuenum'].'】'.$f['summary']
                .'</small></li>';
        }
        $str.='</ol>';


        $mail = $plan['assignee'] . '@zhidaoauto.com';
        $this->assign('sendTo', $mail);
        $cc = 'ylh@zhidaoauto.com;cf@zhidaoauto.com';
        $this->assign('cc', $cc);
        $subject = $plan['summary'] . '，未达到最基本的上线标准，项目已经延期';
        $this->assign('subject', $subject);
        $body = $plan['summary'] . ',<br>
                原计划XX月XX日上线，截止到XX时间<br>'
            . 'Jira中还有BUG没有关闭:<br>';
        if(!$data['mod']){
            $body.= $str;
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
        $tp=I('tp');
        $this->assign('tp', $tp);
        $type=I('type');
        $this->assign('type', $type);

        $map=array('deleted'=>'0','planid'=>$tp);
        $api=M('tp_project_plan_api')->where($map)->select();
        $this->assign('api', $api);

        $search = I('search');
        $this->assign('search', $search);
        $where = array('removed' => '0');
        $where['apiName|apiURI'] = array('like', '%' . $search . '%');
        $where['apiName'] = array('neq', '示例接口');
        if($api){
            $arrApi=array();
            foreach ($api as $a){
                $arrApi[]=$a['api'];
            }
            $where['apiID']  = array('not in',$arrApi);
        }
        $project = $this->projectID();
        $this->assign('project', $project);
        $branch=I('branch','2');
        $this->assign('branch', $branch);
        $where['projectID']=$branch;
        $data = M('eo_api')->where($where)->select();
        $this->assign('data', $data);

        $this->display();
    }

    //编辑被测接口
    public function mod_api(){
        $m=M('tp_project_plan_api');
        $api=$m->find(I('id'));
        $this->assign('api', $api);
        $where=array('planid'=>$api['planid'],'type'=>'1','deleted'=>'0');
        $data=$m->where($where)->select();
        $this->assign('data', $data);

        $this->display();
    }

    //标记性能
    function pressure(){
        $url = '/' . C(PRODUCT) . '/Plan/choice_api/branch/'.I('branch');
        cookie('url',$url,array('prefix'=>C(PRODUCT).'_'));
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
        $where=$_GET;
        $_GET['table']='tp_project_plan_api';
        $data=M($_GET['table'])->where($where)->find();
        if($data){
            //更新删除标识
            $_GET['id']=$data['id'];
            $_GET['deleted']='0';
            $this->update();
        }else{
            //更新插入数据
            $this->insert();
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
        $url = C(JIRAPI) . "/Jirapi/cycle/" . $cyc;
        $cycle = httpGet($url);
        $cycle = json_decode(trim($cycle, "\xEF\xBB\xBF"), true);
        $this->assign('cycle', $cycle);

        $url = C(JIRAPI) . "/Jirapi/issue/" . $cycle['tp_id'];
        $plan = httpGet($url);
        $plan = json_decode(trim($plan, "\xEF\xBB\xBF"), true);
        $this->assign('plan', $plan);

        //3.获取测试关联的测试用例
        $url = C(JIRAPI) . "/Jirapi/testrun?cycle=" . $cyc;
        $case = httpGet($url);
        $case = json_decode(trim($case, "\xEF\xBB\xBF"), true);
        $this->assign('case', $case);

        $this->display();
    }
}