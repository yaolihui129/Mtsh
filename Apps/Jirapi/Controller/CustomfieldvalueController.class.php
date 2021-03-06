<?php
namespace Jirapi\Controller;
class CustomfieldvalueController extends BasicController
{
    function init()
    {
        $data = array(
            'table' => 'customfieldvalue',
            'where' => '',
            'map' => '',
            'order' => '',
            'field' => 'ISSUE,CUSTOMFIELD,STRINGVALUE,DATEVALUE',
            'page'  => ''
        );
        return $data;
    }

    function issue(){
        switch ($this->_method) {
            case 'get': // get请求处理代码
                $this->issue_get();
                break;
            case 'put': // put请求处理代码
                $this->put();
                break;
            case 'post': // post请求处理代码
                $this->post();
                break;
        }
    }

    function issue_get(){
        $var = $this->init();
        $map = array('ISSUE' => I('issue'), 'CUSTOMFIELD' => I('customfield'));
        $data = findOne($var['table'],$map,$var['order'],$var['field']);
        if($data['stringvalue']){
            echo $data['stringvalue'];
        }else{
            echo substr($data['datevalue'],0,10);
        }
    }


    function put()
    {
        $var = $this->init();
        $where = getJsonToArray();
        if($where['ID']){
            $data = array($where,'更新');
        }else{
            $map = array('ISSUE' => $where['ISSUE'], 'CUSTOMFIELD' => $where['CUSTOMFIELD']);
            $id = findOne($var['table'],$map,$var['order'],$var['field']);;
            if($id){
                $data = array($where,'更新');
            }else{
                $data = array($where,'写入');
            }
        }
        $this->response($data, 'json');
    }

}