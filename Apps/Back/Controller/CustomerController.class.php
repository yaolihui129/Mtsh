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
            'where'               => array('deleted'=>'0'),
            'order'               => 'id'
        );
        return $data;
    }
    //首页
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
    //公众号客户
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
        $data=getList($info['table_third'],$where,$info['order']);
        $this->assign("data", $data);

        $this->assign("count", sizeof($data));

        $this->display();
    }
    //拉取微信客户
    function getWechatCustomer(){
        $info = $this->init();
        $id=I('appId');
        $app=find($info['table_app'],$id);
        $res=$this->getWechatUsers($app['appid'],$app['next_openid']);
        $var=$this->saveOpenId($app['appid'],getCookieKey('merchant'),$res['data']['openid']);
        if($var){
           $this-> updateNextOpenid($id,$res['next_openid'],$var);
        }else{
            $this->success('没有新客户要同步');
        }
    }
    //存入OpenID
    function saveOpenId($appId,$merchantId,$res){
        $info = $this->init();
        $data['app_id']=$appId;
        $data['flag']='0';
        $data['merchant_id']=$merchantId;
        $data['source']='0';
        $id='';
        foreach ($res as $vo){
            $data['openid']=$vo;
            $var=findOne($info['table_third'],array('app_id'=>$appId,'openid'=>$vo));
            if(!$var){
                $res=$this->getWechatUserInfo($appId,$vo);
                if($res){
                    $data['nickname']=$res['nickname'];
                    $data['sex']=$res['sex'];
                    $data['city']=$res['city'];
                    $data['province']=$res['province'];
                    $data['country']=$res['country'];
                    $data['headimgurl']=$res['headimgurl'];
                    $data['country']=$res['country'];
                    $data['subscribe_time']=$res['subscribe_time'];
                    $data['remark']=$res['remark'];
                    $data['groupid']=$res['groupid'];
                    $data['tagid_list']=json_encode($res['tagid_list']);
                    $data['subscribe_scene']=$res['subscribe_scene'];
                    $data['qr_scene']=$res['qr_scene'];
                    $data['qr_scene_str']=$res['qr_scene_str'];
                }

                $id[]=insert($info['table_third'],$data);
            }
        }
        return $id;
    }

    //更新next_openid
    function updateNextOpenid($appId,$next_openid,$var){
        $info = $this->init();
        $data['id']=$appId;
        $data['next_openid']=$next_openid;
        $this->update($info['table_app'],$data,'','同步成功了'.sizeof($var).'条客户信息！');
    }

    //补充微信客户信息
    function updateWechatCustomerInfo($appId,$id){
        $info = $this->init();
        $data=find($info['table_third'],$id);
        $app=find($info['table_app'],$appId);
        $res=$this->getWechatUserInfo($app['appid'],$data['openid']);
        if($res){
            if($res['subscribe']=='1'){
                $data['nickname']=$res['nickname'];
                $data['sex']=$res['sex'];
                $data['city']=$res['city'];
                $data['province']=$res['province'];
                $data['country']=$res['country'];
                $data['headimgurl']=$res['headimgurl'];
                $data['country']=$res['country'];
                $data['subscribe_time']=$res['subscribe_time'];
                $data['remark']=$res['remark'];
                $data['groupid']=$res['groupid'];
                $data['tagid_list']=json_encode($res['tagid_list']);
                $data['subscribe_scene']=$res['subscribe_scene'];
                $data['qr_scene']=$res['qr_scene'];
                $data['qr_scene_str']=$res['qr_scene_str'];
            }else{
                $data['nickname']=$res['nickname'];
            }
            $this->update($info['table_third'],$data);
        }else{
            $this->error('对不起，暂时没有信息更新');
        }
    }

}