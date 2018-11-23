<?php
namespace Demo\Controller;
class IndexController extends BaseController
{
    public function index()
    {

        $signPackage=$this->getSignPackage();
        $this->assign("signPackage", $signPackage);


        $this->display();
    }


    public function test(){
        $arr=lock_url('18801043607','Demo','1');
        print_r($arr);
        $data=unlock_url($arr,'Demo');
        dump($_COOKIE);
    }

  

}