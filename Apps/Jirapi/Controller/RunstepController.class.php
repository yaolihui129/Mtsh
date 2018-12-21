<?php
namespace Jirapi\Controller;
class RunstepController extends BasicController
{
    //初始化信息
    function init()
    {
        $data = array(
            'table' => 'AO_69E499_TESTRUNSTEP',
            'where' => array('TEST_RUN_ID' => $_GET['run']),
            'map' => '',
            'order' => '',
            'field' => '',
            'page'  => ''
        );
        return $data;
    }


    public function run()
    {
        switch ($this->_method) {
            case 'get':
                $this->get();
                break;
            case 'put':
                $this->put();
                break;
            case 'post':
                $this->post();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'head':
                $this->head();
                break;
        }
    }

}