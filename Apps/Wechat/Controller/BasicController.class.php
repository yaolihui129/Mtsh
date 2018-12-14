<?php
namespace Wechat\Controller;
use Think\Controller\RestController;
class BasicController extends RestController
{

    public function index()
    {
        switch ($this->_method) {
            case 'get': // get请求处理代码
                $this->get();
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

    function get()
    {
        $var = $this->init();
        if ($_GET['id']) {
            $data = $this->find($var['table'], $_GET['id'], $var['field']);
        } elseif ($_GET['method'] == 'find') {
            $data = $this->find_one($var['table'], $var['where'], $var['order'], $var['field']);
        } elseif ($_GET['method'] == 'count') {
            $data = $this->count($var['table'], $var['where']);
        } else {
            if($_GET['order']){
                $var['order']= $_GET['order'];
            }
            if($_GET['page']){
                $var['page']=$_GET['page'];
                $var['size']=$_GET['size'];
            }
            $data = $this->query($var['table'], $var['where'], $var['order'], $var['field'],$var['page'],$var['size']);
        }
        $this->ajaxReturn($data);
    }

    function put()
    {
        $var = $this->init();
        $data = array('暂不提供该功能');
        $this->response($data, 'json');
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
        $data = $this->query($var['table'],$where,$var['order'],$var['field'],$var['page'],$var['size']);
        $this->ajaxReturn($data);
    }

    function delete()
    {
        $var = $this->init();
        $data = array('暂不提供该功能');
        $this->ajaxReturn($data);
    }

    function head()
    {
        $var = $this->init();
        $data = array('暂不提供该功能');
        $this->ajaxReturn($data);
    }

    //查询数据
    function query($table, $where, $order = 'id', $field = '',$page='',$size='')
    {
        $where['deleted'] = '0';
        if($page){
            $data = M($table)->where($where)->order($order)->field($field)->page($page,$size)->select();
        }else{
            $data = M($table)->where($where)->order($order)->field($field)->select();
        }

        if (!$data) {
            $data = '';
        }
        return $data;
    }

    //根据条件查询最符合的一条数据
    function find_one($table, $where, $order = 'id desc', $field = '')
    {
        $where['deleted'] = '0';
        $data = M($table)->where($where)->order($order)->field($field)->find();
        if (!$data) {
            $data = '查不到数据';
        }
        return $data;
    }
    //查找数据
    function find($table, $id, $field = '')
    {
        $data = M($table)->field($field)->find($id);
        if (!$data) {
            $data = '查不到数据';
        }
        return $data;
    }

    //根据条件统计数量
    function count($table, $where)
    {
        $where['deleted'] = '0';
        $count = M($table)->where($where)->count();
        if (!$count) {
            $count = '查不到数据';
        }
        return $count;
    }
    //插入数据
    function insert($var, $table)
    {
        $m = D($table);
        if (!$m->create($var)) {
            return $m->getError();
        } else {
            $id = $m->add($var);
            if ($id) {
                return $id;
            } else {
                return 0;
            }
        }
    }
    //更新数据
    function update($var, $table)
    {
        if (D($table)->save($var)) {
            return 1;
        } else {
            return 0;
        }
    }

}