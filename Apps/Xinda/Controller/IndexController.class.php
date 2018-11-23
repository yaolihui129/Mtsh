<?php
namespace Xinda\Controller;
class IndexController extends BaseController {
    public function index(){
        $this->assign('JC',C('PRODUCT'));
        getWebInfo(C('PRODUCT'));//获取网页信息

        
        $where=array('prodid'=>C('PRODID'));
        $pic=M('tp_ad')->where($where)->order('utime desc')->select();
        $this->assign('pic',$pic); 

        $where=array('prodid'=>C('PRODID'),'istj'=>1,'state'=>5);
        $data=M('tp_product')->where($where)->order('utime desc')->field("id,name,money,smoney,num,img,utime")->select();
        $this->assign('data',$data);
        
        $this->display();
    }               
}