<?php
    //接收Json并转换为数组；
    function getJsonToArray(){
        $json=file_get_contents('php://input');
        $array=json_decode($json,true);
        return $array;
    }
