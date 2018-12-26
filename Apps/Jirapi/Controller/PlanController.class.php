<?php
namespace Jirapi\Controller;
class PlanController extends BasicController
{
    function init()
    {
        $data = array(
            't_cycle' => 'AO_69E499_TESTCYCLE',
            't_run' => 'AO_69E499_TESTRUN',
            't_bug' => 'AO_69E499_TESTRUNBUG',
            't_issue' => 'jiraissue',
            'where' => array(),
            'map'   => array(),
            'order' => '',
            'field' => '',
            'page'  => ''
        );
        return $data;
    }

    function bug(){
        switch ($this->_method) {
            case 'get': // get请求处理代码
                $this->bug_get();
                break;
            case 'post': // post请求处理代码
                $this->bug_post();
                break;
        }
    }

    function bug_get(){
        $info=$this->init();
        $table=$info['t_issue'];
        $data = array();
        $map  =$info['map'];
        $tp=I('tp');
        $bugId=$this->getBugId($tp);
        if($bugId){
            $map['ID']=array('in',$bugId);
            $data=getList($table,$map);
        }
        $res = resFormat($data);
        $this->ajaxReturn($res);

    }

    function bug_post(){
        $info=$this->init();
        $table=$info['t_issue'];
        $data = array();
        $map=getJsonToArray();
        $bugId=$this->getBugId($map['tp']);
        if($bugId){
            $map['ID']=array('in',$bugId);
            $data=getList($table,$map);
        }
        $res = resFormat($data);
        $this->ajaxReturn($res);
    }


    function getBugId($tp){
        $info=$this->init();
        $table=$info['t_cycle'];
        $where=array('TP_ID'=>$tp);
        $cyc=getList($table,$where);
        if ($cyc){
            $cycId=array();
            foreach ($cyc as $c){
                $cycId[]=$c['id'];
            }
            $map=array();
            $map['TEST_CYCLE_ID']=array('in',$cycId);
            $table=$info['t_run'];
            $run = getList($table,$map);
            if($run){
                $runId=array();
                foreach ($run as $r){
                    $runId[]=$r['id'];
                }
                $where=array();
                $where['TEST_RUN_ID']=array('in',$runId);
                $table=$info['t_bug'];
                $bug=getList($table,$where);
                if($bug){
                    $bugId=array();
                    foreach ($bug as $b){
                        $bugId[]=$b['bug_id'];
                    }
                    return $bugId;
                }else{
                    return '';
                }
            }else{
                return '';
            }
        }else{
            return '';
        }
    }
}