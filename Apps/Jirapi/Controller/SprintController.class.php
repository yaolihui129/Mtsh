<?php

namespace Jirapi\Controller;
class SprintController extends BasicController
{
    //初始化信息
    function init()
    {
        $data = array(
            'table' => 'AO_60DB71_SPRINT',
            'where' => array('RAPID_VIEW_ID' => $_GET['RAPID_VIEW_ID']),
            'map'   => '',
            'order' => '',
            'field' => '',
            'page'  => ''
        );
        return $data;
    }


}