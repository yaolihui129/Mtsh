<?php
namespace Xiuli\Controller;
use Think\Controller;
class ApiController extends Controller {

    public function ad(){

        $where=array('prodid'=>C('PRODID'),'state'=>5);
        $pic=M('tp_ad')->where($where)->order('utime desc')->field('img,url')->select();
        $pic=$this->jsonEncode(200,'ok',$pic);

        echo $pic;
    }
    //json
    public static function jsonEncode($code,$message='',$data='')
    {
        if(!is_numeric($code))
        {
            return '';
        }
    
        $result = array(
            'code' => $code,
            'message' => $message,
            'data' => $data,
        );
        header("Content-type:text/json;chaset=utf-8");
        return json_encode($result);
    }
    

}
