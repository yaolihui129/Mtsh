<?php

namespace Jirapi\Controller;
class TeststepController extends BasicController
{
    //初始化信息
    function init()
    {
        $data = array(
            'table' => 'AO_69E499_TESTSTEP',
            'where' => array('TC_ID' => $_GET['tc']),
            'map' => '',
            'order' => '',
            'field' => '',
            'page'  => ''
        );
        return $data;
    }


}