<?php

namespace Api\Controller;
class IndexController extends BasicController
{
    public function index()
    {
        //练习封装JSon
        $people = array('name'=>"Bill", 'age'=>"Steve", 'hight'=>"Mark");
        $this->json_public($people,'1221312312');
    }

    public function test(){
        $appkey='test';
        $time=time();
        $arr=array(
            'c'=>3,
            'b'=>2,
            'a'=>1
        );
        $sign=get_sign($arr,$time,$appkey,'sha1');
        $arr['sign']=$sign;
        $arr['_timestamp']=date('Y-m-d H:i:s',$time);
        $r=verify_sign($arr,$appkey,'sha1');
        dump($r);
        $this->json_public($sign,'1221312312');
    }

    public function get_sign(){
        $json=file_get_contents('php://input');
        $array=json_decode($json,true);
        $appkey=$array['appkey'];
        $time=$array['time'];
        $data=$array['data'];
//        echo get_sign($array,$time,$appkey);
        $sign=get_sign($data,$time,$appkey);
        $this->json_public($data,$array['requestId'],$sign,$appkey);
    }

    public function ins(){
        //记录接口执行结果
        $table='tp_api_test_result';
        $_POST['adder']=$_POST['tester'].'-JMeter';
        $_POST['moder']=$_POST['tester'].'-JMeter';
        $this->insert($_POST,$table);
    }
}