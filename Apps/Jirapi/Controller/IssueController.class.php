<?php
namespace Jirapi\Controller;
class IssueController extends BasicController
{
    //初始化信息
    function init()
    {
        $data = array(
            'table' => 'jiraissue',
            'where' => $_GET,
            'map'   => '',
            'order' => I('order'),
            'field' => 'id,issuenum,PROJECT,REPORTER,ASSIGNEE,SUMMARY,PRIORITY,issuetype,issuestatus,RESOLUTIONDATE,DESCRIPTION,CREATED,UPDATED',
            'page'  => ''
        );
        return $data;
    }

    function put()
    {
        $info= $this->init();
        $table=$info['table'];
        $data = getJsonToArray();
        if ($data['ID']) {
            //重新封装数组
            $var['ID'] = $data['ID'];
            if ($data['issuestatus']) {
                $var['issuestatus'] = $data['issuestatus'];
            }
            if ($data['SUMMARY']) {
                $var['SUMMARY'] = $data['SUMMARY'];
            }
            if ($data['DESCRIPTION']) {
                $var['DESCRIPTION'] = $data['DESCRIPTION'];
            }
            if ($data['RESOLUTION']) {
                $var['RESOLUTION'] = $data['RESOLUTION'];
            }
            if ($data['RESOLUTIONDATE']) {
                $var['RESOLUTIONDATE'] = $data['RESOLUTIONDATE'];
            }
            if ($data['ASSIGNEE']) {
                $var['ASSIGNEE'] = $data['ASSIGNEE'];
            }
            if ($data['PRIORITY']) {
                $var['PRIORITY'] = $data['PRIORITY'];
            }
            $var['UPDATED'] = date('Y-m-d H:i:s', time());
            //更新数据
            $data = update($table,$data);
        } else {
            $var = getList($table, "", "ID desc");
            //查询按issuenum排序,获取最大的issuenum
            $where = array('PROJECT' => $data['PROJECT']);
            $order = 'issuenum desc';
            $arr = getList($table, $where, $order);
            //封装插入的数组
            $data['ID'] = intval($var[0]['id']) + 1;
            $data['issuestatus'] = 1;
            $data['issuenum'] = $arr[0]['issuenum'] + 1;
            $data['PRIORITY'] = 2;
            $data['VOTES'] = 0;
            $data['WATCHES'] = 1;
            $data['CREATOR'] = $data['REPORTER'];
            $data['CREATED'] = date('Y-m-d H:i:s', time());
            $data['UPDATED'] = $data['CREATED'];
            $data['WORKFLOW_ID'] = intval($var[0]['workflow_id']) + 1;
            //写库操作
            $data = insert($table,$data);
        }
        $this->ajaxReturn($data);
    }

    public function issuenum()
    {
        switch ($this->_method) {
            case 'get': // get请求处理代码
                $this->issuenum_get_list();
                break;
            case 'put': // put请求处理代码
                $this->put();
                break;
            case 'post': // post请求处理代码
                $this->post();
                break;
        }
    }

    function issuenum_get_list()
    {
        $var = $this->init();
        $table=$var['table'];
        $data = array();
        if ($_GET) {
            $arr = explode('-', $_GET['issuenum']);
            $where = array('pkey' => $arr[0]);
            $data = findOne('project', $where);
            $where = array('issuenum' => $arr[1], 'PROJECT' => $data['id']);
            $data = findOne($table, $where);
        }
        $res = resFormat($data);
        $this->ajaxReturn($res);
    }

}