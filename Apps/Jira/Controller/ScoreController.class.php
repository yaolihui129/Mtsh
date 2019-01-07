<?php
namespace Jira\Controller;
class ScoreController extends WebInfoController
{
    public function index()
    {
        $table='tp_my_score';
        $data = getList($table, array(),'ctime desc');
        $saiJi = array();
        foreach ($data as $da) {
            if (!in_array($da['quarter'], $saiJi)) {
                $saiJi[] = $da['quarter'];
            }
        }
        $this->assign("saiJi", $saiJi);

        $quarter = I('quarter', C('KH_QUARTER'));
        $this->assign("quarter", $quarter);

        $where = array('quarter' => $quarter);
        $user = array_diff(C('QA_TESTER'), array('ylh'));
        $where['user'] = array('in', $user);
        $table='tp_score_list';
        $this->assign("data", getList($table,$where,'score desc'));

        $this->display();
    }
    //更多
    public function gengduo()
    {
        $table='tp_my_score';
        $quarter = I('quarter', C('KH_QUARTER'));
        $this->assign("quarter", $quarter);
        $user = I('user',getLoginUser());
        $this->assign("user", $user);

        $where = array('user' => $user, 'quarter' => $quarter);

        $this->assign("data", getList($table,$where,'ctime desc'));
        $this->assign("score", sumScore($user, $quarter));
        $this->assign("loginUser", getLoginUser());

        $this->display();
    }
    //申诉
    public function doAppeal()
    {
        $table='tp_my_score';
        $arr =find($table,I('id'));
        if ($arr['dissent']) {
            $this->assign("arr", $arr);
            $shix = $arr['ctime'] + 7 * 24 * 3600;
            $this->assign("shix", date('Y-m-d H:i:s', $shix));
        } else {
            $this->error('考核不允许申诉');
        }
        $this->display();
    }
    //考核积分
    public function appraisal(){
        $user = ['ylh', 'cf'];
        $dissent = array(
            array('key' => '1', 'value' => '允许申诉'),
            array('key' => '0', 'value' => '不允许申诉'),
        );
        $testers = C('QA_TESTER');
        $this->assign("testers", $testers);
        $tester=I('tester', $testers[0]);
        setCache('Appraisal_tester',$tester);
        $this->assign("tester", $tester);
        if (in_array(getLoginUser(), $user)) {
            $penging=array();
            $map['status']=array('in','1,2');
            $pending=getList('tp_project_pending',$map);
            foreach ($pending as $k => $pend){
                $penging[$k]['key']=$pend['id'];
                $penging[$k]['value']='【'.$pend['pkey'].'】'.$pend['pname'].'('.$pend['id'].')';
            }
            $this->assign("penging", $penging);
            $project=getCache('project');
            $this->assign('project', $project);
            $tp=I('tp');
            $this->assign("tp", $tp);

            if($tp){
                $data=getIssue($tp);
                $summary='【'.getCache('pkey'). $data['issuenum'] .'】'.$data['summary'] ;
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
                $ceShiRen=array();
                foreach ($data as $da){
                    $case = getCycleTestRun($da['id']);
                    foreach ($case as $ca){
                        if($ca['executed_by']){
                            if(!in_array($ca['executed_by'], $ceShiRen)){
                                $ceShiRen[]=$ca['executed_by'];
                            }
                        }
                    }
                }
                $this->assign("ceshiren", $ceShiRen);
            }else{
                $summary = '【未关联】迭代或项目';
            }
            $this->assign("summary", $summary);

            $this->assign("score", sumScore(getCache('Appraisal_tester'), C('KH_QUARTER')));
            $this->assign("quarter", C('KH_QUARTER'));

            //封装加分项下拉
            $where = array('project' => '0', 'type' => '1');
            $map  = array('project' => '0', 'type' => '2' );
            $dissent = select($dissent, 'dissent', '0');
            $var = array(
                'quarter' => C('KH_QUARTER'),
                'user' => getCache('Appraisal_tester')
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
        $where = array('status' => '1');
        $this->assign("acount", countId($table,$where));

        $where = array('quarter' => C('KH_QUARTER'), 'status' => '2');
        $this->assign("data", getList($table,$where));
        $this->assign("dcount", countId($table,$where));

        $where = array('quarter' => C('KH_QUARTER'), 'status' => '3');
        $this->assign("rcount", countId($table,$where));

        $this->display();
    }
    //申诉被驳回
    public function reject()
    {
        $table='tp_my_score';
        $where = array('status' => '1');
        $this->assign("acount", countId($table,$where));

        $where = array('quarter' => C('KH_QUARTER'), 'status' => '2');
        $this->assign("dcount", countId($table,$where));

        $where = array('quarter' => C('KH_QUARTER'), 'status' => '3');
        $this->assign("data", getList($table,$where));
        $this->assign("rcount", countId($table,$where));

        $this->display();
    }
    //插入数据
    function chaRu()
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
    function boHui()
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
        $arr= getList($table,$where);
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

}