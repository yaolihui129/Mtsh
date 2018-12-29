<?php
namespace Jira\Controller;
class ApiController extends WebInfoController
{

    //接口文档
    public function index()
    {
        $search = I('search');
        $this->assign('search', $search);

        $where = array();
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
        $data = getList('eo_api',$where,'projectID');
        $this->assign('data', $data);

        $this->display();
    }
    //接口用例
    public function apicase(){
        $search = I('search');
        $this->assign('search', $search);

        $where = array();
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
        $data = getList('eo_project_test_case',$where,'projectID');
        $this->assign('data', $data);

        $this->display();
    }

    //API详情
    public function details()
    {
        $id = I('id');
        $data = find('eo_api',$id);
        $this->assign('data', $data);

        $this->assign("apinote", PublicController::editor("apinote", $data['apinote']));
        $this->assign("mockresult", PublicController::editor("mockresult", $data['mockresult']));
        $this->assign("apirequestraw", PublicController::editor("apirequestraw", $data['apirequestraw']));

        $where=array('apiID'=>$id);
        $parameter = getList('eo_api_request_param',$where,'paramID');
        $this->assign('parameter', $parameter);
        $this->assign('count', sizeof($parameter));

        $this->display();
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

        if($project){
            $where = array('api' => $id, 'project'=>$project);
        }else{
            $where = array('api' => $id);
        }
        $scene = getList('tp_api_scene_pressure',$where,'project,sn,id');
        $this->assign('scene', $scene);
        $this->assign('c', sizeof($scene));

        $this->display();
    }

    function selected(){
        $id=I('id');
        $table='tp_api_scene_pressure';
        $where=array('press_type'=>I('press_type'),'project'=>I('project'));
        $where['id']=array('not in',[$id]);
        $data =getList($table,$where);
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
        $url = '/' . C('PRODUCT') . '/Api/pressure_test';
        $this->isLogin($url);
        $id = I('id');
        $this->assign('id', $id);
        $arr = find('tp_api_scene_pressure',$id);
        $this->assign('arr', $arr);

        $project=I('project');
        $this->assign('project', $project);
        $scheme=I('scheme');
        $this->assign('scheme', $scheme);

        $where = array('scene' => $id);
        $data = getList('tp_api_scene_pressure_test',$where);
        $this->assign('data', $data);

        $this->assign('t_time', date('Y-m-d H:i:s',time()));

        $this->display();
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