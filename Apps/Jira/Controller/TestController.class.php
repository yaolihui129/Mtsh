<?php
namespace Jira\Controller;
class TestController extends WebInfoController
{
    public function index()
    {
        $str = '18801043607';
        $key = 'yaolihui';
        $miwen=lock_url($str,$key);
        dump($miwen);
        $mingwen = unlock_url($miwen,$key); //解密
        dump($mingwen);
        $host = 'http://10.2.100.16:10230';
        $token = 'b6da77eec22f08b7e0f199ccc619ebf0';
        $plain='18801043607';
        $url=$host.'/api/v1/encrypt?token='.$token.'&plain='.$plain;
        $data=httpGet($url);
        $data=json_decode($data,true);
        $data=$data['result'][$plain];
        dump($data);

        $this->display();
    }
    //分割
    public function fenge(){
        $source = "CX-34";
        $hello = explode('-', $source);
        dump($hello);
    }


    public function test(){
//        $data=getCustomFieldValue(11570,10202);
//        dump($data);
        cookie('type_plan',I('type','doing'),array('prefix'=>C(PRODUCT).'_'));
        $tpye=cookie(C(PRODUCT).'_type_plan');
        if ($tpye == 'online') {
            $where['issuestatus'] = array('in', '3,10002,10011,6');
            $where['order']='UPDATED desc';
            $where['page']=I('page',1);
            $where['size']=30;
        }elseif ($tpye == 'done'){
            $where['issuestatus'] = array('in','10004,10107');
        } else {
            $where['issuestatus'] = array('not in', '3,10002,10011,10004,10107,6');
            $where['order']='issuestatus desc,ASSIGNEE';
        }

        if (I('project')) {
            cookie('project',I('project'),array('prefix'=>C(PRODUCT).'_'));
            $pro = M('project')->find(I('project'));
            cookie('pkey',$pro['pkey'],array('prefix'=>C(PRODUCT).'_'));
            cookie('pname',$pro['pname'],array('prefix'=>C(PRODUCT).'_'));
        }
        $project=cookie(C(PRODUCT).'_project');
        if($project ==10006){
            cookie('testGroup',I('testGroup','one'),array('prefix'=>C(PRODUCT).'_'));
            $testGroup=cookie(C(PRODUCT).'_testGroup');
            $this->assign('testGroup', $testGroup);

            if($testGroup=='one'){
                $where['CREATOR'] =array('in',C(QA_TESTER));
            }else{
                $where['CREATOR'] =array('in','qinzhenxia,congtianyue,zhangaonan');
            }

        }
        $where['PROJECT'] = intval($project);
        $where['issuetype'] = '10102';
        $search = trim(I('search'));
        $this->assign('search', $search);

        $where['SUMMARY|issuenum'] = array('like', '%' . $search . '%');

        $url = C(JIRAPI) . "/Jirapi/issue";
        $data = httpJsonPost($url, json_encode($where));
//        dump($data);
        $this->synch_Jira($data);

        $this->display();
    }


    public function bootstrap4(){
        $this->display();
    }
}