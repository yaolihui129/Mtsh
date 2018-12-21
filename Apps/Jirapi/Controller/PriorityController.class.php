<?php
namespace Jirapi\Controller;
class PriorityController extends BasicController
{
    function init()
    {
        $data = array(
            'table' => 'priority',
            'where' => '',
            'map' => '',
            'order' => '',
            'field' => 'ID,pname',
            'page'  => ''
        );
        return $data;
    }


}