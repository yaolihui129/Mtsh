<?php
namespace Jira\Controller;
class CarController extends WebInfoController
{
    public function index()
    {
        $typeList=array('湘','陕','浙','蒙','辽','吉','黑','冀');
        $this->assign("typeList", $typeList);
        $type=I('type',$typeList[0]);
        $this->assign("type", $type);

        $search = trim(I('search'));
        $this->assign('search', $search);

        $where['vin|engine_code|offence'] = array('like', '%' . $search . '%');
//        $where['offence'] = array('notlike', '%暂时没有新的违章%');
        $where['car_number']=array('like', '%' . $type . '%');
        $data=getList('tp_car_info',$where,'ctime desc');
        $this->assign('data', $data);
        $this->assign("count", sizeof($data));

        $this->display();
    }
    //修改数值
    function car_update(){

        if(I('id')){
            $this->update('tp_car_info',$_POST);
        }else{
            $where['car_number']=I('car_number');
            if(findOne('tp_car_info',$where)){
                $this->error('车牌号已存在');
            }else{
                $map['vin']=I('vin');
                if(findOne('tp_car_info',$map)){
                    $this->error('车架号已存在');
                }else{
                    $var['engine_code']=I('engine_code');
                    if (findOne('tp_car_info',$var)){
                        $this->error('发动机号已存在');
                    }else{
                        $this->insert('tp_car_info',$_POST);
                    }
                }
            }
        }
    }
    //获取车辆信息
    function car_info(){;
        $data=find('tp_car_info',I('id'));
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

    function noOffence(){
        $_GET['offence']="{
            \"ok\":false,
            \"returncode\":100003,
            \"message\":\"暂时没有新的违章\"
        }";
        $this->update('tp_car_info',$_GET);
    }

    //单个查违章
    function offence(){
        $id=I('id');
        $arr=find('tp_car_info',$id);
//        $carNumber=base64_decode($arr['car_number']);
        $url='http://test.open.yxyongche.cn/jcfw/offence/list?carType=02&carNumber='
            .$arr['car_number'].'&vin='.$arr['vin'].'&engineCode='.$arr['engine_code'];
        dump($url);
        $res=httpGet($url,50);
        dump($res);
        $res=json_decode($res,true);

    }
    //批量查违章
    function offence_all(){
        $where['offence']=array('exp','is null');
        $data=getList('tp_car_info',$where);
        foreach ($data as $vo){

        }
    }
}