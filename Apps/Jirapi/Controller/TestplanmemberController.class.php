<?php
namespace Jirapi\Controller;
/*
 * 测试计划关联的测试用例
 */
class TestplanmemberController extends BasicController
{
    //初始化信息
    function init()
    {
        $data = array(
            'table' => 'AO_69E499_TESTPLANMEMBER',
            'where' => array('TP_ID' => $_GET['tp']),
            'map' => '',
            'order' => '',
            'field' => '',
            'page'  => ''
        );
//        print_r($data);
        return $data;
    }

    public function plan()
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