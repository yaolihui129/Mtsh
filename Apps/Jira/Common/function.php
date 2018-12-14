<?php
    //获取自定义字段值
    function getCustomFieldValue($ISSUE, $CUSTOMFIELD)
    {
        $url = C(JIRAPI) . "/Jirapi/customfieldvalue/".$ISSUE."/".$CUSTOMFIELD;
        $data = httpGet($url);
        if($data){
            return $data;
        }else{
            return 'Null';
        }
    }

    //获取Jira用户名
    function getJiraName($name)
    {
        if ($name) {
            $m = D('tp_jira_user');
            $where = array('username' => $name);
            $data = $m->where($where)->cache('cache_' . $name)->find();
            if ($data) {
                return $data['name'];
            } else {
                //查不到的从远端拉取信息到tp_jira_user
                $url = C(JIRAPI) . "/Jirapi/user?method=find&USER_KEY=" . $name;
                $data = httpGet($url);
                $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);
                $var['username'] = $name;
                $var['name'] = $data['display_name'];
                if (!$m->create($var)) {
                    $this->error($m->getError());
                }
                $m->add($var);
                return $data['display_name'];
            }
        }
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
    //解密用户
    function getJiraNameM($name){
        $name=jie_mi($name);
        $name=getJiraName($name);
        return $name;
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
        $url = C(JIRAPI) . "/Jirapi/issue/" . $id;
        $data = httpGet($url);
        $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);
        $str = cookie(C(PRODUCT).'_pkey') . '-' . $data['issuenum'] . '&nbsp;' . $data['summary'];
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
                $url = C(JIRAPI) . "/Jirapi/priority/" . $id;
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
                $url = C(JIRAPI) . "/Jirapi/issuetype/" . $id;
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
                $url = C(JIRAPI) . "/Jirapi/issuestatus/" . $id;
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
    //获取测试周期BUG
    function getTestRunBug($run_id, $step_id = '')
    {
        $url = C(JIRAPI) . "/Jirapi/testrunbug?run_id=" . $run_id . '&step_id=' . $step_id;
        $data = httpGet($url);
        $data = json_decode(trim($data, "\xEF\xBB\xBF"), true);
        return $data;
    }
    //获取测试步骤
    function getTestRunStep($id)
    {
        $str = '';
        $url = C(JIRAPI) . "/Jirapi/testrunsetp/" . $id . '/run';
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
        $url = C(JIRAPI) . "/Jirapi/testsetp/".$tc_id ;
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
        $url = C(JIRAPI) . "/Jirapi/testrun";
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

    /*
     * 设备&图书的预约数量
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
    /*
     * 设备&图书的可借用的状态
     * 借阅：0-可借，1-借出，2-待归还
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

    /*
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
            $user=C(QA_TESTER);
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


