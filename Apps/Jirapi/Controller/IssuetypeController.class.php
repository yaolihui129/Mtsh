<?php

namespace Jirapi\Controller;
class IssuetypeController extends BasicController
{
    function init()
    {
        $data = array(
            'table' => 'issuetype',
            'where' => '',
            'map' => '',
            'order' => '',
            'field' => 'ID,pname',
            'page'  => ''
        );
        return $data;
    }


}