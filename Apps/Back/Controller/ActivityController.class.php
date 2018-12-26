<?php
namespace Back\Controller;
class ActivityController extends BaseController
{
    //初始化数据
    function init(){
        $data = array(
            'table_activity'      => 'marketing_activity',
            'table_activity_uv'   => 'marketing_activity_uv',
            'table_channel'       => 'marketing_channel',
            'table_scene'         => 'wechat_scene',
            'table_app'           => 'admin_app',
            'name'                => 'Activity',
            'where'               => array(),
            'order'               =>'sn desc'
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
        $typeList=getDictList('activity_type','admin_dict');
        $this->assign("typeList", $typeList);
        $type=I('type',$typeList[0]['key']);
        $this->assign("type", $type);
        $where['type']=$type;
        $where['app_id']=getCookieKey('app');
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['name'] = array('like', '%' . $search . '%');
        //查询数据
        $data=getList($info['table_activity'],$where,$info['order']);
        $this->assign("data", $data);

        $this->assign("count", sizeof($data));
        $this->assign("content",PublicController::editor("content"));

        $this->display();
    }
    //上传图片
    public function img(){
        //初始化
        $info = $this->init();
        $data=find($info['table_activity'],I('id'));
        $this->assign("arr", $data);

        $this->display();
    }
    //上传图片接口
    function imgUP(){
        //初始化
        $info = $this->init();
        $_POST['url']='/' . C('PRODUCT') . '/Activity';
        $this->update($info['table_activity'],$_POST,'img');
    }
    //活动详情
    public function details(){
        //初始化
        $info = $this->init();
        $id=I('id');
        $data=find($info['table_activity'],$id);
        $this->assign("data", $data);

        $where=array('activity_id'=>$id);
        $uv=getList($info['table_activity_uv'],$where,'access_date desc');
        $this->assign("uv", $uv);

        $this->display();
    }
    //修改
    function activity_update(){
        //初始化
        $info = $this->init();
        if(I('id')){
            $this->update($info['table_activity'],$_POST);
        }else{
            $this->insert($info['table_activity'],$_POST);
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
        $this->update($info['table_activity'],$_GET);
    }
    //废弃
    function activity_del(){
        //初始化
        $info = $this->init();
        $this->delete($info['table_activity'],$_GET['id']);
    }
    //修改内容
    function activity_content(){
        //初始化
        $info = $this->init();
        $data=find($info['table_activity'],I('id'));
        $this->assign("data", $data);
        $content=PublicController::editor("content",$data['content'],'desc',350);
        $this->assign("content",$content);

        $this->display();
    }
    //获取
    function activity_info(){
        //初始化
        $info = $this->init();
        $res=find($info['table_activity'],I('id'));
        $res=resFormat($res);
        $this->ajaxReturn($res);
    }
    //投放渠道
    public function channel(){

        $this->display();
    }

    //获取二维码
    function getActivityQrCode(){
        //初始化
        $info = $this->init();
        $id=I('id');
        $var=find($info['table_activity'],$id,'qr_code,expire');
        //判定是否过期
        if(time()-$var['expire']>0){
            //获取场景ID
            $arr=find($info['table_app'],getCookieKey('publicNumber'));
            $where['appid']=$arr['appid'];
            $where['marketing']=getCookieKey('merchant');
            $where['activityid']=$id;
            $data=findOne($info['table_scene'],$where,'id desc','id');
            if(!$data){
                $where['url']='https://xiuliguanggao.com/Jinruihs'.'/Activity/index/id/'.$id;
                $data['id']=insert($info['table_scene'],$where);
            }
            $time=15*24*60*60;
            $var['id']=$id;
            $var['qr_code']=$this->getTimeQrCode($data['id'],$arr['appid'],$time);
            $var['expire']=time()+$time*24*60*60;
            $this->update($info['table_activity'],$var);
        }else{
            $this->error('二维码还未过期');
        }

    }


}