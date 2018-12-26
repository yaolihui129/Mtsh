<?php
namespace Xinda\Controller;
class ActivityController extends BaseController {
    function init(){
        $info=array(
            'table'=>'tp_activity',
            'name'=>'Activity',
        );
        return $info;
    }
    public function index(){
        $info=$this->init();
        getWebInfo(C('PRODUCT'));//获取网页信息

        $this->display();
    }
    
    public function activityList(){ 
        $info=$this->init();
        $map['type']=$info['name'];
        getList($info['table'], $map,C('maxPageNum'),I('p'));
        $this->display();      
    }
    
    
    

}