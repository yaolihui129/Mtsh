<?php
namespace Admin\Controller;
class AppController extends BaseController
{
    //初始化数据
    function init(){
        $data = array(
            'table_app'  => 'app',
            'name'       => 'App',
            'where'      => array(),
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
        $typeList=get_dict_list('app_type','dict');
        $this->assign("typeList", $typeList);
        $type=I('type',$typeList[0]['key']);
        $this->assign("type", $type);
        $where['type']=$type;
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['name|subtype|email|website|appid'] = array('like', '%' . $search . '%');
        //查询数据
        $data=getList($info['table_app'],$where,$info['order']);
        $this->assign("data", $data);

        $this->display();
    }
    //修改APP
    function app_update(){
        //初始化
        $info = $this->init();
        if(I('id')){
            $this->update($info['table_app'],$_POST);
        }else{
            $this->insert($info['table_app'],$_POST);
        }
    }
    //废弃APP
    function shan_chu_app(){
        $info = $this->init();
        $this->delete($info['table_app'],I('id'));
    }
    //获取APP
    function app_info(){
        $info = $this->init();
        $data=find($info['table_app'],I('id'));
        $res=resFormat($data);
        $this->ajaxReturn($res);
    }


  

}