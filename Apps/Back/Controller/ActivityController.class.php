<?php
namespace Back\Controller;
class ActivityController extends BaseController
{
    //初始化数据
    function init(){
        $data = array(
            'table_activity'      => 'marketing_activity',
            'name'                => 'Activity',
            'where'               =>array('deleted'=>'0'),
            'order'               =>'id'
        );
        return $data;
    }
    //首页
    public function index()
    {
        //初始化
        $info = $this->init();
        $where=$info['where'];
        //处理查询条件
        $typeList=get_dict_list('activity_type');
        $this->assign("typeList", $typeList);
        $type=I('type',$typeList[0]['key']);
        $this->assign("type", $type);
        $where['type']=$type;
        $where['app_id']=cookie(C(PRODUCT).'_app');
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['name'] = array('like', '%' . $search . '%');
//        dump(cookie(C(PRODUCT).'_app'));
        //查询数据
        $data=M($info['table_activity'])->where($where)->order($info['order'])->select();
        $this->assign("data", $data);

        $this->assign("count", sizeof($data));
        $this->assign("content",PublicController::editor("content"));

        $this->display();
    }

    //修改
    function activity_update(){
        //初始化
        $info = $this->init();
        if(!$_POST['img']){
            unset($_POST['img']);
        }
        $_POST['table']=$info['table_activity'];
        if(I('id')){
            $this->update();
        }else{
            $this->insert();
        }
    }
    //变更状态
    function activity_status(){
        //初始化
        $info = $this->init();
        $_GET['table']=$info['table_activity'];
        if(I('status')=='0'){
            $_GET['status']='1';
        }else{
            $_GET['status']='0';
        }
        $this->update();
    }
    //废弃
    function activity_del(){
        //初始化
        $info = $this->init();
        $_GET['table']=$info['table_activity'];
        $this->del();
    }
    //修改内容
    function activity_content(){
        //初始化
        $info = $this->init();
        $data=M($info['table_activity'])->find(I('id'));
        $this->assign("data", $data);
        $content=PublicController::editor("content",$data['content'],'desc',400);
        $this->assign("content",$content);
    }
    //获取
    function activity_info(){
        //初始化
        $info = $this->init();
        $data=M($info['table_activity'])->find(I('id'));
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