<?php

namespace Jirapi\Controller;
class IssuestatusController extends BasicController
{
    function init()
    {
        $data = array(
            'table' => 'issuestatus',
            'where' => '',
            'map' => '',
            'order' => '',
            'field' => 'ID,pname',
            'page'  => ''
        );
        return $data;
    }


}