<?php
namespace Jira\Controller;
class ReportController extends WebInfoController
{
    public function index()
    {
        $_SESSION['url'] = '/' . C('PRODUCT') . '/Report/index';
        $planid=I('planid');
        if(!$_SESSION['report_planid']){
            $_SESSION['report_planid']=$planid;
        }
        $this->isLogin();
        if(!$planid){
            $planid=$_SESSION['report_planid'];
        }
        $this->assign('planid', $planid);
        $where=array('planid'=>$planid,'type'=>'1');
        $data = findOne('tp_report',$where);
        $this->assign('data', $data);


        $report_url=C('JIRAPI').'/Jira/Testcycle/index/tp/'.$planid;
        $this->assign('report_url', $report_url);
        if(!$_SESSION['testPlan']){
            $_SESSION['testPlan'] = getIssue($planid);
        }
        if($_SESSION['p2_num']){
            if($_SESSION['p3_num']){
                $bug_list='P2级Bug:'.$_SESSION['p2_num'].'个，P3级Bug'.$_SESSION['p3_num'].'个';
            }else{
                $bug_list='P2级Bug:'.$_SESSION['p2_num'].'个';
            }
        }else{
            if($_SESSION['p3_num']){
                $bug_list='P3级Bug:'.$_SESSION['p3_num'].'个';
            }else{
                $bug_list='无遗留BUG';
            }
        }
        $this->assign('bug_list', $bug_list);
        $quality='有效bug :'.$_SESSION['bug_num'].'个，Bug修复率 :'.$_SESSION['xiufl_num'].'%；P0级Bug :'.$_SESSION['p0_num'].'个，P1级Bug :'.$_SESSION['p1_num'].'个。';
        $this->assign('quality', $quality);
        $func='<br>';
        if($_SESSION['testfunc']){
            foreach ($_SESSION['testfunc'] as $k=>$f){
                $func.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.($k+1).')'.$f['summary'].';<br>';
            }
        }else{
            $func='本期无修改的功能点';
        }
        $this->assign('func', $func);
        $sendTo = $_SESSION['testPlan']['assignee'] . '@zhidaoauto.com';
        $this->assign('sendTo', $sendTo);
        $cc = 'ylh@zhidaoauto.com;cf@zhidaoauto.com';
        $this->assign('cc', $cc);
        $subject = '【线下测试报告】'.$_SESSION['testPlan']['summary'] ;
        $this->assign('subject', $subject);
        if($data['mod']==1){
            $this->assign("resume",PublicController::editor("resume",$data['resume'],'desc',250));
            $this->assign("body",PublicController::editor("body",$data['body'],'desc',450));
        }
        $this->display();

    }

    public function online(){
        $_SESSION['url'] = '/' . C('PRODUCT') . '/Report/online';
        $this->isLogin();
        $planid=I('planid');
        $this->assign('planid', $planid);
        $where=array('planid'=>$planid,'type'=>'2');
        $data=M('tp_report')->where($where)->find();
        $this->assign('data', $data);
        $report_url=C('JIRAPI').'/Jira/Testcycle/index/tp/'.$planid;
        $this->assign('report_url', $report_url);
        if(!$_SESSION['testPlan']){
            $_SESSION['testPlan'] = getIssue($planid);
        }
        if($_SESSION['p2_num']){
            if($_SESSION['p3_num']){
                $bug_list='P2级Bug:'.$_SESSION['p2_num'].'个，P3级Bug'.$_SESSION['p3_num'].'个';
            }else{
                $bug_list='P2级Bug:'.$_SESSION['p2_num'].'个';
            }
        }else{
            if($_SESSION['p3_num']){
                $bug_list='P3级Bug:'.$_SESSION['p3_num'].'个';
            }else{
                $bug_list='无遗留BUG';
            }
        }
        $this->assign('bug_list', $bug_list);
        $quality='有效bug :'.$_SESSION['bug_num'].'个，Bug修复率 :'.$_SESSION['xiufl_num'].'%；P0级Bug :'.$_SESSION['p0_num'].'个，P1级Bug :'.$_SESSION['p1_num'].'个。';
        $this->assign('quality', $quality);
        $func='<br>';
        if($_SESSION['testfunc']){
            foreach ($_SESSION['testfunc'] as $k=>$f){
                $func.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.($k+1).')'.$f['summary'].';<br>';
            }
        }else{
            $func='本期无修改的功能点';
        }
        $this->assign('func', $func);

        $sendTo = $_SESSION['testPlan']['assignee'] . '@zhidaoauto.com';
        $this->assign('sendTo', $sendTo);
        $cc = 'ylh@zhidaoauto.com;cf@zhidaoauto.com';
        $this->assign('cc', $cc);
        $subject = '【线上验收报告】'.$_SESSION['testPlan']['summary'] ;
        $this->assign('subject', $subject);

        if($data['mod']==1){
            $this->assign("body",PublicController::editor("body",$data['body'],'desc',450));
            $this->assign("resume",PublicController::editor("resume",$data['resume'],'desc',250));
        }

        $this->display();
    }
    //创建测试报告
    function chuangj(){
        $_POST['table']='tp_report';
        $resume= '一、测试结论：<br>';
        $resume.= '&nbsp;&nbsp;&nbsp;&nbsp;1、测试结果：'.$_POST['result']. '<br>';
        if(!$_POST['result']=='通过'){
            $resume.= '&nbsp;&nbsp;&nbsp;&nbsp;原因：'.$_POST['yuanyin'].'<br>';
        }
        $resume.= '&nbsp;&nbsp;&nbsp;&nbsp;2、项目质量：'.$_POST['quality'].'<br>';
        $resume.= '&nbsp;&nbsp;&nbsp;&nbsp;…………<br>';
        $resume.= '从右侧复制邮件全文后在点击【发送】';
        $_POST['resume']=$resume;
        $body= '一、测试结论：<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;1、测试结果：'.$_POST['result']. '<br>';
        if(!$_POST['result']=='通过'){
            $body.= '&nbsp;&nbsp;&nbsp;&nbsp;原因：'.$_POST['yuanyin'].'<br>';
        }
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;2、项目质量：'.$_POST['quality'].'<br>';
        $body.= '二、详细测试情况：<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;1、主要功能：'.$_POST['main_func'].'<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;2、测试周期：'.$_POST['test_cycle'].' h<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;3、测试方法：'.$_POST['method'].'<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;4、测试功能点：'.$_POST['func'].'<br>';
        $body.= '三、主要问题&潜在风险：'.$_POST['risk'].'<br>';
        $body.= '四、遗留问题：<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;遗留问题总数：'.$_POST['bug_num'].'<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;问题分布情况：'.$_POST['bug_list'].'<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;遗留原因：'.$_POST['reason'].'<br>';
        $body.= '五、测试版本：<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;1、IOS版本：'.$_POST['ios'].'<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;2、Android版本：'.$_POST['android'].'<br><br><br>';
        $body.= '测试详情：<a href="'.$_POST['report_url'].'">'.$_POST['report_url'].'</a><br>';

        $_POST['body']=$body;
        $this->insert();
    }
    //更新测试报告
    function gengx(){
        $_POST['table']='tp_report';
        $resume= '一、测试结论：<br>';
        $resume.= '&nbsp;&nbsp;&nbsp;&nbsp;1、测试结果：'.$_POST['result']. '<br>';
        if(!$_POST['result']=='通过'){
            $resume.= '&nbsp;&nbsp;&nbsp;&nbsp;原因：'.$_POST['yuanyin'].'<br>';
        }
        $resume.= '&nbsp;&nbsp;&nbsp;&nbsp;2、项目质量：'.$_POST['quality'].'<br>';
        $resume.= '二、详细测试情况：<br>';
        $resume.= '&nbsp;&nbsp;&nbsp;&nbsp;1、主要功能：'.$_POST['main_func'].'<br>';
        $resume.= '&nbsp;&nbsp;&nbsp;&nbsp;2、测试周期：'.$_POST['test_cycle'].' h<br>';
        $resume.= '&nbsp;&nbsp;&nbsp;&nbsp;3、测试方法：'.$_POST['method'].'<br>';
        $resume.= '&nbsp;&nbsp;&nbsp;&nbsp;…………<br>';
        $resume.= '从右侧复制邮件全文后在点击【发送】';
        $_POST['resume']=$resume;
        $body= '一、测试结论：<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;1、测试结果：'.$_POST['result']. '<br>';
        if(!$_POST['result']=='通过'){
            $body.= '&nbsp;&nbsp;&nbsp;&nbsp;原因：'.$_POST['yuanyin'].'<br>';
        }
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;2、项目质量：'.$_POST['quality'].'<br>';
        $body.= '二、详细测试情况：<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;1、主要功能：'.$_POST['main_func'].'<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;2、测试周期：'.$_POST['test_cycle'].' h<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;3、测试方法：'.$_POST['method'].'<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;4、测试功能点：'.$_POST['func'].'<br>';
        $body.= '三、主要问题&潜在风险：'.$_POST['risk'].'<br>';
        $body.= '四、遗留问题：<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;遗留问题总数：'.$_POST['bug_num'].'<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;问题分布情况：'.$_POST['bug_list'].'<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;遗留原因：'.$_POST['reason'].'<br>';
        $body.= '五、测试版本：<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;1、IOS版本：'.$_POST['ios'].'<br>';
        $body.= '&nbsp;&nbsp;&nbsp;&nbsp;2、Android版本：'.$_POST['android'].'<br><br><br>';
        $body.= '测试详情：<a href="'.$_POST['report_url'].'">'.$_POST['report_url'].'</a><br>';
        $_POST['body']=$body;
        $this->update();
    }
    //编辑状态更新
    function mod(){
        $_GET['mod']='2';
        $_GET['table']='tp_report';
        $this->update();
    }
    //性能测试报告
    public function performance(){
        $this->display();
    }
    //编辑性能测试报告
    public function edit_perfor(){
        $this->display();
    }
    //迭代评估
    public function standard(){
        $var = '
        <h3>方案一：需求迭代</h3>
            &nbsp;&nbsp;<b>标准评估里程碑：</b><br />
               &nbsp;&nbsp; &nbsp;&nbsp; 1. 冒烟测试用例<br />
                &nbsp;&nbsp;&nbsp;&nbsp; 2. 准入验收（单次不超过4小时）<br />
                &nbsp;&nbsp;&nbsp;&nbsp; 3. 第一轮测试（不超过8小时，可以由多个人参与）<br />
                &nbsp;&nbsp;&nbsp;&nbsp; 4. 第二轮测试（不超过8小时，可以由多个人参与）<br />
                &nbsp;&nbsp;&nbsp;&nbsp; 5. 第三轮测试（不超过8小时，非必须，可以由多个人参与）<br />
                &nbsp;&nbsp;&nbsp;&nbsp; 6. 预发环境验证<br />
                &nbsp;&nbsp;&nbsp;&nbsp; 7. 线上环境验证<br />    
        <b><red>注意：从准入验收通过到达到上线标准，尽量控制一周之内</red></b>
        <h3>方案二：BUG迭代（单功能优化）</h3>
            &nbsp;&nbsp;<b>简化评估里程碑：</b><br />
                &nbsp;&nbsp;&nbsp;&nbsp; 1. 准入验收（单次不超过4小时）<br />
                &nbsp;&nbsp;&nbsp;&nbsp; 2. 预发环境验证<br />
                &nbsp;&nbsp;&nbsp;&nbsp; 3. 线上环境验证<br />
                
        <b><red>注意：尽量在准入验收通过当天上线</red></b>
    ';
        $this->assign('var', $var);

        $this->display();
    }
    //正常迭代
    public function manage()
    {
        $var = array(
            '1.周二，负责人参加需求讨论会，迭代和主任务维护至已排期的状态，所有测试人员按照抽签的方式决出迭代负责人',
            '2.周四，迭代负责人参加需求评审会，了解详细需求，制作相对应的测试计划并给出详细排期',
            '3.开发提测前一天<br>
                &nbsp;&nbsp;1）提供冒烟用例，测试用例必须包含步骤<br>
                &nbsp;&nbsp;2）Jira中任务和需求页面管理功能点用例，并分派给相关责任人（C类以上才迭代，至少2人参与）<br>
                &nbsp;&nbsp;3）整理本次迭代中改动和影响的功能点，并拉取到本期迭代当中<br>
                &nbsp;&nbsp;4）梳理本次迭代的业务场景用例',
            '4.提测当天<br>
                 &nbsp;&nbsp;1）开发任务组织产品负责人及测试负责人演示本次迭代的主要功能<br>
                 &nbsp;&nbsp;2）超过提测规定的时间，开发未完成提测预演，需要发邮件预警并记录到QC平台的迭代风险',
            '5.测试过程中<br>
                 &nbsp;&nbsp;1）按照测试用例，记录需要测试的每一个过程<br>
                 &nbsp;&nbsp;2）严格对照产品原型和效果图进行测试，原型上的每一个文案和提示都要进行对照，只要不符就是BUG（当然可以是产品设计的BUG）<br>
                 &nbsp;&nbsp;3）没一个测试不通过的用例都必须关联一个BUG或故障（可以是新创建的，也可以是关联以前未解决的）<br>
                 &nbsp;&nbsp;4）所有故障或者BUG必须记录在案，不必每次找开发或产品人员确认后再提交，允许BUG被打回<br>
                 &nbsp;&nbsp;5）测试-》预发，预发-》生产，原则上不允许带着未解决的BUG和未测试完成的用例，如果有必须发布风险预警<br>&nbsp;
                     6）按照项目管理部的要求发布《测试环境测试通过的测试报告》',
            '6.预发环境测试：<br>
                &nbsp;&nbsp;1）原则上要求预发环境安排在同一天完成<br>
                &nbsp;&nbsp;2）原则上要求走预发环境测试通过才可以上线，有特殊原因没有走预发，需要及时预警<br>
                &nbsp;&nbsp;3）预发环境原则上是不允许有较多的程序BUG产生的<br>
                &nbsp;&nbsp;&nbsp;&nbsp;（如果出现需要改代码才能修复的BUG出现，必须在迭代风险汇总记录并择期召开项目总结会）',
            '7.线上环境测试：<br>
                &nbsp;&nbsp;1）配合产品完成线上的测试验证<br>
                &nbsp;&nbsp;2）严格把控上线的过程，如果上线完成在第二天凌晨以后，记录迭代风险，择期召开项目总结会<br>
                &nbsp;&nbsp;3）到计划上线日期没有完成的上线（或不可能完成上线的，尽早发出延期风险预警，并记录迭代风险）<br>
                &nbsp;&nbsp;4）迭代上线后，负责人更新Jira的需求、任务、测试计划、测试周期、执行用例状态<br>&nbsp;
                    5）按照项目管理部的要求发布《线上环境测试通过的测试报告》'

        );
        $this->assign('var', $var);
        $this->display();
    }

    public function score()
    {
        $var1 = array(
            '1.为了规范测试流程和鼓励测试任务的记录',
            '2.形成半年度绩效考核的排序依据'
        );
        $this->assign('var1', $var1);

        $var2 = array(
            '1.多承担任务，多得积分（鼓励大家承担项目）',
            '2.多贡献策略和技能用于应用的，多得积分',
            '3.按照规范执行测试任务的，多得积分',
            '4.造成项目延期的，扣积分',
            '5.该预警的没有及时预警，扣积分',
            '6.上线质量差的，扣积分',
        );
        $this->assign('var2', $var2);

        $var3 = array(
            '初级工程师：<span class="badge">200分</span>',
            '中级工程师：<span class="badge">150分</span>',
            '高级工程师：<span class="badge">100分</span>',
            '资深工程师：<span class="badge">50分</span>'
        );
        $this->assign('var3', $var3);

        $var4 = array(
            'A类（30+个功能点）：<span class="badge">20分/10分</span>',
            'B类（20+个功能点）：<span class="badge">16分/8分</span>',
            'C类（10+功能点）：<span class="badge">10分/5分</span>',
            'D类（10个及以下的功能点）：<span class="badge">5分/0分</span>'
        );
        $this->assign('var4', $var4);

        $var5 = array(
            '1.提供详细用例 <span class="badge">3分</span>',
            '2.有详细测试用例执行记录 <span class="badge">5分</span>',
            '3.每个自然月里有扣分记录<span class="badge">10分</span>.',
            '4.将学到的技能应用于实际项目并显著提高工作效率或质量（需个人申请）<span class="badge">20-50分</span>',
            '5.每月的指定学习技能考试及格（5道题目，答对3个算及格）<span class="badge">10分</span>',
            '6.每月的指定学习技能考试满分<span class="badge">20分</span>'
        );
        $this->assign('var5', $var5);

        $var6 = array(
            '1.测试的直接原因造成项目延期<span class="badge">-5分</span>',
            '2.有测试的因素造成项目延期<span class="badge">-3分</span>',
            '3.预警环节没有及时预警<span class="badge">-10分</span>',
            '4.根据BUG的及时处理原则，有未及时复测BUG<span class="badge">-1分/个</span>',
            '5.C类以上项目独立完成<span class="badge">-3分</span>',
            '6.违反迭代评估规范进行项目周期评估<span class="badge">-3分</span>',
            '6.延期项目或上线超过凌晨的项目，一周之内没有召开总结会的<span class="badge">-5分</span>',
        );
        $this->assign('var6', $var6);

        $var7 = array(
            '1.上线后有P2级别以上未知的BUG（以Jira记录为准）<span class="badge">-3分</span>',
            '3.上线后有P0级别BUG<span class="badge">-50分/个</span>',
            '3.上线后有P1级别BUG<span class="badge">-10分/个</span>'
        );
        $this->assign('var7', $var7);

        $var8 = array(
            '1.项目需求评审当天，Jira中没有维护好测试计划和测试周期的<span class="badge">-3分</span>',
            '2.未及时更新Jira中的需求、任务、测试计划、测试周期、用例执行记录、BUG<span class="badge">-1分/项</span>',
            '3.项目风险未及时披露或未在QC平台记录<span class="badge">-3分</span>',
            '4.BUG记录描述不明确（需求&任务、模块、测试计划、重现数据&链接，现象截图，指派人等信息）<span class="badge">-1分</span>',
        );
        $this->assign('var8', $var8);

        $var9 = array(
            '1.恶意修改和伪造评分记录（扣为0分，不在参与本半年度考核项目）<span class="badge">有多少扣多少</span>',
        );
        $this->assign('var9', $var9);

        $this->display();

    }
    //测试BUG
    public function bug()
    {
        $var1 = array(
            '<b>开发人员：</b>当天17:30之前的BUG，必须当天进行确认（非BUG退回，是BUG确认，争议转产品经理）；<br>
            17：30以后的提交的BUG,可以第二天进行确认，尽量当天确定；<br>
            （温馨提示：BUG标记已解决的标准：开发负责部署的环境，部署到被测的环境才能标记已解决）',
            '<b>测试人员：</b>17:30前已解决的BUG，必须当天执行回归，17：30以后的解决的BUG,可以第二天进行回归，尽量当天回归；',
            '<b>产品经理：</b>17:30前指派给产品需要确认裁定的BUG，必须当天做出裁定，17：30以后的提交的BUG,可以第二天进行裁定，尽量当天裁定；'
        );
        $this->assign('var1', $var1);

        $var2 = array(
            '1.BUG提交标题必须简要明确',
            '2.没有关联测试记录的，一定要自己手工关联到对应的项目、模块、版本和需求信息（方便以后的分类汇总统计）',
            '3.必要的时候在BUG的实际结果正文处留下截图尽量不要用以附件的形式添加截图（以便尽可能的减少再次沟通）',
        );
        $this->assign('var2', $var2);

        $var3 = array(
            'P0：服务器崩溃、宕机、主链接404、主流程阻塞（影响接下来的流程执行），客户端有规律的崩溃（闪退）',
            'P1：产品基础功能缺失或不可用',
            'P2：非常用功能缺失或不可用，兼容性问题可视功能',
            'P3：界面BUG不影响功能实现',
            'P4：测试的建议（它本身不界定的BUG）'
        );
        $this->assign('var3', $var3);

        $var4 = array(
            '1.数字越低的越要有限解决，即1为最紧急，2为次紧急……',
            '2.默认情况为空，测试人员不做维护，这个字段只有在待解决的BUG量较大时，由产品经理维护',
            '3.开发人员依据标记优先解决紧急的BUG',
            '4.这个字段对P0，P1类BUG无效'
        );
        $this->assign('var4', $var4);

        $this->display();
    }

    //测试准入
    public function admittance()
    {
        $var = array(
            '1.测试人员计划提测的时间前（至少提前半天），提供本次迭代新功能的冒烟测试用例（即完全正向主流程用例）',
            '2.开发人员在提交测试时（和合并代码到Develop分支前），要通过冒烟测试，保证提交模块的基本功能可用（最正向的流程可以跑通）',
            '3.开发人员给产品和测试人员演示本次提测的基本功能（尽量包含冒烟测试用的全部内容）',
            '4.如果演示功能全部可用，则接收测试版本进入测试环节',
            '5.如果演示功能失败，将提测版本退回，认为开发人员没有按时间提测',
            '6.开发可以逐个功能分批次提交，测试人员在持续集成环境进行验证。这次迭代的所有功能均达到以上标准后方可定义为提交测试，然后再部署标准测试环境'
        );
        $this->assign('var', $var);
        $this->display();
    }
}