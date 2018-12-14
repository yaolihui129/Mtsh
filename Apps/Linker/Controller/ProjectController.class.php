<?php

namespace Linker\Controller;

class ProjectController extends BasicController
{
    public function index()
    {
        $search = I('search');
        $this->assign('search', $search);
        $where['projectName|projectVersion'] = array('like', '%' . $search . '%');
        $data = $this->projectList($where);
        $this->assign('data', $data);

        $projectType = $this->select(C(PROJECTTYPE), 'projectType', 0);
        $this->assign("projectType", $projectType);

        $this->display();
    }

    public function inside()
    {
        $arr = M('project')->find(I('id'));

        $this->assign('arr', $arr);
//        dump($arr);
        $this->display();
    }

    function set()
    {
        $data = M('project')->find(I('id'));
        $projectType = $this->select(C(PROJECTTYPE), projectType, $data['projecttype']);
        $arr = array(
            'code' => 0,
            'data' => $data,
            'projectType' => $projectType
        );
        $this->ajaxReturn($arr);
    }


}