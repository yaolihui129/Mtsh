<?php
namespace Jirapi\Controller;
use Think\Controller\RestController;
class BasicController extends RestController
{

    function index()
    {
        switch ($this->_method) {
            case 'get':
                $this->getMethod();
                break;
            case 'put':
                $this->putMethod();
                break;
            case 'post':
                $this->postMethod();
                break;
            case 'delete':
                $this->delMethod();
                break;
            case 'head':
                $this->headMethod();
                break;
        }
    }

    function getMethod()
    {
        $var = $this->init();
        if ($_GET['id']) {
            $data = $this->find($var['table'], $_GET['id'], $var['field']);
        } elseif ($_GET['method'] == 'find') {
            $data=findOne($var['table'],$var['where'],$var['order'],$var['field']);
        } elseif ($_GET['method'] == 'count') {
            $data =countId($var['table'], $var['where']);
        } else {
            if($_GET['order']){
                $var['order']= $_GET['order'];
            }
            if($_GET['page']){
                $var['page']=$_GET['page'];
                $var['size']=$_GET['size'];
            }
            $data = getList($var['table'], $var['where'], $var['order'], $var['field'],$var['page'],$var['size']);
        }
        $res=resFormat($data);
        $this->ajaxReturn($res);
    }

    function putMethod()
    {
        $data = array();
        $res=resFormat($data,'-1','暂不提供该功能');
        $this->ajaxReturn($res);
    }

    function postMethod()
    {
        $var = $this->init();
        $where = getJsonToArray();
        if($where['order']){
            $var['order']=$where['order'];
        }
        if($where['page']){
            $var['page']=$where['page'];
            $var['size']=$where['size'];
        }
        $data = getList($var['table'], $var['where'], $var['order'], $var['field'],$var['page'],$var['size']);
        $res=resFormat($data);
        $this->ajaxReturn($res);
    }

    function delMethod()
    {
        $data = array();
        $res=resFormat($data,'-1','暂不提供该功能');
        $this->ajaxReturn($res);
    }

    function headMethod()
    {
        $data = array();
        $res=resFormat($data,'-1','暂不提供该功能');
        $this->ajaxReturn($res);
    }

}