<?php
namespace Xinda\Controller;
class ChoujController extends BaseController {
    public function index(){
        getWebInfo(C('PRODUCT'));//获取网页信息

        $this->display();
    }
    
    public function baox(){
        getWebInfo(C('PRODUCT'));//获取网页信息

        $this->display();
    }
    
    public function choujnum(){
        getWebInfo(C('PRODUCT'));//获取网页信息

        $this->display();
    }
    
    public function lhjnum(){
        getWebInfo(C('PRODUCT'));//获取网页信息

        $this->display();
    }
    
    public function ggk(){
        getWebInfo(C('PRODUCT'));//获取网页信息

        $this->display();
    }
    
}