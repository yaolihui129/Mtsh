<?php

namespace Jirapi\Controller;
class ComponentController extends BasicController
{
    //初始化信息
    function init()
    {
        $data = array(
            'table' => 'component',
            'where' => array('PROJECT' => $_GET['project']),
            'map'   => '',
            'order' => '',
            'field' => '',
            'page'  => ''
        );
        return $data;
    }

}