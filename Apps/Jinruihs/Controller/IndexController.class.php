<?php
namespace Jinruihs\Controller;
class IndexController extends BaseController
{
    private function init(){
        $data = array(
            'table_activity'      => 'marketing_activity',
            'name'                => 'Index',
            'where'               =>array(),
            'order'               =>'sn desc'
        );
        return $data;
    }

    public function index()
    {
        $info=$this->init();
        $this->display();
    }
    //公司简介
    public function introduction(){
//        $info=$this->init();
        $content='<h1>公司简介</h1>';
        $content.='<p style="text-indent:2em;"> 北京金瑞恒升科技有限公司是一家基于IT产品销售，私人定制服务的综合性科技公司。</p>';
        $content.='<p style="text-indent:2em;"> 公司先后与英特尔、联想、肆拾玖坊达成了合作协议，是英特尔SSD、NUC、联想麦克风、硬盘等系列产品，华北区核心分销商，是肆拾玖坊定制酒的战略合作伙伴。</p>';
        $content.='<p style="text-indent:2em;"> 公司本着合作供赢，诚信为基石，践行分享经济、利他的模式的，以人为本的理念，为企业提供服务及产品；为新中产提创意服务及产品。</p>';
        $content.='<p style="text-indent:2em;"> 企业坚持四原则：</p>';
        $content.='<p style="padding-left:4em">
                        客户第一原则<br>
                        优质产品原则<br>
                        诚信为首原则<br>
                        合法创新原则<br>
                    </p>';
        $content.='<p style="text-indent:2em;">搭建了华北区核心分销体系，并且与重要客户达成战略合作关系，成果得到了厂商及合作伙伴的充分认可。期待未来与更多的合作伙伴共创辉煌！</p>';
        $this->assign("content", $content);

        $this->display();
    }

  

}