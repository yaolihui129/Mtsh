<?php
namespace Jirapi\Controller;
class TestsuitememberController extends BasicController
{
    //初始化信息
    function init()
    {
        $data = array(
            'table' => 'AO_69E499_TESTSUITEMEMBER',
            'where' => array('SEQ_BALANCER' => $_GET['seq']),
            'map'   => '',
            'order' => 'SEQUENCE',
            'field' => '',
            'page'  => ''
        );
        return $data;
    }

}