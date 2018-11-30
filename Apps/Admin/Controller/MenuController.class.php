<?php
namespace Admin\Controller;
class MenuController extends BaseController
{
    //初始化数据
    function init(){
        $data = array(
            'table_dict' => 'menu',
            'name'       => 'Menu',
            'where'      =>array(),
            'order'      =>'id'
        );
        return $data;
    }
    //首页
    public function index()
    {
        
        $this->display();
    }


}