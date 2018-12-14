<?php

namespace Jirapi\Controller;
class RunstepController extends BasicController
{
    //初始化信息
    function init()
    {
        $data = array(
            'table' => 'AO_69E499_TESTRUNSTEP',
            'where' => array('TEST_RUN_ID' => $_GET['run']),
            'map' => '',
            'order' => '',
            'field' => '',
            'page'  => ''
        );
        return $data;
    }


    public function run()
    {
        switch ($this->_method) {
            case 'get': // get请求处理代码
                $this->get();
                break;
            case 'put': // put请求处理代码
                $this->put();
                break;
            case 'post': // post请求处理代码
                $this->post();
                break;
            case 'delete': // delete请求处理代码
                $this->delete();
                break;
            case 'head': // head请求处理代码
                $this->head();
                break;
        }
    }

}