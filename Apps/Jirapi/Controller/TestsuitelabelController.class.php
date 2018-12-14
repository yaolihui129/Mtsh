<?php

namespace Jirapi\Controller;
class TestsuitelabelController extends BasicController
{
    //初始化信息
    function init()
    {
        $data = array(
            'table' => 'AO_69E499_TESTSUITELABEL',
            'where' => array('TEST_SUITE_ID' => $_GET['suite']),
            'map'   => '',
            'order' => '',
            'field' => '',
            'page'  => ''
        );
        return $data;
    }


}