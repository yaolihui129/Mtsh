<?php
namespace Jira\Controller;
class MapiController extends WebInfoController
{
    function home(){
        $table='tp_project_pending';
        $map=array();
        $map['status'] = array('in' , '0,1,2');
        $order='status desc,ctime desc';
        $data = getList($table,$map,$order);
//        $data['agent']=$_SERVER['HTTP_USER_AGENT'];
        $res = resFormat($data);
        $this->ajaxReturn($res);
    }

    function task(){
        $table='tp_project_pending';
        $map=array();
        $map['status'] = array('in' , '0,1,2');
        $order='status desc,ctime desc';
        $data = getList($table,$map,$order);
//        $data['agent']=$_SERVER['HTTP_USER_AGENT'];
        $res = resFormat($data);
        $this->ajaxReturn($res,'jsonp');
    }

    function plan(){
        $data =array();
//        $data['agent']=$_SERVER['HTTP_USER_AGENT'];
        $res = resFormat($data);
        $this->ajaxReturn($res);
    }

    function bug(){
        $data =array();
//        $data['agent']=$_SERVER['HTTP_USER_AGENT'];
        $res = resFormat($data);
        $this->ajaxReturn($res);
    }

    function testCase(){
        $data =array();
//        $data['agent']=$_SERVER['HTTP_USER_AGENT'];
        $res = resFormat($data);
        $this->ajaxReturn($res);
    }

    function getInfo(){
        $table='tp_project_pending';
        $id=I('id');
        $data=find($table,$id);
//        $data['agent']=$_SERVER['HTTP_USER_AGENT'];
        $res = resFormat($data);
        $this->ajaxReturn($res);
    }

}