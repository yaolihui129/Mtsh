<?php

namespace Jirapi\Controller;
class VersionController extends BasicController
{
    //初始化信息
    function init()
    {
        $data = array(
            'table' => 'projectversion',
            'where' => array('PROJECT' => $_GET['project']),
            'map'   => '',
            'order' => '',
            'field' => '',
            'page'  => ''
        );
        return $data;
    }

}