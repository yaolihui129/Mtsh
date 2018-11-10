<?php
return array(
    //'配置项'=>'配置值'
    'TMPL_PARSE_STRING' => array(
        '__CSS__' => 'https://xiuliguanggao.com/Public/css',
        '__JS__' => 'https://xiuliguanggao.com/Public/js',
    ),
    'TMPL_ACTION_ERROR' => 'Public:error',
    'TMPL_ACTION_SUCCESS' => 'Public:success',
    'DEFAULT_THEME' => 'Amaze',//默认模板目录
    'PRODUCT' => 'Record',//定义产品编号
    'DB_PREFIX' => 'eo_',
    'PROJECTTYPE' => array(
        array('key' => 0, 'value' => 'Web'),
        array('key' => 1, 'value' => 'App'),
        array('key' => 2, 'value' => 'Pc'),
        array('key' => 3, 'value' => '物联网'),
        array('key' => 4, 'value' => '其他'),
    )

);