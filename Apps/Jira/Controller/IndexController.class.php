<?php

namespace Jira\Controller;
class IndexController extends WebInfoController
{
    public function index()
    {
        $tpye=I('type','doing');
        setCookieKey('type_index',$tpye,7*24*3600);
        $project=I('project', '10006');
        setCookieKey('project',$project,7*24*3600);
        $pro= find('project',$project);
        setCookieKey('pkey',$pro['pkey'],7*24*3600);
        setCookieKey('pname',$pro['pname'],7*24*3600);
        if ($tpye== 'done') {
            $where['issuestatus'] = array('in', '3,10002,6');
        } elseif ($tpye== 'assigned') {
            $url= '/' . C(PRODUCT) . '/Index/assigned/project/' .$project;
            setCookieKey('url',$url,7*24*3600);
            $this->isLogin();
            $where['issuestatus'] = array('in', '1,10000,10016,10017');
        } else {
            $where['issuestatus'] = array('not in', '1,3,10000,10002,10015,10016,10017,6');
        }
        $where['PROJECT'] = intval($project);
        $where['issuetype'] = array('in', '10005,10006');

        $search = trim(I('search'));
        $this->assign('search', $search);

        $where['SUMMARY|issuenum'] = array('like', '%' . $search . '%');
        $data=$this->getIssueList($where);
        $this->assign('data', $data);
        $this->assign('count',sizeof($data));

        $project = $this->projectDict();
        $this->assign('project', $project);

        $this->display();
    }

    public function assigned()
    {
        $tpye=I('type','doing');
        setCookieKey('type_index',$tpye,7*24*3600);
        $project=getCookieKey('project');
        if ($tpye == 'done') {
            $where['issuestatus'] = array('in', '3,10002');
        } elseif ($tpye== 'assigned') {
            $url= '/' . C(PRODUCT) . '/Index/assigned/project/' . $project;
            setCookieKey('url',$url,7*24*3600);
            $this->isLogin();
            $where['issuestatus'] = array('in', '1,10000,10015,10016,10017');
        } else {
            $where['issuestatus'] = array('not in', '1,3,10000,10002,10015');
        }
        $where['PROJECT'] = intval($project);
        $where['issuetype'] = array('in', '10005,10006');

        $search = trim(I('search'));
        $this->assign('search', $search);

        $where['SUMMARY|issuenum'] = array('like', '%' . $search . '%');
        $data=$this->getIssueList($where);
        $this->assign('data', $data);

        $project = $this->projectDict();
        $this->assign('project', $project);

        $this->display();
    }

    public function started()
    {
        $tpye=I('type');
        setCookieKey('type_index',$tpye,7*24*3600);
        $project=getCookieKey('project');
        if ($tpye == 'done') {
            $where['issuestatus'] = array('in', '3,10002');
        } elseif ($tpye == 'assigned') {
            $where['issuestatus'] = array('in', '10000,10016,10017');
        } else {
            $where['issuestatus'] = array('not in', '1,3,10000,10002,10015');
        }

        $where['PROJECT'] = intval($project);
        $where['issuetype'] = array('in', '10005,10006');

        $search = trim(I('search'));
        $this->assign('search', $search);

        $where['SUMMARY|issuenum'] = array('like', '%' . $search . '%');
        $where['order']='issuenum desc';
        $map = array('deleted' => '0');
        $pending = getList('tp_project_pending',$map);
        if ($pending) {
            foreach ($pending as $pend) {
                $p[] = $pend['id'];
            }
            $where['ID'] = array('not in', $p);
        }
        $data=$this->getIssueList($where);
        $this->assign('data', $data);

        $testGroup=$this->get_dict_list('test_group');
        $this->assign('group', $testGroup);
        $this->assign('user', getLoginUser());

        $project = $this->projectDict();
        $this->assign('project', $project);

        $this->display();
    }

    public function pending()
    {
        $url = '/' . C(PRODUCT) . '/Index/pending';
        setCookieKey('url',$url,7*24*3600);
        $this->isLogin();
        $map['status'] = array('in' , '0,1,2');;
        $draw=I('draw');
        $this->assign('draw', $draw);
        if($draw){
            $map['draw'] = $draw;
        }
        $table='tp_project_pending';
        $data = getList($table,$map,'status desc,ctime desc');
        if ($draw){
            foreach ($data as $da){
                $this->synch_issuestatus($da['id']);
            }
            $data = getList($table,$map,'status desc,ctime desc');
        }
        $this->assign('data', $data);

        if(getLoginUser()=='ylh'){
            $editable2=1;
        }else{
            $editable2=0;
        }
        $this->assign('editable2', $editable2);

        $user=C(QA_TESTER);
        $this->assign('user', $user);

        $where=array('deleted'=>'0');
        $testGroup=getList('tp_test_group_member',$where,'test_group');
        $this->assign('group', $testGroup);

        $this->display();
    }
    //加入分派
    function jion()
    {
        if (getLoginUser()== 'ylh') {
            $data = $this->getIssueInfo(I('issueid'));
            $data['pkey'] = getCookieKey('pkey') . '-' . $data['issuenum'];
            $data['pgroup'] = I('pgroup');
            $data['pname'] = $data['summary'];
            $data['url'] = '/' . C(PRODUCT) . '/Index/pending';
            $this->insert('tp_project_pending',$data);
        } else {
            $this->error('对不起，你没有权限！');
        }
    }
    //撤回
    function chehui()
    {
        $this->delete('tp_project_pending',I('project'));
    }
    //完成
    function done(){

        $project=getCookieKey('project');
        $where['SUMMARY'] = array('like', '%【' . I('pkey') . '】%');
        $where['PROJECT'] =intval($project);
        $where['issuetype'] = '10102';
        $data=$this->getIssueList($where);
        $id=I('project');
        $_GET=array();
        $_GET['id'] = $id;
        $_GET['status'] = '1';
        if($data[0]){
            $_GET['planid'] = $data[0]['id'];
            $_GET['plankey'] =getCookieKey('pkey').'-'.$data[0]['issuenum'];
        }
        $this->update('tp_project_pending',$_GET);
    }
    //结束
    function jieshu(){
        $_GET['id'] = I('project');
        $_GET['status'] = '3';
        $this->update('tp_project_pending',$_GET);
    }
    //抽签
    function draw()
    {
        $this->isLogin();
        //1.先生成一个随机数
        if (in_array($_SESSION['user'], C(QA_TESTER))) {
            $project = I('project');
            $name = getLoginUser();
            $draw = C(DRAW);
            $key = rand(0, sizeof($draw) - 1);
            $draw = $draw[$key];
            //2.先查库，如果有值直接返回“您已经完成抽签”
            $where = array('project' => $project);
            $data = getList('tp_project_assigne',$where);
            foreach ($data as $d) {
                $arr[] = $d['draw'];
                $user[] = $d['name'];
            }
            if (in_array($name, $user)) {//判断此人是否已经抽过
                $this->error("您已经完成此项目的抽签！");
            } else {
                if (in_array("$draw", $arr)) {
                    $this->error("你抽中的是" . $draw . ',签码重复请重抽');
                } else {
                    $_GET = array();
                    $_GET['project'] = $project;
                    $_GET['name'] = $name;
                    $_GET['k'] = $key;
                    $_GET['draw'] = $draw;
                    $_GET['role']='1';
                    $this->insert('tp_project_assigne',$_GET);
                    $data = getList('tp_project_assigne',$where,'k desc');
                    $num=sizeof($data);
                    if ($num >= (sizeof(C(QA_TESTER)) - 1)) {
                        $_GET = array();
                        $_GET['id'] = $project;
                        $_GET['draw'] = $data[0]['name'];
                        $_GET['checkd'] = $data[$num-1]['name'];
                        $this->update('tp_project_pending',$_GET);
                    }
                }
            }
        } else {
            $this->error('对不起，该功能只对车险测试人员开放！');
        }

    }
    //指派
    function zhipai(){
        $id=I('id');
        $draw=I('draw');
        $user=C(QA_TESTER);
        $key = rand(0, sizeof($user) - 1);
        $checkd=$user[$key];
        $arr=find('tp_project_pending',$id);
        if($arr['draw']){
            $this->error('已指派负责人，请不要重复指派');
        }else{
            //1.更新tp_project_pending
            $_GET['checkd']=$checkd;
            $this->update('tp_project_pending',$_GET);
            //2.发送企业微信消息
            $who=['ylh','shenjiakai',$arr['reporter'],$arr['assignee'],$draw];
            $who=array_unique($who);//去除重复的收信人
            $msg='【测试任务安排 - QC平台】
《【'.$arr['pkey'].'】'.$arr['pname'].'》
已安排给【'.getJiraName($draw).'】 负责测试';
            $msg=$this->sendMessage($who,$msg);
            print_r($msg);
            //3.写入tp_project_assigne
            $_GET=array();
            $_GET['name']=$draw;
            $_GET['k']=13;
            $_GET['draw']='JOKER';
            $_GET['project']=$id;
            $_GET['role']='1';
            $this->insert('tp_project_assigne',$_GET);
        }
    }
    //换人
    function huanren(){
        $id=I('id');
        $user=I('user');
        $pending=find('tp_project_pending',$id);
        if($user==$pending['draw']){
            $this->error('不能更换为原负责人');
        }else{
            //1,更新负责人
            $_GET['draw']=$user;
            $this->update('tp_project_pending',$_GET);
            //2,新负责人设置为manger
            $m=D('tp_project_assigne');
            $where=array('project'=>$id,'name'=>$user,'deleted'=>'0');
            $arr=$m->where($where)->find();
            $_GET=array();
            if($arr){//1)新负责人在此项目
                $_GET['id']=$arr['id'];
                $_GET['name']=$user;
                $_GET['role']=1;
                $this->update('tp_project_assigne',$_GET);
            }else{//2）新负责人不在此项目
                $_GET['name']=$user;
                $_GET['role']=1;
                $_GET['project']=$id;
                $this->insert('tp_project_assigne',$_GET);
            }
            //3,原负责人设置为tester
            $where=array('project'=>$id,'name'=>$pending['draw']);
            $arr=findOne('tp_project_pending',$where);
            $this->delete('tp_project_assigne',$arr['id']);
//            //4,发送消息
            $who=['ylh','shenjiakai',$pending['reporter'],$pending['assignee'],$pending['draw'],$user];
            $who=array_unique($who);//去除重复的收信人
//                $who=[$user];
            $msg='【测试任务安排 - QC平台】
《【'.$pending['pkey'].'】'.$pending['pname'].'》任务的测试负责人由【'.getJiraName($pending['draw']).'】更换为【'.getJiraName($user).'】 ';
            $msg=$this->sendMessage($who,$msg);
            print_r($msg);
        }

    }
    //放弃
    function renounce()
    {
        if(1){
            $this->error('该功能暂时关闭！');
        }else{
            $name = getLoginUser();
            $project = I('project');
            $where = array('project' => $project, 'renounce' => '0');
            $where['name'] = array('neq', $name);
            $data = getList('tp_project_assigne',$where,'k desc');
            if ($data) {//有其他人可以转移
                //更改项目负责人
                $_GET = array();
                $_GET['id'] = $project;
                $_GET['draw'] = $data[0]['name'];
                $this->update('tp_project_pending',$_GET);
                $arr = findOne('tp_project_assigne',array('name' => $name, 'project' => $project, 'renounce' => '0'));
                //todo更改签码标识
                $_GET = array();
                $_GET['id'] = $arr['id'];
                $_GET['renounce'] = '1';
                $this->update('tp_project_assigne',$_GET);
            } else {
                $this->error("没有其他人可承接，你不能放弃该迭代");
            }
        }


    }
    //更改分派任务状态
    function changeStatus($issues, $status)
    {
        $table='tp_project_pending';
        $where = array('issueid' => $issues);
        $data = findOne($table,$where);
        $var['id'] = $data['id'];
        $var['issuestatus'] = $status;
        $var['status'] = '1';
        $arr = update($table,$var);
        return $arr;
    }

    public function add(){
        $url = '/' . C(PRODUCT) . '/Index/add';
        setCookieKey('url',$url,7*24*3600);
        $this->isLogin();
        $ststus=$this->statusDiect('issuestatus',10000,2);
        $this->assign('ststus', $ststus);
        $data=$this->get_dict_list('test_group');
        $pgroup=select($data,'pgroup',0);
        $this->assign('pgroup', $pgroup);

        $this->display();
    }

    function supple(){
        $table='tp_project_pending';
        $pkey=I('pkey');
        if($pkey){
            $data = $this->getIssueWithPkey($pkey);
            $arr = find($table,$data['id']);
            if($arr){
                $this->error(该任务已分派过，请不要重复分派);
            }else{
                $_POST['id']=$data['id'];
                $_POST['issuetype']=$data['issuetype'];
                $_POST['pname']=$data['summary'];
                $_POST['reporter'] = $data['reporter'];
                $_POST['assignee'] = $data['assignee'];
                $_POST['table']='tp_project_pending';
                $_POST['url']='/Jira/Index/pending';
                $this->insert($table,$_POST);
            }
        }else{
            $this->error('必须输入任务代号');
        }


    }

    //添加测试人员
    public function add_member(){
        $project=I('project');
        $pending = find('tp_project_pending',$project);
        $this->assign('data', $pending);
        //获取已分派测试人员
        $where=array('project'=>$project);
        $data=getList('tp_project_assigne',$where);
        $tester='';
        foreach ($data as $m){
            $tester[]=$m['name'];
        }
        //获取本小组测试人员
        $where=array('test_group'=>$pending['pgroup']);
        $member=getList('tp_test_group_member',$where);
        $testGroup='';
        foreach ($member as $m){
            $testGroup[]=$m['name'];
        }
        $adder=array_diff($testGroup,$tester);
        $arr=array();
        if($adder){
            foreach ($adder as $add){
                $arr[]= array('key'=>$add,'value'=>getJiraName($add));
            }
        }else{
            $adder=array_diff(C(QA_TESTER),$tester);
            foreach ($adder as $add){
                $arr[]= array('key'=>$add,'value'=>getJiraName($add));
            }
        }
        $name=select($arr,'name',0);
        //获取所有测试人员
        $this->assign('name', $name);
        $role=array(
            array('key'=>'0','value'=>'参与者'),
            array('key'=>'2','value'=>'观察者')
        );
        $role=select($role,'role',0);
        $this->assign('role', $role);

        $this->display();
    }

    function assigne(){
        $_POST['k']='1';
        $_POST['draw']='3';
        $this->insert('tp_project_assigne',$_POST);
    }


    public function hosts(){
        $table='tp_hosts';
        $where = array('type' => '0');
        $test = getList($table,$where,'hosts');
        $this->assign("test", $test);
        $where = array('type' => '1');
        $yufa = getList($table,$where,'hosts');
        $this->assign("yufa", $yufa);

        $this->display();
    }
}