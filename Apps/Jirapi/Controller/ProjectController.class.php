<?php
namespace Jirapi\Controller;
class ProjectController extends BasicController
{
    function init()
    {
        $data = array(
            'table' => 'project',
            'where' => array('pkey' => $_GET['pkey']),
            'map' => '',
            'order' => '',
            'field' => 'ID,pname,pkey',
            'page'  => ''
        );
        return $data;
    }


}