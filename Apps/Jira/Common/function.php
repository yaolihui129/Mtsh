<?php
    function getJiraUrl($type='api'){
        if($type=='api'){
            $str='http://qc.zhidaoauto.com';
        }elseif ($type=='url'){
            $str='http://jira.zhidaohulian.com';
        }else{
            $str='http://qc.zhidaoauto.com';
        }
        return $str;
    }
    /**
     * 获取接口数据
     */
    function doJiraLogin($user,$password){
        $url = getJiraUrl('url') . '/rest/auth/1/session';
        $json = json_encode(array('username' => $user, 'password' => $password));
        $json = httpJsonPost($url, $json);
        $res = json_decode($json, true);
        return $res;
    }
    function getIssue($id){
        $url  = getJiraUrl(). "/Jirapi/issue/".$id;
        $res = requestApi($url);
        return $res['result'];
    }
    function postIssue($where){
        $url = getJiraUrl() . "/Jirapi/issue";
        $res = httpJsonPost($url, json_encode($where));
        $res = json_decode(trim($res, "\xEF\xBB\xBF"), true);
        return $res['result'];
    }
    function getJiraUser($name){
        $url = getJiraUrl() . "/Jirapi/user?method=find&USER_KEY=" . $name;
        $res = requestApi($url);
        return $res['result'];
    }
    function getJiraPriority($id){
        $url = getJiraUrl() . "/Jirapi/priority/" . $id;
        $res = requestApi($url);
        return $res['result'];
    }
    function getJiraIssueType($id){
        //请求接口拉取数据
        $url = getJiraUrl() . "/Jirapi/issuetype/" . $id;
        $res = requestApi($url);
        return $res['result'];
    }
    function getJiraIssueStatus($id){
        $url = getJiraUrl() . "/Jirapi/issuestatus/" . $id;
        $res = requestApi($url);
        return $res['result'];
    }
    function getJiraTestRunStep($id){
        $url = getJiraUrl(). "/Jirapi/testrunsetp/" . $id . '/run';
        $res = requestApi($url);
        return $res['result'];
    }
    function getJiraTestStep($id){
        $url = getJiraUrl() . "/Jirapi/testsetp/".$id ;
        $res = requestApi($url);
        return $res['result'];
    }
    function postJiraCaseRun($where){
        $url = getJiraUrl() . "/Jirapi/testrun";
        $res = httpJsonPost($url, $where);
        $res = json_decode(trim($res, "\xEF\xBB\xBF"), true);
        return $res['result'];
    }
    function getIssueNum($pkey){
        $url = getJiraUrl() . "/Jirapi/issue/".$pkey."/issuenum";
        $res = requestApi($url);
        return $res['result'];
    }
    function getPlanCase($tp){
        $url = getJiraUrl() . "/Jirapi/plancase/" . $tp . "/plan";
        $res = requestApi($url);
        return $res['result'];
    }
    function getPlanBug($tp){
        $url = getJiraUrl(). "/Jirapi/Plan/" . $tp . "/bug";
        $res = requestApi($url);
        return $res['result'];
    }
    function postPlanBug($where){
        $url = getJiraUrl() . "/Jirapi/Plan/" . $where['tp'] . "/bug";
        $res = httpJsonPost($url, json_encode($where));
        $res = json_decode(trim($res, "\xEF\xBB\xBF"), true);
        return $res['result'];
    }
    function getCycle($cyc){
        $url = getJiraUrl() . "/Jirapi/cycle/" . $cyc;
        $res = requestApi($url);
        return $res['result'];
    }
    function getCycleRun($cyc){
        $url = getJiraUrl() . "/Jirapi/testrun?cycle=" . $cyc;
        $res = requestApi($url);
        return $res['result'];
    }
    function getCyclePlan($tp){
        $url = getJiraUrl() . "/Jirapi/cycle/" . $tp. "/plan";
        $res = requestApi($url);
        return $res['result'];
    }
    function getCycleTestRun($cycleId){
        $url = getJiraUrl() . "/Jirapi/testrun?cycle=" . $cycleId;
        $res = requestApi($url);
        return $res['result'];
    }
    //获取测试周期BUG
    function getTestRunBug($run_id, $step_id = '')
    {
        $url = getJiraUrl() . "/Jirapi/testrunbug?run_id=" . $run_id . '&step_id=' . $step_id;
        $res = httpGet($url);
        $res = json_decode(trim($res, "\xEF\xBB\xBF"), true);
        return $res['data'];
    }
    //获取自定义字段值
    function getCustomFieldValue($ISSUE, $CUSTOMFIELD)
    {
        $url =getJiraUrl() . "/Jirapi/customfieldvalue/".$ISSUE."/".$CUSTOMFIELD;
        $res = httpGet($url);
        $res = json_decode(trim($res, "\xEF\xBB\xBF"), true);
        return $res['result'];
    }

    //获取Jira用户名
    function getJiraName($name)
    {
        $str='';
        if ($name) {
            $m = D('tp_jira_user');
            $where = array('username' => $name);
            $data = $m->where($where)->find();
            if ($data) {
                $str = $data['name'];
            } else {
                //查不到的从远端拉取信息到tp_jira_user
                $url = C('JIRAPI') . "/Jirapi/user?method=find&USER_KEY=" . $name;
                $data = httpGet($url);
                $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);
                $var['username'] = $name;
                $var['name'] = $data['display_name'];
                if (!$m->create($var)) {
                    $this->error($m->getError());
                }
                $m->add($var);
                $str = $data['display_name'];
            }
        }

        return $str;
    }
    function getJiraNameM($name){
        $name=jie_mi($name);
        return getJiraName($name);
    }
    //获取禅道用户名
    function getZTUserName($account){
        if($account){
            $where=array('account'=>$account);
            $arr=M('user')->where($where)->find();
            return $arr['realname'];
        }else {
            return 'NoBody';
        }
    }
    //获取EoLinker用户名
    function getELUser($id)
    {
        $arr = M('eo_user')->find($id);
        return $arr['usernickname'];
    }
    //获取迭代抽签信息
    function drawInfo($project)
    {
        $where = array("project" => $project, 'deleted' => '0');
        $data = M('tp_project_assigne')->where($where)->field('name,draw,renounce,role')->select();
        $str = '';
        if ($data) {
            foreach ($data as $da) {
                $str .= '<div class="col-xs-6 col-md-2">';
                $str .= '<div class="thumbnail">';
                $str .= '<div class="caption">';
                if ($da['renounce']) {
                    $str .= '<h3 class="text-center">' . getJiraName($da['name']) . ' <small>放弃</small></h3>';
                } else {
                    $str .= '<h3 class="text-center">' . getJiraName($da['name']) . '</h3>';
                }
                $str .= '</div>';
//                $str .= '<img src="https://xiuliguanggao.com/Public/images/puk/h_' . $da['draw'] . '.jpg" width="80px">';
                if($da['role']==1){
                    $str .= '<img src="https://xiuliguanggao.com/Public/images/jira/Manager.jpg" width="80px">';
                }elseif ($da['role']==2){
                    $str .= '<img src="https://xiuliguanggao.com/Public/images/jira/Observer.jpg" width="80px">';
                }else{
                    $str .= '<img src="https://xiuliguanggao.com/Public/images/jira/Tester.jpg" width="80px">';
                }
                $str .= '</div>';
                $str .= '</div>';
            }
        }
        return $str;
    }
    //获取issue详情
    function getIssueInfo($id)
    {
        $data = getIssue($id);
        $str = getCache('pkey') . '-' . $data['issuenum'] . '&nbsp;' . $data['summary'];
        $str .= '<span class="badge pull-right">' . getPriority($data['priority']) . '</span>';
        return $str;
    }
    //获取优先级别
    function getPriority($id)
    {
        $str = '';
        if ($id) {
            $m = D('priority');
            $data = $m->find($id);
            if (!$data) {
                //请求接口拉取数据
                $url = C('JIRAPI') . "/Jirapi/priority/" . $id;
                $data = httpGet($url);
                $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);
                $var['ID'] = $id;
                $var['pname'] = $data['pname'];
                if (!$m->create($var)) {
                    $this->error($m->getError());
                }
                $m->add($var);
            }
            $str = $data['pname'];
        }
        return $str;
    }
    //获取issue类型
    function getIssueType($id)
    {
        $str = '';
        if ($id) {
            $m = D('issuetype');
            $data = $m->find($id);
            if (!$data) {
                //请求接口拉取数据
                $url = C('JIRAPI') . "/Jirapi/issuetype/" . $id;
                $data = httpGet($url);
                $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);
                $var['ID'] = $id;
                $var['pname'] = $data['pname'];
                if (!$m->create($var)) {
                    $this->error($m->getError());
                }
                $m->add($var);
            }
            $str = $data['pname'];
        }
        return $str;
    }
    //获取issue状态
    function getIssueStatus($id)
    {
        $str = '';
        if ($id) {
            $m = D('issuestatus');
            $data = $m->find($id);
            if (!$data) {
                //请求接口拉取数据
                $url = C('JIRAPI') . "/Jirapi/issuestatus/" . $id;
                $data = httpGet($url);
                $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);
                $var['ID'] = $id;
                $var['pname'] = $data['pname'];
                if (!$m->create($var)) {
                    $this->error($m->getError());
                }
                $m->add($var);
            }
            $str = $data['pname'];
        }
        return $str;
    }

    //获取测试步骤
    function getTestRunStep($id)
    {
        $str = '';
        $url = C('JIRAPI') . "/Jirapi/testrunsetp/" . $id . '/run';
        $data = httpGet($url);
        $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);
        if ($data) {
            foreach ($data as $k => $da) {
                $str .= '<li class="list-group-item">';
                $str .= '<div class="row">';
                $str .= '<div class="col-md-1">' . $k . '</div>';
                $str .= '<div class="col-md-4">' . $da['step'] . '</div>';
                $str .= '<div class="col-md-4">' . $da['expected_result'] . '</div>';
                $str .= '<div class="col-md-1">' . $da['status'] . '</div>';
                $str .= '<div class="col-md-2">' . $da['actual_result'] . '</div>';
                $str .= '</div>';
                $str .= '</li>';
            }

        }
        return $str;

    }
    //获取用例步骤
    function getTestStep($tc_id){
        $str = '';
        $url = C('JIRAPI') . "/Jirapi/testsetp/".$tc_id ;
        $data = httpGet($url);
        $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);
        if ($data) {
            foreach ($data as $k => $da) {
                $str .= '<li class="list-group-item">';
            $str .=         '<div class="row">';
                $str .=         '<div class="col-md-1">' . $k. '</div>';
                $str .=         '<div class="col-md-4">' . $da['step'] . '</div>';
                $str .=         '<div class="col-md-4">' . $da['expected_result'] . '</div>';
                $str .=         '<div class="col-md-2">' . $da['step_data'] . '</div>';
                $str .=         '<div class="col-md-1">'  . '</div>';
                $str .=     '</div>';
                $str .= '</li>';
            }
        }
        return $str;
    }
    //获取测试用例的执行周期
    function getCaseRun($tc, $cyc)
    {
        $str = '';
        $where['TC_ID'] = $tc;
        $where['TEST_CYCLE_ID'] = array('in', $cyc);
        $url = C('JIRAPI') . "/Jirapi/testrun";
        $data = httpJsonPost($url, $where);
        $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);
        if ($data) {
            foreach ($data as $k => $da) {
                $str .= '<li class="list-group-item">';
                $str .= '<div class="row">';
                $str .= '<div class="col-md-1">' . $k . '</div>';
                $str .= '<div class="col-md-4">' . $da['test_cycle_id'] . '</div>';
                $str .= '<div class="col-md-4">' . $da['executed_by'] . '</div>';
                $str .= '<div class="col-md-1">' . $da['status'] . '</div>';
                $str .= '<div class="col-md-2">' . $da['execution_on'] . '</div>';
                $str .= '</div>';
                $str .= '</li>';
            }
        }
        return $str;
    }
    //获取API测试用例
    function apiCase($caseID){
        $str = '';
        $where['caseID'] = $caseID;
        $data = M('eo_project_test_case_single')->where($where)->order('orderNumber')->select();
        if ($data) {
            foreach ($data as $k => $da) {
                $str .= '<li class="list-group-item">';
                $str .= '<div class="row">';
                $str .= '<div class="col-md-3"><small>' . ($k+1) .'.'.$da['apiname']. '</small></div>';
                $str .= '<div class="col-md-8"><small>' . $da['apiuri'] . '</small></div>';
                if($da['apirequesttype']=='0'){
                    $str .= '<div class="col-md-1"><small>【POST】</small></div>';
                }elseif ($da['apirequesttype']=='1'){
                    $str .= '<div class="col-md-1"><small>【GET】</small></div>';
                }else{
                    $str .= '<div class="col-md-1"><small>【GET】</small></div>';
                }
                $str .= '</div>';
                $str .= '</li>';
            }
        }
        return $str;
    }


    //统计加班时长
    function getJiabHour($user)
    {
        $where = array('user' => $user, 'type' => '1');
        $Num = M('tp_overtime')->where($where)->sum('hourlong');
        if ($Num) {
            return $Num;
        } else {
            return 0;
        }
    }
    //统计调休时长
    function getTiaoxHour($user)
    {
        $where = array('user' => $user, 'type' => '2');
        $Num = M('tp_overtime')->where($where)->sum('hourlong');
        if ($Num) {
            return $Num;
        } else {
            return 0;
        }

    }
    //计算可调休时长
    function getKeyHour($user)
    {
        $where = array('user' => $user, 'type' => '2');
        $tiaox = M('tp_overtime')->where($where)->sum('hourlong');
        $where = array('user' => $user, 'type' => '1');
        $jiab = M('tp_overtime')->where($where)->sum('hourlong');
        $Num = $jiab - $tiaox;
        if ($Num) {
            return $Num;
        } else {
            return 0;
        }
    }

    function getUserWorkHour($user,$riQi=''){
        if(!$riQi){
            $riQi=date("Y-m-d", time());
        }
        $where=array('user'=>$user,'riqi'=>$riQi);
        return sum('tp_work_hour',$where,'hourlong');
    }

    function getTaskWorkHour($task){
        $where=array('task'=>$task,);
        return sum('tp_work_hour',$where,'hourlong');
    }

    /**
     * 设备&图书的预约数量
     * @param $id
     * @param string $start_time
     * @return
     */
    function count_yd($id,$start_time=''){
        if($start_time){
            $where=array('device'=>$id,'start_time'=>$start_time,'type'=>'1','deleted'=>'0');
        }else{
            $where=array('device'=>$id,'type'=>'1','deleted'=>'0');
        }
        $count=M('tp_device_loaning_record')->where($where)->count();
        return $count;
    }

    /**
     * 设备&图书的可借用的状态
     * 借阅：0-可借，1-借出，2-待归还
     * @param $status
     * @return string
     */
    function book_status($status){
        if ($status==1){
            return '借出';
        }elseif ($status==2){
            return '借出=';
        }elseif ($status==0){
            return '可借';
        }else{
            return '';
        }
    }

    /**
     * 设备&图书的借出状态
     * 分类：0-借阅，1-预订，2-归还
     */
    function book_history_status($status){
        if ($status==1){
            return '预订';
        }elseif ($status==2){
            return '归还';
        }elseif ($status==0){
            return '借用';
        }else{
            return '';
        }
    }

    //测试组人员
    function test_group_member($testGroup){
        $where=array('test_group'=>$testGroup,'deleted'=>'0');
        $data=M('tp_test_group_member')->where($where)->field('name')->select();
        return $data;
    }

    function zhipai($id){
        $pending=M('tp_project_pending')->find($id);
        $user=test_group_member($pending['pgroup']);
        $str='';
        if(!$user){
            $user=C('QA_TESTER');
        }
        foreach ($user as $u){
            $str.='<a href="'.__APP__.'/Jira/Index/zhipai/id/'.$id.'/draw/'.$u['name']
                .'" class="btn btn-success btn-xs">'.getJiraName($u['name']).'</a>&nbsp;';
        }
        return $str;
    }

    //周期积分汇总
    function sumScore($user,$quarter){
        $m=M('tp_my_score');
        $where=array('quarter'=>$quarter,'type'=>'1','user'=>$user,'deleted'=>'0');
        $jiaf=$m->where($where)->sum('score');
        $where=array('quarter'=>$quarter,'type'=>'2','user'=>$user,'deleted'=>'0');
        $jianf=$m->where($where)->sum('score');
        return $jiaf-$jianf;
    }

    /**
     * 计算个级别的BUG数量
     * @param $tp
     * @param int $priority
     * @param string $type false 计算所有，true  计算遗留，
     * @return int
     */
    function countTpBugPriority($tp,$priority=0,$type=''){
        $where = array();
        $where['tp']=$tp;
        $where['PRIORITY'] =strval($priority+1);
        if($type){
            $where['issuestatus'] = array('not in', '6');
        }
        $bug=postPlanBug($where);
        $bugNum=sizeof($bug);
        return $bugNum;
    }


    function getBugReviewInfo($bugID,$field='chief'){
        $table='tp_bug_review';
        $res=find($table,$bugID);
        if($res){
            return getDictValue($field,$res[$field]);
        }else{
            return '未评审';
        }
    }

    function getBugReviewInfoReasonSub($bugID){
        $table='tp_bug_review';
        $res=find($table,$bugID);
        if($res){
            return $res['reason_sub'];
        }
    }




