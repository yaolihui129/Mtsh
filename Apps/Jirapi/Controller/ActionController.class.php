<?php
namespace Jirapi\Controller;
class ActionController extends BasicController
{
    //初始化信息
    function init()
    {
        $data = array(
            'table' => 'jiraaction',
            'where' => array('issueid' => $_GET['issueid']),
            'map'   => '',
            'order' => 'ID',
            'field' => '',
            'page'  => ''
        );
        return $data;
    }


}