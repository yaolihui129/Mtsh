<?php
namespace Xinda\Controller;
class ApiController extends BaseController {
    public function test(){
               
        echo '您购买的是：'.I('name').'团购价格为：'.I('jiage');
    }
    public function ad(){
        $b=I('key');
        if(C('apiKey')==$b){
            $where=array('prodid'=>C('PRODID'),'state'=>5);
            $pic=M('tp_ad')->where($where)->order('utime desc')->field('img,url')->select();
            $pic=$this->jsonEncode(200,'ok',$pic);          
        }else {
            $pic=$this->jsonEncode(405,'shib',$b);
        }
        echo $pic;
    }
    
    public function activity(){
        $where=array('prodid'=>C('PRODID'),'state'=>5);
        $data=M('tp_activity')->where($where)->order('utime desc')->select();
        $arr=self::xmlEncode(200,"ok",$data);              
        echo $arr;
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
