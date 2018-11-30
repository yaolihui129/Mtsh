<?php
namespace Business\Controller;
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


  

}