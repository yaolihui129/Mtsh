<?php
namespace Back\Controller;
class CustomerController extends BaseController
{

    //初始化数据
    function init(){
        $data = array(
            'table_customer'      => 'customer',
            'table_third'         => 'customer_third_party',
            'table_credit'        => 'admin_credit',
            'table_app'           => 'admin_app',
            'name'                => 'Customer',
            'where'               =>array('deleted'=>'0'),
            'order'               =>'id'
        );
        return $data;
    }

    public function index()
    {
        
        $this->display();
    }
    //B端用户（供应链）
    public function tob(){
        //初始化
        $info = $this->init();
        $where=$info['where'];
        //处理查询条件

        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['name'] = array('like', '%' . $search . '%');
        //查询数据
        $data=M($info['table_customer'])->where($where)->order($info['order'])->select();
        $this->assign("data", $data);

        $this->assign("count", sizeof($data));
        $this->display();
    }


    public function wechat(){
        //初始化
        $info = $this->init();
        $where=$info['where'];
        //处理查询条件
        $merchant=getCookieKey('merchant');
        $where['merchant_id']=$merchant;
        $publicNumberList=getPublicNumberList($merchant);
        $this->assign('publicNumberList', $publicNumberList);
        $publicNumber=I('publicNumber');
        if($publicNumber){
            setCookieKey('publicNumber',$publicNumber,24*3600);
        }else{
            $publicNumber=getCookieKey('publicNumber');
        }
        $where['app_id']=getName('admin_app',$publicNumber,'appid');
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['nickname|openid|unionid'] = array('like', '%' . $search . '%');

        //查询数据
        $data=M($info['table_third'])->where($where)->order($info['order'])->select();
        $data=getList($info['table_third'],$where,$info['order']);
        $this->assign("data", $data);

        $this->assign("count", sizeof($data));

        $this->display();
    }

  

}