<?php
return array(
    //'配置项'=>'配置值'
    'DEFAULT_THEME'     => 'Amaze',
    'PRODUCT'           => 'Wechat',
    'maxPageNum'        => 200,
    'DB_HOST'           => '127.0.0.1',
    'DB_NAME'           => 'xiuli',
    'DB_USER'           => 'root',
    'DB_PWD'            => 'root',
    'DB_PORT'           => '3306',
    'DB_PREFIX'         => 'tp_',
    'URL_ROUTE_RULES'   => array(
        'Token/:appId'     => 'Action/index',
    ),
);