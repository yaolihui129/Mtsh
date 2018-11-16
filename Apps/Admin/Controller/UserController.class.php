<?php
namespace Admin\Controller;
class UserController extends BaseController
{
    //初始化数据
    function init(){
        $data = array(
            'table_user' => 'user',
            'name'       => 'User',
            'where'      => array('deleted'=>'0'),
            'order'      => 'id'
        );
        return $data;
    }
    public function index()
    {
        //初始化
        $info = $this->init();
        $where=$info['where'];
        //处理查询条件
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['name'] = array('like', '%' . $search . '%');
        //查询数据
        $data=M($info['table_user'])->where($where)->order($info['order'])->select();
        $this->assign("data", $data);

        $this->display();
    }

    //修改
    function user_update(){
        //初始化
        $info = $this->init();
        $_POST['table']=$info['table_user'];
        if(I('id')){
            $this->update();
        }else{
            $this->insert();
        }
    }
    //变更状态
    function user_status(){
        //初始化
        $info = $this->init();
        $_GET['table']=$info['table_user'];
        if(I('status')=='1'){
            $_GET['status']='2';
        }else{
            $_GET['status']='1';
        }
        $this->update();
    }
    //重置密码
    function reset_password(){
        //初始化
        $info = $this->init();
        $_GET['password']=md5('123456');
        $_GET['table']=$info['table_user'];
        $this->update();
    }
    //废弃
    function user_del(){
        //初始化
        $info = $this->init();
        $_GET['table']=$info['table_user'];
        $this->del();
    }
    //获取
    function user_info(){
        //初始化
        $info = $this->init();
        $data=M($info['table_user'])->find(I('id'));
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