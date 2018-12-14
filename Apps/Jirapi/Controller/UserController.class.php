<?php
namespace Jirapi\Controller;
class UserController extends BasicController
{
    function init()
    {
        $data = array(
            'table' => 'AO_2D3BEA_USER_INDEX',
            'where' => array('USER_KEY' => $_GET['USER_KEY']),
            'map'   => '',
            'order' => '',
            'field' => '',
            'page'  => ''
        );
        return $data;
    }


}