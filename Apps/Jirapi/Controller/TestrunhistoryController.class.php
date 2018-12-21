<?php
namespace Jirapi\Controller;
class TestrunhistoryController extends BasicController
{
    //初始化信息
    function init()
    {
        $data = array(
            'table' => 'AO_69E499_TESTRUNHISTORY',
            'where' => $_GET,
            'map' => '',
            'order' => '',
            'field' => '',
            'page'  => ''
        );
        return $data;
    }


}