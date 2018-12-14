<?php
namespace Jira\Controller;
class ApiController extends WebInfoController
{

    //接口文档
    public function index()
    {

        $search = I('search');
        $this->assign('search', $search);

        $where = array('removed' => '0');
        $where['apiName|apiURI'] = array('like', '%' . $search . '%');
        $where['apiName'] = array('neq', '示例接口');
        $project = $this->projectID();
        $this->assign('project', $project);
        $branch=I('branch');
        $this->assign('branch', $branch);
        if($branch){
            $where['projectID']=$branch;
        }else{
            $where['projectID'] = array('in', $project);
        }
        $data = M('eo_api')->where($where)->order('projectID')->select();
        $this->assign('data', $data);

        $this->display();
    }

    //接口用例
    public function apicase(){
        $search = I('search');
        $this->assign('search', $search);

        $where = array('removed' => '0');
        $where['caseName|caseDesc'] = array('like', '%' . $search . '%');
        $project = $this->projectID();
        $this->assign('project', $project);
        $branch=I('branch');
        $this->assign('branch', $branch);
        if($branch){
            $where['projectID']=$branch;
        }else{
            $where['projectID'] = array('in', $project);
        }
        $data = M('eo_project_test_case')->where($where)->order('projectID')->select();
        $this->assign('data', $data);

        $this->display();
    }

    //标记性能
    function pressure(){
        $_SESSION['url'] = '/' . C(PRODUCT) . '/Api/index';
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

    //API详情
    public function details()
    {
        $id = I(id);
        $data = M('eo_api')->find($id);
        $this->assign('data', $data);

        $this->assign("apinote", PublicController::editor("apinote", $data['apinote']));
        $this->assign("mockresult", PublicController::editor("mockresult", $data['mockresult']));
        $this->assign("apirequestraw", PublicController::editor("apirequestraw", $data['apirequestraw']));

        $where=array('apiID'=>$id);
        $parameter = M('eo_api_request_param')->where($where)->select();
        $this->assign('parameter', $parameter);
        $this->assign('count', sizeof($parameter));

        $this->display();
    }
    //API性能测试场景
    public function press(){
        $id = I(id);
        $_SESSION['url'] = '/' . C(PRODUCT) . '/Api/press/id/'.$id;
        $this->isLogin();
        $data = M('eo_api')->find($id);
        $this->assign('data', $data);

        $project=I('project');
        $this->assign('project', $project);
        $scheme=I('scheme');
        $this->assign('scheme', $scheme);

        if($project){
            $where = array('api' => $id, 'project'=>$project,'deleted' => '0');
        }else{
            $where = array('api' => $id, 'deleted' => '0');
        }
        $scene = M('tp_api_scene_pressure')->where($where)->order('project,sn,id')->select();
        $this->assign('scene', $scene);

        $this->display();
    }

    function selected(){
        $id=I('id');
        $table='tp_api_scene_pressure';
        $where=array('press_type'=>I('press_type'),'project'=>I('project'),'deleted'=>'0');
        $where['id']=array('not in',[$id]);
        $data=M($table)->where($where)->select();
        if($data){//取消选中状态
            foreach ($data as $da){
                $_GET['id']=$da['id'];
                $_GET['selected']='1';
                $_GET['table']=$table;
                $this->update();
            }
        }
        //设置选中状态
        $_GET=array();
        $_GET['id']=$id;
        $_GET['selected']='1';
        $_GET['table']=$table;
        $this->update();

    }

    function update_pressure()
    {
        $_POST['table']='tp_api_scene_pressure';
        $this->update();
    }

    //API压测记录
    public function pressure_test(){
        $id = I('id');
        $this->assign('id', $id);
        $url = '/' . C(PRODUCT) . '/Api/pressure_test/id/'.$id;
        cookie('url',$url,array('prefix'=>C(PRODUCT).'_'));
        $this->isLogin();

        $arr = M('tp_api_scene_pressure')->find($id);
        $this->assign('arr', $arr);

        $user=jie_mi(cookie(C(PRODUCT).'_user'));
        $this->assign('user', $user);

        $project=I('project');
        $this->assign('project', $project);

        $scheme=I('scheme');
        $this->assign('scheme', $scheme);

        $where = array('scene' => $id, 'deleted' => '0');
        $data = M('tp_api_scene_pressure_test')->where($where)->order('id')->select();
        $this->assign('data', $data);

        $this->assign('t_time', date('Y-m-d H:i:s',time()));

        $this->display();
    }
    //标记结果
    function record_result(){
        $scene=I('scene');
        $url = '/' . C(PRODUCT) . '/Api/pressure_test/id/'.$scene;
        cookie('url',$url,array('prefix'=>C(PRODUCT).'_'));
        $this->isLogin();
        $arr =M('tp_api_scene_pressure_test')->find(I('id'));
        $_GET['id']=$scene;
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