<?php
namespace Jinruihs\Controller;
class ActivityController extends BaseController
{
    //初始化数据
    function init(){
        $data = array(
            'table_activity'      => 'marketing_activity',
            'table_activity_uv'   => 'marketing_activity_uv',
            'table_third'         => 'customer_third_party',
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
        $id=I('id');
        $this->countPV($id);
        $openid=cookie(C(appID).'_openid');
        $userid=cookie(C(appID).'_isLogin');

        if ($openid){
            $this->openidLogin(C(appID),$openid);
        }else{
            $_SESSION['uri'] =C(WEBSITE). '/'.C(PRODUCT).'/Activity/index/id/'.$id;
            //$scope='snsapi_base';
            $scope='snsapi_userinfo';
            $this->getBaseInfo($scope,$id);
        }
        $this->countUV($id,$userid);
        $data=M($info['table_activity'])->find($id);
        $this->assign("data", $data);
        $user=M($info['table_third'])->find($userid);
        $this->assign("user", $user);
        $where=array('activity_id'=>$id,'deleted'=>'0');
        $uv=M($info['table_activity_uv'])->where($where)->order('access_date desc')->select();
        $this->assign("uv", $uv);

        $this->display();
    }

    function countPV($id){
        //初始化
        $info = $this->init();
        $data=M($info['table_activity'])->find($id);
        $var['id']=$id;
        $var['clicknum']=$data['clicknum']+1;
        update($info['table_activity'],$var);
    }

    function countUV($id,$user){
        //初始化
        $info = $this->init();
        $var['activity_id']=$id;
        $var['customer_third_id']=$user;
        $var['access_date']=date('Y-m-d',time());
        $data=M($info['table_activity_uv'])->where($var)->find();
        if (!$data){
            insert($info['table_activity_uv'],$var);
        }


    }







}