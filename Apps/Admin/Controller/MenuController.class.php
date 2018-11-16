<?php
namespace Admin\Controller;
class MenuController extends BaseController
{
    //初始化数据
    function init(){
        $data = array(
            'table_dict' => 'menu',
            'name'       => 'Menu',
            'where'      =>array('deleted'=>'0')
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
        $data=M($info['table_dict'])->where($where)->select();
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
        $data=M('dict')->where($where)->select();
        //返回数据
        $this->assign("data", $data);
        $this->assign("count", sizeof($data));

        $this->display();
    }
    //插入数据字典值
    function cha_ru_dict()
    {
        //初始化
        $info = $this->init();
        $_POST['table']=$info['table_dict'];
        $this->insert();
    }
    //更新数据字典值
    function geng_xin_dict(){
        //初始化
        $info = $this->init();
        $_POST['table']=$info['table_dict'];
        $this->update();
    }
    //废弃数据字典值
    function shan_chu_dict(){
        //初始化
        $info = $this->init();
        $_POST['table']=$info['table_dict'];
        $_GET['table']='dict';
        $this->del();
    }
    //获取数据字典值
    function dict_info(){
        //初始化
        $info = $this->init();
        $data=M($info['table_dict'])->find(I('id'));
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