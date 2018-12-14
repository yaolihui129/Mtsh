<?php
namespace Jirapi\Controller;
class IndexController extends BasicController
{
    function init()
    {
        $data = array(
            'table' => 'component',
            'where' => '',
            'map'   => '',
            'order' => '',
            'field' => '',
            'page'  => ''
        );
        return $data;
    }

    function get_list()
    {
        $data = array('A', $_GET);
        $this->response($data, 'json');
    }

    function put()
    {
        $data = array('A', $_GET);
        $this->response($data, 'json');
    }

    function post_fun()
    {
        $data = array_merge($_POST, $_GET);
        $this->ajaxReturn($data);
    }


}