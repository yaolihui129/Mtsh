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
            'where'               =>array(),
            'order'               =>'id'
        );
        return $data;
    }
    //商户首页
    public function index()
    {
        $info = $this->init();
        $where=$info['where'];
        //处理查询条件
        $typeList=getDictList('merchant_type','dict');
        $this->assign("typeList", $typeList);
        $type=I('type',$typeList[0]['key']);
        $this->assign("type", $type);
        $where['type']=$type;
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['name'] = array('like', '%' . $search . '%');
        //查询数据
        $data=getList($info['table_merchant'],$where,$info['order']);
        $this->assign("data", $data);

        $this->display();
    }
    //修改
    function merchant_update(){
        //初始化
        $info = $this->init();
        if(I('id')){
            $this->update($info['table_merchant'],$_POST);
        }else{
            $this->insert($info['table_merchant'],$_POST);
        }
    }
    //变更公司状态
    function merchant_status(){
        //初始化
        $info = $this->init();
        if(I('status')=='0'){
            $_GET['status']='1';
        }else{
            $_GET['status']='0';
        }
        $this->update($info['table_merchant'],$_GET);
    }
    //废弃
    function merchant_del(){
        //初始化
        $info = $this->init();
        $this->delete($info['table_merchant'],I('id'));
    }
    //获取
    function merchant_info(){
        //初始化
        $info = $this->init();
        $res=find($info['table_merchant'],I('id'));
        $res=resFormat($res);
        $this->ajaxReturn($res);
    }

    //商户下的用户
    public function merchant_user(){
        //初始化
        $info = $this->init();
        $where=$info['where'];
        //处理查询条件
        $merchant=I('id');
        $this->assign("merchant", $merchant);
        $where['merchant_id']=$merchant;
        //查询数据
        $data=getList($info['table_merchant_user'],$where,$info['order']);
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
        $user=getList($info['table_user'],$map);

        $this->assign("user", $user);

        $this->display();
    }
    //添加用户
    function user_add(){
        //初始化
        $info = $this->init();
        $this->insert($info['table_merchant_user'],$_GET);
    }
    //撤销用户
    function user_del(){
        //初始化
        $info = $this->init();
        $this->delete($info['table_merchant_user'],I('id'));
    }
    //商户下的应用
    public function merchant_app(){
        //初始化
        $info = $this->init();
        $where=$info['where'];
        //处理查询条件
        $merchant=I('id');
        $this->assign("merchant", $merchant);
        $where['merchant_id']=$merchant;
        //查询数据
        $data=getList($info['table_merchant_app'],$where,$info['order']);
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
        $app=getList($info['table_app'],$map);
        $this->assign("app", $app);

        $this->display();
    }
    //添加应用
    function app_add(){
        //初始化
        $info = $this->init();
        $this->insert($info['table_merchant_app'],$_GET);
    }
    //撤销应用
    function app_del(){
        //初始化
        $info = $this->init();
        $this->delete($info['table_merchant_app'],I('id'));
    }

}