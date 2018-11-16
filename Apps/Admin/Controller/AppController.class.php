<?php
namespace Admin\Controller;
class AppController extends BaseController
{
    //初始化数据
    function init(){
        $data = array(
            'table_app'  => 'app',
            'name'       => 'App',
            'where'      => array('deleted'=>'0'),
            'order'      =>'id'
        );
        return $data;
    }
    //APP首页
    public function index()
    {
        //初始化
        $info = $this->init();
        $where=$info['where'];
        //处理查询条件
        $typeList=get_dict_list('app_type');
        $this->assign("typeList", $typeList);
        $type=I('type',$typeList[0]['key']);
        $this->assign("type", $type);
        $where['type']=$type;
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['name|subtype|email|website|appid'] = array('like', '%' . $search . '%');
        //查询数据
        $data=M($info['table_app'])->where($where)->order($info['order'])->select();
        $this->assign("data", $data);

        $this->display();
    }
    //修改APP
    function app_update(){
        //初始化
        $info = $this->init();
        $_POST['table']=$info['table_app'];
        if(I('id')){
            $this->update();
        }else{
            $this->insert();
        }
    }
    //废弃APP
    function shan_chu_app(){
        //初始化
        $info = $this->init();
        $_GET['table']=$info['table_app'];
        $this->del();
    }
    //获取APP
    function app_info(){
        //初始化
        $info = $this->init();
        $data=M($info['table_app'])->find(I('id'));
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