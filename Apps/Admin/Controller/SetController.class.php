<?php
namespace Admin\Controller;
class SetController extends BaseController
{
    //初始化数据
    function init(){
        $data = array(
            'table_dict' => 'dict',
            'name'       => 'Set',
            'where'      => array(),
            'order'      =>'id'
        );
        return $data;
    }
    //首页
    public function index()
    {
        
        $this->display();
    }
    //数据字典
    public function dict(){
        //初始化
        $info = $this->init();
        $where=$info['where'];
        //处理查询条件
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['type'] = array('like', '%' . $search . '%');
        //查询数据
        $data=getList($info['table_dict'],$where,$info['order']);
        //返回数据
        $typeList=array();
        foreach ($data as $da){
            if(!in_array($da['type'],$typeList)){
                $typeList[]=$da['type'];
            }
        }
        $this->assign("typeList", $typeList);
        $type=I('type',$typeList[0]);
        $this->assign("type", $type);
        $where['type']=$type;
        //查询数据
        $data=getList($info['table_dict'],$where,$info['order']);
        //返回数据
        $this->assign("data", $data);
        $this->assign("count", sizeof($data));

        $this->display();
    }
    //修改数值
    function dict_update(){
        //初始化
        $info = $this->init();
        if(I('id')){
            $this->update($info['table_dict'],$_POST);
        }else{
            $this->insert($info['table_dict'],$_POST);
        }
    }
    //废弃数据字典值
    function shan_chu_dict(){
        //初始化
        $info = $this->init();
        $this->delete($info['table_dict'],I('id'));
    }
    //获取数据字典值
    function dict_info(){
        //初始化
        $info = $this->init();
        $res=find($info['table_dict'],I('id'));
        $res=resFormat($res);
        $this->ajaxReturn($res);
    }

}