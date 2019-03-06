<?php
namespace Jira\Controller;
class WebInfoController extends BaseController
{
    public function _initialize()
    {

    }
    function _empty()
    {
        $this->display('index');
    }
    function isLogin($url='/Jira/Index')
    {
        setCache('url',$url);
        if (!getLoginUser()) {
            $this->redirect('Jira/Login/index');
        }
    }

    function iusserDiect(){
        $where['issuestatus'] = array('in', '3,10002,6');
        $where['PROJECT'] = intval($_SESSION['project']);
        $where['issuetype'] = '10102';//测试计划
        $datum = date("Y-m-d H:i:s", time() - 7*24* 3600);
        $where['UPDATED']  = array('gt', $datum);;//更新时间两周以内
        $data=postIssue($where);
        $project = array();
        foreach ($data as $k => $v) {
            $project[$k]['key'] = $v['id'];
            $project[$k]['value'] = $v['summary'] . '(' . $v['issuenum'] . ')';
        }
        return $project;
    }
    function iusserStatus($STATUSCATEGORY=4){
        $where=array('STATUSCATEGORY'=>$STATUSCATEGORY);
        $data=getList('issuestatus',$where,'SEQUENCE');
        $status = array();
        foreach ($data as $k => $v) {
            $status[$k]['key'] = $v['id'];
            $status[$k]['value'] = $v['pname'] . '(' . $v['id'] . ')';
        }
        return $status;
    }
    function statusDiect($name,$value,$STATUSCATEGORY=4){
        $data=$this->iusserStatus($STATUSCATEGORY);
        $html=select($data,$name,$value);
        return $html;
    }
    function projectDict()
    {
        $data = getList('project',array());
        $project = array();
        foreach ($data as $k => $v) {
            $project[$k]['key'] = $v['id'];
            $project[$k]['value'] = $v['pname'] . '(' . $v['pkey'] . ')';
        }
        return $project;
    }
    function projectList($where = array())
    {
        $arr = array('eolinker示例', 'eoinker示例','微信', '测试管理', 'Jira');
        $where['projectName'] = array('not in', $arr);
        $data =getList('eo_project',$where,'projectID');
        return $data;
    }
    function projectID()
    {
        $project = $this->projectList();
        $pro = '';
        foreach ($project as $p) {
            $pro[] = $p['projectid'];
        }
        return $pro;
    }
    function order()
    {
        $num = 0;
        foreach ($_POST['sn'] as $id => $sn) {
            $num += D(I('table'))->save(array("id" => $id, "sn" => $sn));
        }
        if ($num) {
            $this->success("排序成功!");
        } else {
            $this->error("排序失败...");
        }
    }
    function insert()
    {
        $m = D(I('table'));
        if (IS_GET) {
            $_GET['adder'] = getLoginUser();
            $_GET['moder'] = getLoginUser();
            $_GET['ctime'] = time();
            if (!$m->create($_GET)) {
                $this->error($m->getError());
            }
            if ($m->add($_GET)) {
                if ($_GET['url']) {
                    $this->success("成功", U($_GET['url']));
                } else {
                    $this->success("成功");
                }
            } else {
                $this->error("失败");
            }
        } else {
            $_POST['adder'] = getLoginUser();
            $_POST['moder'] = getLoginUser();
            $_POST['ctime'] = time();
            if (!$m->create()) {
                $this->error($m->getError());
            }
            if ($m->add()) {
                if ($_POST['url']) {
                    $this->success("成功", U($_POST['url']));
                } else {
                    $this->success("成功");
                }
            } else {
                $this->error("失败");
            }
        }
    }
    function update()
    {
        if (IS_GET) {
            $_GET['moder'] = getLoginUser();
            if (D(I('table'))->save($_GET)) {
                if ($_GET['url']) {
                    $this->success("成功", U($_GET['url']));
                } else {
                    $this->success("成功");
                }
            } else {
                $this->error("失败！");
            }
        } else {
            $_POST['moder'] = getLoginUser();
            if (D(I('table'))->save($_POST)) {
                if ($_POST['url']) {
                    $this->success("成功", U($_POST['url']));
                } else {
                    $this->success("成功");
                }
            } else {
                $this->error("失败！");
            }
        }
    }
    function del($msg='成功')
    {
        $_POST['id'] = I('id');
        $_POST['moder'] = getLoginUser();
        $_POST['deleted'] = 1;
        if (D(I('table'))->save($_POST)) {
            $this->success($msg);
        } else {
            $this->error("失败！");
        }
    }
    function realDel($id)
    {
        $count = D(I('table'))->delete($id);
        if ($count > 0) {
            $this->success('成功');
        } else {
            $this->error('失败');
        }
    }
    function dataUpdate($table,$savePath,$data,$img='img',$url=''){
        $_POST=$data;
        $_POST['moder']=getLoginUser();
        //处理上传图片
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize  =     7145728 ;// 设置附件上传大小
        $upload->exts     =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath =  './Upload';// 设置附件上传目录
        $upload->savePath = '/'.$savePath.'/'; // 设置附件上传目录
        $info  =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息或没有上传图片
            if (D($table)->save($_POST)){
                if($url){
                    $this->success("修改成功！",U($_POST['url']));
                }else{
                    $this->success("修改成功！");
                }
            }else{
                $this->error("失败");
            }
        }else {
            $_POST[$img]=$info[$img]['savepath'].$info[$img]['savename'];
            if (D($table)->save($_POST)){
                $image = new \Think\Image();
                $image->open('./Upload/'.$info[$img]['savepath'].$info[$img]['savename']);
                $image->thumb(600, 400)->save('./Upload/'.$info[$img]['savepath'].$info[$img]['savename']);
                if($url){
                    $this->success("修改成功！",U($_POST['url']));
                }else{
                    $this->success("修改成功！");
                }
            }else{
                $this->error("修改失败！");
            }
        }
    }


    //获取字典列表
    function getDictList($type,$lim=''){
        $table='tp_dict';
        $order='sn';
        $where=array('type'=>$type);
        if($lim){
            $where['key']=array('in',$lim);
        }
        $data=getList($table,$where,$order);
        return $data;
    }
    //获取字典值
    function getDictInfo($type,$key){
        $table='tp_dict';
        $where=array('type'=>$type,'key'=>$key);
        $data=findOne($table,$where);
        return $data;
    }
    function dictList($type,$field,$default='0',$lim=''){
        $data=$this->getDictList($type,$lim);
        $res=select($data,$field,$default);
        return $res;
    }

    //规则
    function book_rules(){
        $data='<div style="font-family: &quot;Microsoft YaHei UI&quot;; font-size: 14px; font-variant-numeric: normal; font-variant-east-asian: normal; line-height: 21px; white-space: normal; widows: 1; background-color: rgb(255, 255, 255);">
        <h2 id="id-移动设备管理规范-1.规范目的" style="margin: 30px 0px 0px; padding: 0px; color: rgb(51, 51, 51); font-size: 20px; font-weight: normal; line-height: 1.5; border-bottom-color: rgb(204, 204, 204); font-family: Arial, sans-serif; widows: 2;">
            1.规范目的</h2>
        <p style="margin: 10px 0px 0px; padding: 0px; color: rgb(51, 51, 51); font-family: Arial, sans-serif; widows: 2;">
            移动设备范围包括产品、开发、测试部门采购的各型号手机、Pad（包括但不限于苹果、安卓、win）、iTouch无线上网等设备及配件。</p>
        <p style="margin: 10px 0px 0px; padding: 0px; color: rgb(51, 51, 51); font-family: Arial, sans-serif; widows: 2;">
            该规范适用于运营、产品、开发、测试等相关使用移动设备人员。</p>
        <p style="margin: 10px 0px 0px; padding: 0px; color: rgb(51, 51, 51); font-family: Arial, sans-serif; widows: 2;">
            随着项目的深入，用于项目的移动设备越来越多，为了使采购的设备能够充分利用、统一调配、统一保管，故建立此规范。</p>
        <h2 id="id-移动设备管理规范-2.规范细则" style="margin: 30px 0px 0px; padding: 0px; color: rgb(51, 51, 51); font-size: 20px; font-weight: normal; line-height: 1.5; border-bottom-color: rgb(204, 204, 204); font-family: Arial, sans-serif; widows: 2;">
            2.规范细则</h2>

        <h4 id="id-移动设备管理规范-2.1采购申请" style="margin: 20px 0px 0px; padding: 0px; color: rgb(51, 51, 51); line-height: 1.42857; font-family: Arial, sans-serif; widows: 2;">
            2.1 采购申请</h4>
        <div>
            <ul style="list-style-position: inside;">
                <li>待补充</li>
            </ul>
        </div>
        <h4 id="id-移动设备管理规范-2.2设备保管" style="margin: 10px 0px 0px; padding: 0px; color: rgb(51, 51, 51); line-height: 1.42857; font-family: Arial, sans-serif; widows: 2;">
            2.2 设备保管</h4>
        <div>
            <ol style="list-style-position: inside;">
                <li>保险中心的测试设备统一由腰立辉进行保管</li>
                <li>车服中心的测试设备由秦振霞、赵辉进行保管</li>
                <li>特殊机型的所有者可以共享出自己的手机由所有人自己保管</li>
                <li>在<a href="http://qc.zhidaoauto.com/index.php/Jira/Books/index">图书&设备管理平台</a>进行公示（品牌、型号、系统版本、当前借用人、预约情况及借用的历史记录）</li>
            </ol>
        </div>
        <h4 id="id-移动设备管理规范-2.3设备的借用/归还" style="margin: 10px 0px 0px; padding: 0px; color: rgb(51, 51, 51); line-height: 1.42857; font-family: Arial, sans-serif; widows: 2;">
            2.3 设备的借用/归还</h4>
        <div>
            <ol style="list-style-position: inside;">
                <li>测试设备随用随还，加快流转（无效占用可耻）</li>
                <li>测试设备的借用，必须声明设备的用途，保管员区分优先级</li>
                <li>测试设备务必当天借用当天归还（特殊情况加班需要使用的，第二个工作日9:00-9:15期间必须归还至保管人处）</li>
                <li>测试设备需要联系使用的，必须在平台再次办理借用手续（前提是这台设备没有被他人预约）</li>
                <li>建立预约优先的机制，同一台设备每人只保留一条有效的预约，如果同一天内多个人预约，按预约单先建立的优先</li>
                <li>在计划有变的情况下，预约可以取消，如果有三次有预约但不借用的情况，此人的预约先用权无效，这个准则有保管员自行掌握</li>
                <li>如果有紧急情况需要使用某设备，找当前借用人私下解决，但是当天也必须归还保管人处</li>
                <li>保管员本人用于工作用途也要办理借用手续，否则有人借用或预约就必须借出不得私自扣留</li>
            </ol>
        </div>
        <h4  style="margin: 10px 0px 0px; padding: 0px; color: rgb(51, 51, 51); line-height: 1.42857; font-family: Arial, sans-serif; widows: 2;">2.3 设备的损坏/丢失</h4>
        </div>
        <div style="font-family: &quot;Microsoft YaHei UI&quot;; font-size: 14px; font-variant-numeric: normal; font-variant-east-asian: normal; line-height: 21px; white-space: normal; widows: 1; background-color: rgb(255, 255, 255);">
            <ul style="list-style-position: inside;">
                <li>
                    待补充</li>
            </ul>
        </div>
    ';
        return $data;
    }

    /**
     **发送企业微信文本消息
     */
    //预约消息
    function msgYuDing($borrower,$manager,$device,$start_time,$remark){
        $who=['ylh',$borrower,$manager];
        $who=array_unique($who);//去除重复的收信人
//                $who=[$borrow];
        $msg='【预约 - 图书设备管理】'.'
【'.getName('tp_device',$device,'brand').'-'.getName('tp_device',$device,'ts').'】('.getName('tp_device',$device,'serial').')
'.'被【'.getJiraName($borrower).'】预约了
时间：'.$start_time.',用途：'.$remark;
        $msg=$this->sendMessage($who,$msg);
//                print_r($msg);
    }
    //管理员驳回
    function msgBoHui($id){
        $arr=M('tp_device_loaning_record')->find($id);
        $borrower=$arr['borrower'];
        $manager=$arr['manager'];
        $device=$arr['device'];
        $start_time=$arr['start_time'];
        $who=['ylh',$borrower,$manager];
        $who=array_unique($who);//去除重复的收信人
//                $who=[$borrow];
        $msg='【预订驳回 -图书设备管理】'.'
【'.getName('tp_device',$device,'brand').'-'.getName('tp_device',$device,'ts').'】('.getName('tp_device',$device,'serial').')
【'.getJiraName($borrower).'】【'.$start_time.'】的预订被管理员：【'.getJiraName($manager).'】驳回了！
如有任何疑问请私下联系管理员【'.getJiraName($manager).'】';
        $this->sendMessage($who,$msg);
    }
    //取消预约消息
    function msgQXYuDing($id){
        $arr=M('tp_device_loaning_record')->find($id);
        $borrower=$arr['borrower'];
        $manager=$arr['manager'];
        $device=$arr['device'];
        $start_time=$arr['start_time'];
        $who=['ylh',$borrower,$manager];
        $who=array_unique($who);//去除重复的收信人
        $msg='【取消预约 - 图书设备管理】'.'
【'.getName('tp_device',$device,'brand').'-'.getName('tp_device',$device,'ts').'】('.getName('tp_device',$device,'serial').')
【' .$start_time.'】的预约了被预订人：【'.getJiraName($borrower).'】主动取消了';
        $this->sendMessage($who,$msg);
    }
    //借出消息
    function msgJieChu($borrower,$manager,$device,$end_time,$remark){
        $who=['ylh',$borrower,$manager];
        $who=array_unique($who);//去除重复的收信人
//                $who=[$borrow];
        $msg='【借出 -图书设备管理】'.'
【'.getName('tp_device',$device,'brand').'-'.getName('tp_device',$device,'ts').'】('.getName('tp_device',$device,'serial').')
已经被【'.getJiraName($borrower).'】借出，用于：'.$remark.'，
暂定：【'.$end_time.'】之前归还。
备注：'.getName('tp_device',$device,'remark');
        $this->sendMessage($who,$msg);
//
    }
    //申请延期成功
    function msgYanQi($borrower,$manager,$device,$end_time){
        $who=['ylh',$borrower,$manager];
        $who=array_unique($who);//去除重复的收信人
//                $who=[$borrow];
        $msg='【申请延期 - 图书设备管理】'.'
【'.getName('tp_device',$device,'brand').'-'.getName('tp_device',$device,'ts').'】('.getName('tp_device',$device,'serial').')
' .'被借用人：【'.getJiraName($borrower).'】申请延期到：【'.$end_time.'】归还!
这是最后的归还时间，不得再次延期！';
        $this->sendMessage($who,$msg);
//
    }
    //归还消息
    function msgGuiHuan($borrower,$manager,$device){
        $who=['ylh',$borrower,$manager];
        $who=array_unique($who);//去除重复的收信人
//                $who=[$borrow];
        $msg='【归还 -图书设备管理】'.'
【'.getName('tp_device',$device,'brand').'-'.getName('tp_device',$device,'ts').'】('.getName('tp_device',$device,'serial').')
'.'被借用人：【'.getJiraName($borrower).'】归还';
        $this->sendMessage($who,$msg);

    }
    //测试任务指派
    function msgZhiPai($who,$arr,$draw){
        $who=array_unique($who);//去除重复的收信人
        $msg='【测试任务安排 - QC平台】
《【'.$arr['pkey'].'】'.$arr['pname'].'》
已安排给【'.getJiraName($draw).'】 负责测试';
        $msg=$this->sendMessage($who,$msg);
        return $msg;
    }
    //测试任务换人
    function msgHuanRen($who,$pending,$user){
        $who=array_unique($who);//去除重复的收信人
        $msg='【测试任务安排 - QC平台】
《【'.$pending['pkey'].'】'.$pending['pname'].'》任务的测试负责人由【'.getJiraName($pending['draw']).'】更换为【'.getJiraName($user).'】 ';
        $msg=$this->sendMessage($who,$msg);
        return $msg;
    }

    //获取任务状态并更新同步
    function synchIssueStatus($id){
        $data=getIssue($id);
        if($data){
            $var['id']=$id;
            $var['issuestatus']=$data['issuestatus'];
            update('tp_project_pending',$var);
        }
    }
    function synchJiraIssue($issue){
        $table = 'tp_jira_issue';
        $id=array();
        $pkey=getCache('pkey');
        foreach ($issue as $iss){
            $data = find($table,$iss['id']);
            $iss['pkey']=$pkey.'-'.$iss['issuenum'];
            if($data){
                $id[]=update($table,$iss);
            }else{
                $id[]=insert($table,$iss);
            }
        }
        return $id;
    }
    function delJiraIssueClosedBug(){
        $table = 'tp_jira_issue';
        $where=array('issuetype'=>'10008','issuestatus'=>'6','status'=>'1');
        $data=getList($table,$where);
        if($data){
            foreach ($data as $vo){
                realdel($table,$vo['id']);
            }
        }
    }



}