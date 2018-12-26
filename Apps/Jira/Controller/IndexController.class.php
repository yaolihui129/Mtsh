<?php
namespace Jira\Controller;
class IndexController extends WebInfoController
{
    public function index()
    {
        $_SESSION['type']['index'] = I('type');
        if ($_SESSION['type']['index'] == 'done') {
            $where['issuestatus'] = array('in', '3,10002,6');
        } elseif ($_SESSION['type']['index'] == 'assigned') {
            $_SESSION['url'] = '/' . C('PRODUCT') . '/Index/assigned/project/' . $_SESSION['project'];
            $this->isLogin();
            $where['issuestatus'] = array('in', '1,10000,10016,10017');
        } else {
            $where['issuestatus'] = array('not in', '1,3,10000,10002,10015,10016,10017,6');
        }

        $_SESSION['project'] = I('project', '10006');
        $pro = M('project')->find($_SESSION['project']);
        $_SESSION['pkey'] = $pro['pkey'];
        $_SESSION['pname'] = $pro['pname'];
        $where['PROJECT'] = intval($_SESSION['project']);
        $where['issuetype'] = array('in', '10005,10006');

        $search = trim(I('search'));
        $_SESSION['search']['index'] = $search;
        $this->assign('search', $search);

        $where['SUMMARY|issuenum'] = array('like', '%' . $search . '%');
        $data = postIssue($where);
        $this->assign('data', $data);
        $this->assign('count',sizeof($data));
        $this->assign('project', $this->projectDict());

        $this->display();
    }

    public function assigned()
    {
        $_SESSION['type']['index'] = I('type');
        if ($_SESSION['type']['index'] == 'done') {
            $where['issuestatus'] = array('in', '3,10002');
        } elseif ($_SESSION['type']['index'] == 'assigned') {
            $_SESSION['url'] = '/' . C('PRODUCT') . '/Index/assigned/project/' . $_SESSION['project'];
            $this->isLogin();
            $where['issuestatus'] = array('in', '1,10000,10015,10016,10017');
        } else {
            $where['issuestatus'] = array('not in', '1,3,10000,10002,10015');
        }
        $_SESSION['project'] = I('project', '10006');
        $where['PROJECT'] = intval($_SESSION['project']);
        $where['issuetype'] = array('in', '10005,10006');

        $search = trim(I('search'));
        $this->assign('search', $search);

        $where['SUMMARY|issuenum'] = array('like', '%' . $search . '%');
        $this->assign('data', postIssue($where));
        $this->assign('project', $this->projectDict());

        $this->display();
    }

    public function started()
    {
        $_SESSION['type']['index'] = I('type');
        if ($_SESSION['type']['index'] == 'done') {
            $where['issuestatus'] = array('in', '3,10002');
        } elseif ($_SESSION['type']['index'] == 'assigned') {
            $_SESSION['url'] = '/' . C('PRODUCT') . '/Index/assigned/project/' . $_SESSION['project'];
            $where['issuestatus'] = array('in', '10000,10016,10017');
        } else {
            $where['issuestatus'] = array('not in', '1,3,10000,10002,10015');
        }
        $_SESSION['project'] = I('project', '10006');
        $where['PROJECT'] = intval($_SESSION['project']);
        $where['issuetype'] = array('in', '10005,10006');

        $search = trim(I('search'));
        $_SESSION['search']['index'] = $search;
        $this->assign('search', $search);

        $where['SUMMARY|issuenum'] = array('like', '%' . $search . '%');
        $where['order']='issuenum desc';
        $pending = getList('tp_project_pending',array());
        if ($pending) {
            $p=array();
            foreach ($pending as $pend) {
                $p[] = $pend['id'];
            }
            $where['ID'] = array('not in', $p);
        }
        $this->assign('data', postIssue($where));
        $this->assign('group', $this->getDictList('test_group'));
        $this->assign('project', $this->projectDict());

        $this->display();
    }

    public function pending()
    {
        $_SESSION['url'] = '/' . C('PRODUCT') . '/Index/pending';
        $this->isLogin();
        $map['status'] = array('in' , '0,1,2');
        $map['deleted']='0';
        $draw=I('draw');
        $this->assign('draw', $draw);
        if($draw){
            $map['draw'] = $draw;
        }
        $table='tp_project_pending';
        $order='status desc,ctime desc';
        $data = getList($table,$map,$order);
        if ($draw){
            foreach ($data as $da){
                $this->synch_issuestatus($da['id']);
            }
            $data = getList($table,$map,$order);
        }
        $this->assign('data', $data);

        if(getLoginUser()=='ylh'){
            $editable2=1;
        }else{
            $editable2=0;
        }
        $this->assign('editable2', $editable2);
        $user=C('QA_TESTER');
        $this->assign('user', $user);
        $testGroup = getList('tp_test_group_member',array(),'test_group');
        $this->assign('group', $testGroup);

        $this->display();
    }
    //加入分派
    function jion()
    {
        if (getLoginUser() == 'ylh') {
            $id = I('issueid');
            $data= getIssue($id);
            $_GET['table'] = 'tp_project_pending';
            $_GET['id'] = $id;
            $_GET['pkey'] = $_SESSION['pkey'] . '-' . $data['issuenum'];
            $_GET['pgroup'] = I('pgroup');
            $_GET['reporter'] = $data['reporter'];
            $_GET['assignee'] = $data['assignee'];
            $_GET['issuetype'] = $data['issuetype'];
            $_GET['issuestatus'] = $data['issuestatus'];
            $_GET['pname'] = $data['summary'];
            $this->insert();
        } else {
            $this->error('对不起，你没有权限！');
        }
    }
    //撤回
    function chehui()
    {
        $_GET['id'] = I('project');
        $_GET['table'] = 'tp_project_pending';
        $this->del();
    }
    //完成
    function done(){
        $where['SUMMARY'] = array('like', '%【' . I('pkey') . '】%');
        $where['PROJECT'] =intval($_SESSION['project']);
        $where['issuetype'] = '10102';
        $data= postIssue($where);
        $project = I('project');
        $_GET=array();
        $_GET['id'] = $project;
        $_GET['status'] = '1';
        if($data[0]){
            $_GET['planid'] = $data[0]['id'];
            $_GET['plankey'] =$_SESSION['pkey'].'-'.$data[0]['issuenum'];
        }
        $_GET['table'] = 'tp_project_pending';
        $this->update();
    }
    //结束
    function jieshu(){
        $_GET['id'] = I('project');;
        $_GET['status'] = '3';
        $_GET['table'] = 'tp_project_pending';
        $this->update();
    }
    //抽签
    function draw()
    {
        $this->isLogin();
        $name=getLoginUser();
        if (in_array($name, C('QA_TESTER'))) {
            $project = I('project');
            $draw = C('DRAW');
            $key = rand(0, sizeof($draw) - 1);
            $draw = $draw[$key];
            //2.先查库，如果有值直接返回“您已经完成抽签”
            $where = array('project' => $project);
            $table='tp_project_assigne';
            $data = getList($table,$where);
            $user=array();
            $arr=array();
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
                    $_GET['table'] =$table;
                    $this->insert();
                    $data = getList($table,$where,'k desc');
                    $num=sizeof($data);
                    if ($num >= (sizeof(C('QA_TESTER')) - 1)) {
                        $_GET = array();
                        $_GET['id'] = $project;
                        $_GET['draw'] = $data[0]['name'];
                        $_GET['checkd'] = $data[$num-1]['name'];
                        $_GET['table'] = 'tp_project_pending';
                        $this->update();
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
        $arr= find('tp_project_pending',$id);
        if($arr['draw']){
            $this->error('已指派负责人，请不要重复指派');
        }else{
            //1.更新tp_project_pending
            $_GET['table']='tp_project_pending';
            $this->update();
            //2.发送企业微信消息
            $who=['ylh','shenjiakai',$arr['reporter'],$arr['assignee'],$draw];
            $this->msgZhiPai($who,$arr,$draw);
            //3.写入tp_project_assigne
            $_GET=array();
            $_GET['name']=$draw;
            $_GET['k']=13;
            $_GET['draw']='JOKER';
            $_GET['project']=$id;
            $_GET['role']='1';
            $_GET['table']='tp_project_assigne';
            $this->insert();
        }
    }
    //换人
    function huanren(){
        $id=I('id');
        $user=I('user');
        $table='tp_project_pending';
        $pending=find($table,$id);
        if($user==$pending['draw']){
            $this->error('不能更换为原负责人');
        }else{
            //1,更新负责人
            $_GET['draw']=$user;
            $_GET['table']=$table;
            $this->update();
            //2,新负责人设置为manger
            $table='tp_project_assigne';
            $where=array('project'=>$id,'name'=>$user);
            $arr=findOne($table,$where);
            $_GET=array();
            if($arr){//1)新负责人在此项目
                $_GET['id']=$arr['id'];
                $_GET['name']=$user;
                $_GET['role']=1;
                $_GET['table']=$table;
                $this->update();
            }else{//2）新负责人不在此项目
                $_GET['name']=$user;
                $_GET['role']=1;
                $_GET['project']=$id;
                $_GET['table']=$table;
                $this->insert();
            }
            //3,原负责人设置为tester
            $where=array('project'=>$id,'name'=>$pending['draw'],);
            $arr=findOne($table,$where);
            $_GET=array();
            $_GET['id']=$arr['id'];
            $_GET['table']=$table;
            $this->del();
            //4,发送消息
            $who=['ylh','shenjiakai',$pending['reporter'],$pending['assignee'],$pending['draw'],$user];
            $this->msgHuanRen($who,$pending,$user);
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
            $where = array('project' => $project, 'renounce' => '0', );
            $where['name'] = array('neq', $name);
            $table='tp_project_assigne';
            $data = getList($table,$where,'k desc');
            if ($data) {//有其他人可以转移
                //更改项目负责人
                $_GET = array();
                $_GET['id'] = $project;
                $_GET['draw'] = $data[0]['name'];
                $_GET['table'] = 'tp_project_pending';
                $this->update();
                $where=array('name' => $name, 'project' => $project, 'renounce' => '0');
                $arr = findOne($table,$where);
                //todo更改签码标识
                $_GET = array();
                $_GET['id'] = $arr['id'];
                $_GET['renounce'] = '1';
                $_GET['table'] = $table;
                $this->update();
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
        $arr= update($table,$var);
        return $arr;
    }

    public function add(){
        $_SESSION['url'] = '/' . C('PRODUCT') . '/Index/add';
        $this->isLogin();
        $this->assign('ststus', $this->statusDiect('issuestatus',10000,2));
        $data=$this->getDictList('test_group');
        $this->assign('pgroup', select($data,'pgroup',0));
        $this->display();
    }

    function supple(){
        $key=I('pkey');
        if($key){
            $table='tp_project_pending';
            $data=getIssuenum($key);
            $arr = find($table,$data['id']);
            if($arr){
                $this->error('该任务已分派过，请不要重复分派');
            }else{
                $_POST['id']=$data['id'];
                $_POST['issuetype']=$data['issuetype'];
                $_POST['pname']=$data['summary'];
                $_POST['id']=$data['id'];
                $_POST['reporter'] = $data['reporter'];
                $_POST['assignee'] = $data['assignee'];
                $_POST['table']=$table;
                $_POST['url']='/Jira/Index/pending';
                $this->insert();
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
        $data =getList('tp_project_assigne',array('project'=>$project),'id','name');
        $tester=array();
        foreach ($data as $m){
            $tester[]=$m['name'];
        }
        //获取本小组测试人员
        $member =getList('tp_test_group_member',array('test_group'=>$pending['pgroup']),'id','name');
        $testGroup=array();
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
            $adder=array_diff(C('QA_TESTER'),$tester);
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
        $this->assign('role', select($role,'role',0));
        $this->display();
    }

    function assigne(){
        $_POST['k']='1';
        $_POST['draw']='3';
        $_POST['table']='tp_project_assigne';
        $this->insert();
    }


    public function hosts(){
        $table='tp_hosts';
        $this->assign("test", getList($table,array('type' => '0'),"hosts"));
        $this->assign("yufa", getList($table,array('type' => '1'),"hosts"));

        $this->display();
    }

}