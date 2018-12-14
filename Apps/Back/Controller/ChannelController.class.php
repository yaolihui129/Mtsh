<?php
namespace Back\Controller;
class ChannelController extends BaseController
{
    //初始化数据
    function init(){
        $data = array(
            'table_channel'      => 'marketing_channel',
            'name'                => 'Channel',
            'where'               =>array(),
            'order'               =>'code'
        );
        return $data;
    }
    //渠道管理
    public function index()
    {
        $info=$this->init();
        $where=$info['where'];
        $merchant=getCookieKey('merchant');
        $this->assign("merchant", $merchant);
        $where['merchant_id']=array('in',[$merchant,'0']);
        $search = trim(I('search'));
        $this->assign('search', $search);
        $where['name|code'] = array('like', '%' . $search . '%');
        //查询数据
        $data=getList($info['table_channel'],$where,$info['order']);
        $this->assign("data", $data);
        $this->assign("count", sizeof($data));

        $where['parent']='0';
        $primaryChannel=getList($info['table_channel'],$where,$info['order']);
        $parent=array(
            array('key'=>'0','value' => '【0】&nbsp;--')
        );
        foreach ($primaryChannel as $channel){
            $parent[] = array(
                'key' => $channel['id'],
                'value' => '【' . $channel['code'] . '】' . $channel['name']
            );
        }
        $parent = select($parent, 'parent');
        $this->assign("parent", $parent);

        $this->display();
    }
    //修改渠道
    function channel_update(){
        //初始化
        $info = $this->init();
        if(I('id')){
            $this->update($info['table_channel'],$_POST);
        }else{
            $this->insert($info['table_channel'],$_POST);
        }
    }
    //废弃渠道
    function channel_del(){
        //初始化
        $info = $this->init();
        $this->delete($info['table_channel'],I('id'));
    }
    //获取渠道信息
    function channel_info(){
        //初始化
        $info = $this->init();
        $data=find($info['table_channel'],I('id'));
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