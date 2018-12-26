<?php
namespace Back\Controller;
class BaseController extends WechatController
{
    public function _initialize()
    {
        //判定登录态
        $isLogin=cookie(C('PRODUCT').'_isLogin');
        $user=cookie(C('PRODUCT').'_user');
        if($isLogin==''||$user==''){
            $this->redirect(C('PRODUCT').'/Login/index');
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

    /**
     ** 操作数据库
     **   1. 排序
     **   2. 插入
     **   3. 修改
     **   4. 逻辑删除
     **   5. 物理删除
     **   6. 获取列表
     **   7. 查找数据
     **   8. 查找符合条件的一条数据
     **   9. 计数
     */
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
    function insert($table,$data,$file='',$msg='成功',$eMsg='失败')
    {
        $_POST=$data;
        if($_POST[$file]){
            $info=$this->uploadFile(C('PRODUCT'),'img');
            $_POST[$file]=$info[$file]['savepath'].$info[$file]['savename'];
            $this->tailoringImg($info,$file);
        }
        $user=getLoginUser();
        $_POST['adder'] = $user;
        $_POST['moder'] = $user;
        $_POST['ctime'] = time();
        $m = D($table);
        if (!$m->create()) {
            $this->error($m->getError());
        }
        if ($m->add()) {
            if ($_POST['url']) {
                $this->success($msg, U($_POST['url']));
            } else {
                $this->success($msg);
            }
        } else {
            $this->error($eMsg);
        }
    }
    function update($table,$data,$file='',$msg='成功',$eMsg='失败')
    {
        $_POST=$data;
        if($file){
            $info=$this->uploadFile();
            $_POST[$file]=$info[$file]['savepath'].$info[$file]['savename'];
            $this->tailoringImg($info,$file);
        }
        $_POST['moder'] = getLoginUser();
        if (D($table)->save($_POST)) {
            if ($_POST['url']) {
                $this->success($msg, U($_POST['url']));
            } else {
                $this->success($msg);
            }
        } else {
            $this->error($eMsg);
        }
    }
    function delete($table,$arrId,$msg='成功',$eMsg='失败')
    {
        $user=getLoginUser();
        $info='';
        if(is_array($arrId)){
            foreach ($arrId as $vo){
                $_POST['id'] = $vo;
                $_POST['moder'] = $user;
                $_POST['deleted'] = 1;
                $info[]=D($table)->save($_POST);
            }
        }else{
            $_POST['id'] = $arrId;
            $_POST['moder'] = $user;
            $_POST['deleted'] = 1;
            $info=D($table)->save($_POST);
        }
        if ($info) {
            $this->success($msg);
        } else {
            $this->error($eMsg);
        }
    }
    function realDelete($table,$arrId,$msg='成功',$eMsg='失败')
    {
        $count = D($table)->delete($arrId);
        if ($count > 0) {
            $this->success($msg);
        } else {
            $this->error($eMsg);
        }
    }

    function imgUpdate($table,$savePath,$data,$img='img',$url='',$msg='成功',$eMsg='失败'){
        $user=jie_mi(cookie(C('PRODUCT').'_user'));
        $_POST=$data;
        $_POST['moder']=$user;
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
                    $this->success($msg,U($_POST['url']));
                }else{
                    $this->success($msg);
                }
            }else{
                $this->error($eMsg);
            }
        }else {
            $_POST[$img]=$info[$img]['savepath'].$info[$img]['savename'];
            if (D($table)->save($_POST)){
                $image = new \Think\Image();
                $image->open('./Upload/'.$info[$img]['savepath'].$info[$img]['savename']);
                $image->thumb(600, 400)->save('./Upload/'.$info[$img]['savepath'].$info[$img]['savename']);
                if($url){
                    $this->success($msg,U($_POST['url']));
                }else{
                    $this->success($msg);
                }
            }else{
                $this->error($eMsg);
            }
        }
    }
    function imgInsert($table,$savePath,$data,$img='img',$url='',$msg='成功',$eMsg='失败'){
        $user=jie_mi(cookie(C('PRODUCT').'_user'));
        $_POST=$data;
        $_POST['moder']=$user;
        //处理上传图片
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize  =     7145728 ;// 设置附件上传大小
        $upload->exts     =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath =  './Upload';// 设置附件上传目录
        $upload->savePath = '/'.$savePath.'/'; // 设置附件上传目录
        $info  =   $upload->upload();
        $m = D($table);
        if (!$m->create()) {
            $this->error($m->getError());
        }
        if(!$info) {// 上传错误提示错误信息或没有上传图片
            if ($m->add()){
                if($url){
                    $this->success($msg,U($url));
                }else{
                    $this->success($msg);
                }
            }else{
                $this->error($eMsg);
            }
        }else {
            $_POST[$img]=$info[$img]['savepath'].$info[$img]['savename'];
            if ($m->add()){
                $image = new \Think\Image();
                $image->open('./Upload/'.$info[$img]['savepath'].$info[$img]['savename']);
                $image->thumb(600, 400)->save('./Upload/'.$info[$img]['savepath'].$info[$img]['savename']);
                if($url){
                    $this->success($msg,U($url));
                }else{
                    $this->success($msg);
                }
            }else{
                $this->error($eMsg);
            }
        }
    }

    //文件上传
    function uploadFile($savePath='Jira',$type='img'){
        $upload = new \Think\Upload();
        // 设置附件上传大小
        $upload->maxSize  = 7145728 ;
        // 设置附件上传类型
        if($type=='img'){
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        }elseif ($type=='excel'){
            $upload->exts = array('xls', 'xlsx');
        }else{
            $upload->exts = array();
        }
        // 设置附件上传目录
        $upload->rootPath =  './Upload';
        // 设置附件上传目录
        $upload->savePath = '/'.$savePath.'/';
        $info =  $upload->upload();
        return $info;
    }
    //图片剪裁
    function tailoringImg($info,$img,$width='600',$height='400'){
        $image = new \Think\Image();
        $imgUrl='./Upload/'.$info[$img]['savepath'].$info[$img]['savename'];
        $image->open($imgUrl);
        $image->thumb($width, $height)->save($imgUrl);
    }






}