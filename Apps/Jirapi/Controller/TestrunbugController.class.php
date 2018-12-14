<?php

namespace Jirapi\Controller;
class TestrunbugController extends BasicController
{
    //初始化信息
    function init()
    {
        $data = array(
            'table' => 'AO_69E499_TESTRUNBUG',
            'map'   => '',
            'order' => '',
            'field' => '',
            'page'  => ''
        );
        if ($_GET['run_id']) {
            if ($_GET['step_id']) {
                $data['where'] = array('TEST_RUN_ID' => $_GET['run_id'], 'TEST_RUN_STEP_ID' => $_GET['step_id']);
            } else {
                $data['where'] = array('TEST_RUN_ID' => $_GET['run_id']);
            }
        } else {
            if ($_GET['step_id']) {
                $data['where'] = array('TEST_RUN_STEP_ID' => $_GET['step_id']);
            }
        }
//        dump($data);
        return $data;
    }

}