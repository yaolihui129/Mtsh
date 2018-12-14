<?php

namespace Linker\Controller;

class ApiController extends BasicController
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


}