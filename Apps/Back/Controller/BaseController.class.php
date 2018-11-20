<?php
namespace Back\Controller;
use Think\Controller;
class BaseController extends Controller
{
    public function _initialize()
    {
        //判定登录态
        $isLogin=cookie(C(PRODUCT).'_isLogin');
        $user=cookie(C(PRODUCT).'_user');
        if($isLogin==''||$user==''){
            $this->redirect(C(PRODUCT).'/Login/index');
        }
        //判定是否是手机模式
        if (ismobile()) {//设置默认默认主题为 Amaze
            C('DEFAULT_V_LAYER', 'Amaze');
        }
    }

    function _empty()
    {
        $this->display('index');
    }

    function getAppList(){
        $where=array('user_id'=>cookie(C(PRODUCT).'_user'),'deleted'=>'0');
        $data=M('admin_user_app')->where($where)->field('app_id')->select();
        $appList=array();
        if($data){
            foreach ($data as $k=>$da){
                $appList[$k]['key']=$da['app_id'];
                $appList[$k]['value']=getName('admin_app',$da['app_id'],'website');
            }
        }
        return $appList;
    }

    function getMerchantList(){
        $where=array('user_id'=>cookie(C(PRODUCT).'_user'),'deleted'=>'0');
        $data=M('admin_merchant_user')->where($where)->field('merchant_id')->select();
        $merchantList=array();
        if($data){
            foreach ($data as $k=>$da){
                $merchantList[$k]['key']=$da['merchant_id'];
                $merchantList[$k]['value']=getName('admin_merchant',$da['merchant_id']);
            }
        }
        return $merchantList;
    }


    function getPublicNumberList($merchant=''){

        if(!$merchant){
            $where=array('user_id'=>cookie(C(PRODUCT).'_user'),'deleted'=>'0');
            $data=M('admin_merchant_user')->where($where)->field('merchant_id')->select();
            $merchant=$data[0];
        }
        $where=array('merchant_id'=>$merchant,'deleted'=>'0');
        $where['type']=array('in',['0','1']);
        $data=M('admin_merchant_app')->where($where)->field('app_id')->select();
        $publicNumberList=array();
        if($data){
            foreach ($data as $k=>$da){
                $publicNumberList[$k]['key']=$da['app_id'];
                $publicNumberList[$k]['value']=getName('admin_app',$da['app_id']);
            }
        }
        return $publicNumberList;
    }
    
    function select($data, $name, $value)
    {
        $html = '<select name="' . $name . '" class="form-control">';
        foreach ($data as $v) {
            $selected = ($v['key'] == $value) ? "selected" : "";
            $html .= '<option ' . $selected . ' value="' . $v['key'] . '">' . $v['value'] . '</option>';
        }
        $html .= '<select>';
        return $html;
    }

    public function order()
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

    public function insert()
    {
        $m = D(I('table'));
        if (IS_GET) {
            $_GET['adder'] = cookie(C(PRODUCT).'_user');
            $_GET['moder'] = cookie(C(PRODUCT).'_user');
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
            $_POST['adder'] = cookie(C(PRODUCT).'_user');
            $_POST['moder'] = cookie(C(PRODUCT).'_user');
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

    //修改
    public function update()
    {
        if (IS_GET) {
            $_GET['moder'] = cookie(C(PRODUCT).'_user');
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
            $_POST['moder'] = $_SESSION['user'];
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

    //逻辑删除
    public function del()
    {
        $_POST['id'] = I('id');
        $_POST['moder'] = cookie(C(PRODUCT).'_user');
        $_POST['deleted'] = 1;
        if (D(I('table'))->save($_POST)) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }




}