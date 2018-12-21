<?php
return array(
	//'配置项'=>'配置值'
	'SHOW_PAGE_TRACE'       => true,                         //显示页面Trace信息false
    'SESSION_AUTO_START'    => true,                        //开启SESSION
    'URL_MODEL'             => '2',                         //URL模式
    'MODULE_ALLOW_LIST'     => array(                       //设置允许模块
        'Admin','Demo','Back', 'Jinruihs','Business','Coupon','Goods','Linker',
        'User','Order','Customer','Market','Payment ','Jira','Jirapi','Lagou'
    ),
    'DEFAULT_MODULE'        => 'Demo',                      //设置默认模块设置
    'MODULE_DENY_LIST'      => array('Common','Runtime'),  // 禁止访问的模块列表
    'URL_CASE_INSENSITIVE'  => true,                        //不区分大小写
    'ONLINE'                => 0,                            //环境，0：测试环境；1：生产环境
    'TMPL_L_DELIM'          => '<{',
    'TMPL_R_DELIM'          => '}>',
    'TMPL_ACTION_ERROR'     => 'Public:error',
    'TMPL_ACTION_SUCCESS'   => 'Public:success',
    'DB_TYPE'               => 'mysql',
    'DB_PREFIX'             => '',
    'DB_CHARSET'            => 'utf8',
    'URL_ROUTER_ON'         => true,
    'ERROR_MESSAGE'         => '页面错误！请稍后再试～',//错误显示信息,非调试模式有效
    'ERROR_PAGE'            => '',                      // 错误定向页面
    'SHOW_ERROR_MSG'        => false,                  // 显示错误信息
    'TRACE_MAX_RECORD'      => 100,                    // 每个级别的错误信息 最大记录数
);