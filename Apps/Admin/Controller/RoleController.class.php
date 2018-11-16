<?php
namespace Admin\Controller;
class RoleController extends BaseController
{
    //初始化数据
    function init(){
        $data = array(
            'table_role' => 'role',
            'table_app'  => 'app',
            'name'       => 'Role',
            'where'      =>array('deleted'=>'0'),
            'order'      => 'id'
        );
        return $data;
    }

    public function index()
    {
        //初始化
        $info = $this->init();
        $where=$info['where'];

        $appList=get_list($info['table_app'],$where,'name');
        $this->assign("appList", $appList);
        $type=I('type',$appList[0]['key']);
        $this->assign("type", $type);
        $where['appid']=$type;

        //处理查询条件
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['name'] = array('like', '%' . $search . '%');
        //查询数据
        $data=M($info['table_role'])->where($where)->order($info['order'])->select();
        $this->assign("data", $data);

        $this->display();
    }

    //修改
    function role_update(){
        //初始化
        $info = $this->init();
        $_POST['table']=$info['table_role'];
        if(I('id')){
            $this->update();
        }else{
            $this->insert();
        }
    }

    //废弃
    function role_del(){
        //初始化
        $info = $this->init();
        $_GET['table']=$info['table_role'];
        $this->del();
    }
    //获取
    function role_info(){
        //初始化
        $info = $this->init();
        $data=M($info['table_role'])->find(I('id'));
        if($data){
            $res=array(
                'errorcode'=>'0',
                'message'=>'ok',
                'result'=>$data
            );
        }else{
            $res=array(
                'errorcode'=>'0',
                'message'=>'ok'
            );
        }
        $this->ajaxReturn($res);
    }

}