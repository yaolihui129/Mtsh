<?php
namespace Admin\Controller;
class MerchantController extends BaseController
{
    //初始化数据
    function init(){
        $data = array(
            'table_merchant'      => 'merchant',
            'table_merchant_user' => 'merchant_user',
            'table_user'          => 'user',
            'table_merchant_app'  => 'merchant_app',
            'table_app'           => 'app',
            'name'                => 'Merchant',
            'where'               =>array('deleted'=>'0'),
            'order'               =>'id'
        );
        return $data;
    }
    //商户首页
    public function index()
    {
        //初始化
        $info = $this->init();
        $where=$info['where'];
        //处理查询条件
        $typeList=get_dict_list('merchant_type');
        $this->assign("typeList", $typeList);
        $type=I('type',$typeList[0]['key']);
        $this->assign("type", $type);
        $where['type']=$type;
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['name'] = array('like', '%' . $search . '%');
        //查询数据
        $data=M($info['table_merchant'])->where($where)->order($info['order'])->select();
        $this->assign("data", $data);

        $this->display();
    }
    //修改
    function merchant_update(){
        //初始化
        $info = $this->init();
        $_POST['table']=$info['table_merchant'];
        if(I('id')){
            $this->update();
        }else{
            $this->insert();
        }
    }
    //变更公司状态
    function merchant_status(){
        //初始化
        $info = $this->init();
        $_GET['table']=$info['table_merchant'];
        if(I('status')=='0'){
            $_GET['status']='1';
        }else{
            $_GET['status']='0';
        }
        $this->update();
    }
    //废弃
    function merchant_del(){
        //初始化
        $info = $this->init();
        $_GET['table']=$info['table_merchant'];
        $this->del();
    }
    //获取
    function merchant_info(){
        //初始化
        $info = $this->init();
        $data=M($info['table_merchant'])->find(I('id'));
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

    //商户下的用户
    public function merchant_user(){
        //初始化
        $info = $this->init();
        $where=$info['where'];
        //处理查询条件
        $order=$info['order'];
        $merchant=I('id');
        $this->assign("merchant", $merchant);
        $where['merchant_id']=$merchant;
        //查询数据
        $data=M($info['table_merchant_user'])->where($where)->order($order)->select();
        $this->assign("data", $data);
        //处理查询条件
        $map=$info['where'];
        $search = trim(I('search'));
        $this->assign('search', $search);
        $map['username|real_name|phone'] = array('like', '%' . $search . '%');

        if($data){
            $userList=array();
            foreach ($data as $da){
                $userList[]=$da['user_id'];
            }
            $map['id']=array('not in',$userList);
        }

        //查询数据
        $user=M($info['table_user'])->where($map)->select();

        $this->assign("user", $user);

        $this->display();
    }
    //添加用户
    function user_add(){
        //初始化
        $info = $this->init();
        $_GET['table']=$info['table_merchant_user'];
        $this->insert();
    }
    //撤销用户
    function user_del(){
        //初始化
        $info = $this->init();
        $_GET['table']=$info['table_merchant_user'];
        $this->del();
    }
    //商户下的应用
    public function merchant_app(){
        //初始化
        $info = $this->init();
        $where=$info['where'];
        //处理查询条件
        $order=$info['order'];
        $merchant=I('id');
        $this->assign("merchant", $merchant);
        $where['merchant_id']=$merchant;
        //查询数据
        $data=M($info['table_merchant_app'])->where($where)->order($order)->select();
        $this->assign("data", $data);
        //处理查询条件
        $map=$info['where'];
        $search = trim(I('search'));
        $this->assign('search', $search);
        $map['name|subtype|website'] = array('like', '%' . $search . '%');
        if($data){
            $appList=array();
            foreach ($data as $da){
                $appList[]=$da['app_id'];
            }
            $map['id']=array('not in',$appList);
        }
        //查询数据
        $app=M($info['table_app'])->where($map)->select();
        $this->assign("app", $app);

        $this->display();
    }
    //添加应用
    function app_add(){
        //初始化
        $info = $this->init();
        $_GET['table']=$info['table_merchant_app'];
        $this->insert();
    }
    //撤销应用
    function app_del(){
        //初始化
        $info = $this->init();
        $_GET['table']=$info['table_merchant_app'];
        $this->del();
    }

}