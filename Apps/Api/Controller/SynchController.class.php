<?php
/*
 * Api：数据同步接口
 *
 */
namespace Api\Controller;
class SynchController extends BasicController
{
    function getInfo()
    {
        $data = array(
            'table' => array(
                'user'
            )
        );
        return $data;
    }

    function index()
    {

    }



}