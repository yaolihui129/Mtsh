<?php
namespace Jirapi\Controller;
use Think\Controller\RestController;
class BasicController extends RestController
{

    public function index()
    {
        switch ($this->_method) {
            case 'get': // get请求处理代码
                $this->getMethod();
                break;
            case 'put': // put请求处理代码
                $this->put();
                break;
            case 'post': // post请求处理代码
                $this->post();
                break;
            case 'delete': // delete请求处理代码
                $this->delete();
                break;
            case 'head': // head请求处理代码
                $this->head();
                break;
        }
    }

    function getMethod()
    {
        $var = $this->init();
        if ($_GET['id']) {
            $data = find($var['table'], $_GET['id'], $var['field']);
        } elseif ($_GET['method'] == 'find') {
            $data = findOne($var['table'], $var['where'], $var['order'], $var['field']);
        } elseif ($_GET['method'] == 'count') {
            $data = countId($var['table'], $var['where']);
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
        $res = resFormat($data);
        $this->ajaxReturn($res);
    }

    function put()
    {
//        $var = $this->init();
        $data = array();
        $code = 404;
        $msg = '暂不提供该功能';
        $res = resFormat($data,$code,$msg);
        $this->ajaxReturn($res);
    }

    function post()
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
        $res = resFormat(getList($var['table'],$where,$var['order'],$var['field'],$var['page'],$var['size']));
        $this->ajaxReturn($res);

    }

    function delete()
    {
        $var = $this->init();
        $data = array();
        $code = 404;
        $msg = '暂不提供该功能';
        $res = resFormat($data,$code,$msg);
        $this->ajaxReturn($res);
    }

    function head()
    {
        $var = $this->init();
        $data = array();
        $code = 404;
        $msg = '暂不提供该功能';
        $res = resFormat($data,$code,$msg);
        $this->ajaxReturn($res);
    }





}