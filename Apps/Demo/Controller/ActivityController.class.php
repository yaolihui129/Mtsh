<?php
namespace Demo\Controller;
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
        $info = $this->init();
        $id=I('id');
        //计算PV
        $this->countPV($id);
        $data=M($info['table_activity'])->find($id);
        $this->assign("data", $data);
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')){
            //微信浏览器
            $openid=cookie(C(appID).'_openid');
            if ($openid){
                $this->openidLogin(C(appID),$openid);
            }else{
                $_SESSION['uri'] =C(WEBSITE). '/'.C(PRODUCT).'/Activity/index/id/'.$id;
                //$scope='snsapi_base';
                $scope='snsapi_userinfo';
                $this->getBaseInfo($scope,$id);
            }
            $signPackage=$this->getSignPackage();
            $this->assign("signPackage", $signPackage);
            $link='https://xiuliguanggao.com/Jinruihs/Activity/index/id/'.$id;
            $this->assign("link", $link);
            $imgUrl='https://xiuliguanggao.com/Upload'.$data['img'];
            $this->assign("imgUrl", $imgUrl);
        }
        $user=cookie(C(appID).'_isLogin');
        $ip=GetIP();
        $this->countUV($id,$ip,$user);

        $this->display();
    }

    function countPV($id){
        $info = $this->init();
        $data=M($info['table_activity'])->find($id);
        $var['id']=$id;
        $var['clicknum']=$data['clicknum']+1;
        update($info['table_activity'],$var);
    }

    function countUV($id,$user){

        $info = $this->init();
        if($user){
            $var['activity_id']=$id;
            $var['customer_third_id']=$user;
            $var['access_date']=date('Y-m-d',time());
            $data=M($info['table_activity_uv'])->where($var)->find();
            if (!$data){
                insert($info['table_activity_uv'],$var);
            }
        }
    }

}