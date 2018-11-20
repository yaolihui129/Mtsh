<?php

    //无提示插入
    function insert($table,$data){
        $m = D($table);
        $data['adder'] = cookie(C(appID).'_isLogin');
        $data['moder'] = cookie(C(appID).'_isLogin');
        $data['ctime'] = time();
        if($m->create($data)){
            $id=$m->add($data);
            return $id;
        }else{
            return 0;
        }
    }

    //无提示更新
    function update($table,$data){
        $data['moder'] = cookie(C(appID).'_isLogin');
        if (D($table)->save($data)) {
            return 1;
        } else {
            return 0;
        }
    }

    //无提示逻辑删除
    function del($table,$id)
    {
        $_GET['id'] = $id;
        $_GET['moder'] = cookie(C(appID).'_isLogin');
        $_GET['deleted'] = 1;
        if (D($table)->save($_GET)) {
            return 1;
        } else {
            return 0;
        }
    }

