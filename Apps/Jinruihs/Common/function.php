<?php

    //无提示插入
    function insert($table,$data){
        $m = D($table);
        $data['adder'] = $_SESSION['user'];
        $data['moder'] = $_SESSION['user'];
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
        $data['moder'] = $_SESSION['user'];
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
        $_GET['moder'] = $_SESSION['user'];
        $_GET['deleted'] = 1;
        if (D($table)->save($_GET)) {
            return 1;
        } else {
            return 0;
        }
    }
