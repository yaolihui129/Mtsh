<?php

namespace Jirapi\Controller;
class TestsuiteController extends BasicController
{
    //初始化信息
    function init()
    {
        $data = array(
            'table' => 'AO_69E499_TESTSUITE',
            'where' => array('PROJECT_ID' => $_GET['project']),
            'map' => '',
            'order' => '',
            'field' => '',
            'page'  => ''
        );
        return $data;
    }

}