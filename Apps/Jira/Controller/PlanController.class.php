<?php
namespace Jira\Controller;
class PlanController extends WebInfoController
{
    //测试计划列表
    public function index()
    {
        $type=I('type','doing');
        setCache('type_plan',$type);
        $this->assign('typePlan', $type);
        if ($type == 'online') {
            $where['issuestatus'] = array('in', '3,10002,10011,6');
            $where['order']='UPDATED desc';
            $where['page']=I('page',1);
            $where['size']=30;
        }elseif ($type == 'done'){
            $where['issuestatus'] = array('in','10004,10107');
        } else {
            $where['issuestatus'] = array('not in', '3,10002,10011,10004,10107,6');
            $where['order']='issuestatus desc,ASSIGNEE';
        }
        $project=getCache('project');
        $this->assign('project', $project);
        if($project ==10006){
            $testGroup=I('testGroup','one');
            setCache('testGroup',$testGroup);
            $this->assign('testGroup', $testGroup);
            if($testGroup=='one'){
                $where['CREATOR'] =array('in',C('QA_TESTER'));
                $where['reporter'] =array('in',C('QA_TESTER'));
            }else{
                $where['CREATOR'] =array('in','qinzhenxia,congtianyue,zhangaonan');
                $where['reporter'] =array('in','qinzhenxia,congtianyue,zhangaonan');
            }
        }
        $where['PROJECT'] = $project;
        $where['issuetype'] = '10102';
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['SUMMARY|issuenum|pkey'] = array('like', '%' . $search . '%');
        $data=postIssue($where);
        //同步到本地库
        $this->synchJiraIssue($data);
        //从本地库查询
        $where['project'] = $project;
        $where['summary|pkey'] = array('like', '%' . $search . '%');
        $table= 'tp_jira_issue';
        $order='issuestatus desc,assignee';
        $this->assign('data', getList($table,$where,$order));
        $this->assign('user', getLoginUser());

        $this->display();
    }
    //更改测试计划类型
    function change_type(){
        $project=cookie(C('PRODUCT') .'project');
        $testGroup=cookie(C('PRODUCT') .'testGroup');
        $url = '/' . C('PRODUCT') . '/Plan/index/project/'.$project.'/testGroup/'.$testGroup;
        $this->isLogin($url);
        $_GET['plantype']=I('plantype');
        $_GET['table']='tp_jira_issue';
        $this->update();
    }
    //测试执行记录
    public function run(){
        $cyc = I('cyc');
        //1.获取测试周期详情
        $data=getCycle($cyc);
        $this->assign('cycle', $data);
        $table='tp_jira_issue';
        //获取计划详情
        $this->assign('plan',find($table,$data['tp_id']) );
        //2.获取测试关联的测试用例
        $this->assign('case', getCycleRun($cyc));

        $this->display();
    }
    //功能测试详情
    public function details(){
        $tp = I('tp');
        $table='tp_jira_issue';
        $this->assign('tp', $tp);
        $issue=array(getIssue($tp));
        if($issue){
            $this->synchJiraIssue($issue);
        }
        //获取计划详情
        $this->assign('plan',find($table,$tp) );
        //获取计划周期
        $data=getCyclePlan($tp);
        $this->assign('cycle', $data);
        $this->assign('project', getCache('project'));
        $this->assign('pkey', getCache('pkey'));
        $this->assign('typePlan', getCache('type_plan'));
        $this->assign('user',getLoginUser() );
        //获取测试关联的测试用例
        $testCase=getPlanCase($tp);
        setCache('testCase',$testCase);
        $this->assign('case', $testCase);
        if ($testCase) {
            $caseNum = sizeof($testCase);
            $case_id_arr=array();
            foreach ($testCase as $ca) {
                $case_id_arr[] = $ca['tc_id'];
            }
            //3.获取测试功能点（测试范围）
            $where=array();
            $where['ID'] = array('in', $case_id_arr);
            $where['PRIORITY'] = '1';
            $testFunc=postIssue($where);
            setCache('testFunc',$testFunc);
            $this->assign('func', $testFunc);
            $this->assign('funcNum', sizeof($testFunc));
        } else {
            $caseNum = 0;
            $this->assign('funcNum', 0);
        }

        $this->assign('caseNum', $caseNum);
        setCache('caseNum',$caseNum);
        //本迭代的BUG
        $testBug=getPlanBug($tp);
        $this->assign('testBug', $testBug);
        setCache('testBug',$testBug);

        //获取遗留BUG
        $where = array();
        $where['tp']=$tp;
        $where['issuestatus'] = array('not in', '6');
        $residueBug=postPlanBug($where);
        $this->assign('residueBug', $residueBug);
        setCache('residueBug',$residueBug);

        //Bug修复率
        $bugNum=sizeof($testBug);
        $this->assign('bugNum', $bugNum);
        setCache('bugNum',$bugNum);

        $residueBugNum=sizeof($residueBug);
        $this->assign('residueBugNum', $residueBugNum);
        setCache('residueBugNum',$residueBugNum);

        $bugRepairRate=($bugNum-$residueBugNum)/$bugNum;
        $bugRepairRate=round($bugRepairRate,4)*100;
        $this->assign('bugRepairRate', $bugRepairRate);
        setCache('bugRepairRate',$bugRepairRate);

        //P0级别的bug的数量
        $bugNumP0=countTpBugPriority($tp,0);
        $this->assign('bugNumP0', $bugNumP0);
        setCache('bugNumP0',$bugNumP0);

        //P1级别的bug的数量
        $bugNumP1=countTpBugPriority($tp,1);
        $this->assign('bugNumP1', $bugNumP1);
        setCache('bugNumP1',$bugNumP1);

//        //P2级别的遗留BUG数量
//        $bugNumP2=countTpBugPriority($tp,2,'yiLiu');
//        $this->assign('bugNumP2', $bugNumP2);
//        setCache('bugNumP2',$bugNumP2);
//
//        //P3级别的遗留BUG数量
//        $bugNumP3=countTpBugPriority($tp,3,'yiLiu');
//        $this->assign('bugNumP3', $bugNumP3);
//        setCache('bugNumP3',$bugNumP3);
//
//        //P4级别的遗留BUG数量
//        $bugNumP4=countTpBugPriority($tp,4,'yiLiu');
//        $this->assign('bugNumP4', $bugNumP4);
//        setCache('bugNumP4',$bugNumP4);

        $this->display();
    }
    public function tcase()
    {
        $tp = I('tp');
        $table='tp_jira_issue';
        $this->assign('tp', $tp);
        //获取计划详情
        $this->assign('plan',find($table,$tp) );
        $this->assign('project', getCache('project'));
        $this->assign('pkey', getCache('pkey'));
        $this->assign('typePlan', getCache('type_plan'));
        $this->assign('bugNum', getCache('bugNum'));
        $this->assign('residueBugNum', getCache('residueBugNum'));
        $this->assign('bugRepairRate', getCache('bugRepairRate'));
        $this->assign('bugNumP0', getCache('bugNumP0'));
        $this->assign('bugNumP1', getCache('bugNumP1'));
        $this->assign('user',getLoginUser() );
        $this->assign('case', getCache('testCase'));
        $this->assign('caseNum', getCache('caseNum'));


        $this->display();
    }
    public function bug()
    {
        $tp = I('tp');
        $table='tp_jira_issue';
        $this->assign('tp', $tp);
        //获取计划详情
        $this->assign('plan',find($table,$tp) );
        $this->assign('project', getCache('project'));
        $this->assign('pkey', getCache('pkey'));
        $this->assign('typePlan', getCache('type_plan'));
        $this->assign('residueBugNum', getCache('residueBugNum'));
        $this->assign('bugRepairRate', getCache('bugRepairRate'));
        $this->assign('bugNumP0', getCache('bugNumP0'));
        $this->assign('bugNumP1', getCache('bugNumP1'));
        $this->assign('caseNum', getCache('caseNum'));
        $this->assign('bug', getCache('testBug'));
        $this->assign('bugNum', getCache('bugNum'));

        $this->display();
    }
    public function yiliu()
    {
        $tp=I('tp');
        $table='tp_jira_issue';
        $this->assign('tp', $tp);
        //获取计划详情
        $this->assign('plan',find($table,$tp) );
        $this->assign('project', getCache('project'));
        $this->assign('pkey', getCache('pkey'));
        $this->assign('typePlan', getCache('type_plan'));
        $this->assign('bugNum', getCache('bugNum'));
        $this->assign('residueBugNum', getCache('residueBugNum'));
        $this->assign('bugRepairRate', getCache('bugRepairRate'));
        $this->assign('bugNumP0', getCache('bugNumP0'));
        $this->assign('bugNumP1', getCache('bugNumP1'));
        $this->assign('residueBug', getCache('residueBug'));
        $this->assign('caseNum', getCache('caseNum'));

        $this->display();
    }
    //Api测试详情
    public function api(){
        $tp = I('tp');
        $table='tp_jira_issue';
        $this->assign('tp', $tp);
        $issue=array(getIssue($tp));
        if($issue){
            $this->synchJiraIssue($issue);
        }
        //获取计划详情
        $plan=find($table,$tp);
        $this->assign('plan',$plan);

        $table='tp_project_plan_extend';
        $extend = find($table,$tp);
        if(!$extend){
            $_GET['id']=$tp;
            $_GET['ptype']=$plan['ptype'];
            insert($table,$_GET);
            $extend = find($table,$tp);
        }
        $this->assign('extend',$extend);

        $table='tp_project_plan_api';
        $this->assign('api', getList($table,array('planid'=>$tp)));

        $this->display();
    }
    //性能测试详情
    public function capability(){
        $tp = I('tp');
        $table='tp_jira_issue';
        $this->assign('tp', $tp);
        $this->assign('pkey', getCache('pkey'));
        $issue=array(getIssue($tp));
        if($issue){
            $this->synchJiraIssue($issue);
        }
        //获取计划详情
        $plan=find($table,$tp);
        $this->assign('plan',$plan );

        $table='tp_project_plan_extend';
        $extend = find($table,$tp);
        if(!$extend){
            $_GET['id']=$tp;
            $_GET['ptype']=$plan['ptype'];
            insert($table,$_GET);
            $extend = find($table,$tp);
        }
        $this->assign('extend',$extend);

        $dict=getDictInfo('per_scheme',$extend['per_scheme'],'tp_dict','json');
        $dict=json_decode($dict);
        foreach ($dict as $d){
            $this->assign('extend_scheme'.$d, 'extend_scheme'.$d);
        }


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
        $this->assign('source', I('source'));
        $table='tp_project_plan_api';
        $map=array('planid'=>$tp);
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
        $this->assign('data', getList($table,$where,'apiID'));

        $this->display();
    }
    function getApi(){
        $table='tp_project_plan_api';
        $res=find($table,I('id'));
        $res['apiUrl']=getName('eo_api',$res['api'],'apiuri');
        $res['apiName']=getName('eo_api',$res['api'],'apiname');
        $res=resFormat($res);
        $this->ajaxReturn($res);
    }
    //标记性能
    function tabPressure($api){
        $table='eo_api';
        $var=array('apiID'=>$api,'pressure'=>'1');
        update($table,$var);
    }
    //撤销选择
    function cheXiao(){
        $_GET['deleted']='1';
        $_GET['table']='tp_project_plan_api';
        $this->update();
    }
    //标记性能
    function jiaRu(){
        if($_GET['source']=='capability'){
            //先标记性能
            $this->tabPressure($_GET['api']);
        }
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


    //API性能测试场景
    public function press(){
        $id = I('id');
        $url = '/' . C('PRODUCT') . '/Api/press/id/'.$id;
        $this->isLogin($url);
        $data = find('eo_api',$id);
        $this->assign('data', $data);

        $project=I('project');
        $this->assign('project', $project);
        $scheme=I('scheme');
        $this->assign('scheme', $scheme);
        $this->assign('user', getLoginUser());

        if($project){
            $where = array('api' => $id, 'project'=>$project);
        }else{
            $where = array('api' => $id);
        }
        $scene = getList('tp_api_scene_pressure',$where,'project,sn,id');
        $this->assign('scene', $scene);
        $this->assign('c', sizeof($scene));

        $lim=$this->getDictInfo("per_scheme", $scheme);
        $default = json_decode(trim($lim['json'], "\xEF\xBB\xBF"), true);
        $press_type=$this->dictList('press_type','press_type',$default[0],$default);
        $this->assign('press_type', $press_type);

        $compres=$this->getDictList('compres');
        $compres = select($compres, 'compres',0);
        $this->assign("compres", $compres);


        $this->display();
    }

    function selected(){
        $id=I('id');
        $selected=I('selected');
        $table='tp_api_scene_pressure';
        $_GET=array();
        $_GET['id']=$id;
        if($selected){
            $_GET['selected']='0';
        }else{
            $_GET['selected']='1';
        }
        $_GET['table']=$table;
        $this->update();

    }

    function pressureSceneInfo(){
        $table='tp_api_scene_pressure';
        $res=find($table,I('id'));
        $res['compres']=getDictValue('compres',$res['compres']);
        $res['press_type']=getDictValue('press_type',$res['press_type']);
        $res=resFormat($res);
        $this->ajaxReturn($res);
    }
    //API压测记录
    public function pressure_test(){
        $url = '/' . C('PRODUCT') . '/Plan/pressure_test';
        $this->isLogin($url);
        $id = I('id');
        $this->assign('id', $id);
        $arr = find('tp_api_scene_pressure',$id);
        $this->assign('arr', $arr);

        $project=I('project');
        $this->assign('project', $project);

        $scheme=I('scheme');
        $this->assign('scheme', $scheme);

        $user=getLoginUser();
        $user=getJiraName($user);
        $this->assign('user', $user);

        $where = array('scene' => $id);
        $data = getList('tp_api_scene_pressure_test',$where);
        $this->assign('data', $data);

        $this->assign('t_time', date('Y-m-d H:i:s',time()));

        $this->display();
    }

    function getScenePressureTestInfo(){
        $table='tp_api_scene_pressure_test';
        $res=find($table,I('id'));
        $res=resFormat($res);
        $this->ajaxReturn($res);
    }
    //标记结果
    function record_result(){
        $url = '/' . C('PRODUCT') . '/Api/index';
        $this->isLogin($url);
        $arr =find('tp_api_scene_pressure_test',I('id'));
        $_GET['id']=$arr['scene'];
        $_GET['samples']=$arr['samples'];
        $_GET['average']=$arr['average'];
        $_GET['median']=$arr['median'];
        $_GET['line90']=$arr['line90'];
        $_GET['line95']=$arr['line95'];
        $_GET['line99']=$arr['line99'];
        $_GET['min']=$arr['min'];
        $_GET['max']=$arr['max'];
        $_GET['error']=$arr['error'];
        $_GET['tps']=$arr['tps'];
        $_GET['tester']=$arr['tester'];
        $_GET['t_time']=$arr['t_time'];
        $_GET['test_id']=$arr['id'];
        $_GET['table']='tp_api_scene_pressure';
        $this->update();
    }


}