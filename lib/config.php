<?php

define('APPNAME', 'photo360');
define('HOMEPAGE', '/');
define('DOMAIN', $_SERVER["HTTP_HOST"]);
define('HOST', 'http://'.DOMAIN.'/');

$config = array(

    # DEBUG
    'error' => array(
        'reporting'       => 4095,
        'throw_exception' => true,
    ),

    # 数据库
    'db' => array(
        'adapter'            => 'mysql',
        'host'               => '127.0.0.1',
        'dbname'             => APPNAME . '_development',
        'username'           => APPNAME . '_devuser',
        'password'           => APPNAME . '_development_password',
        'charset'            => 'utf8',
        'use_pconnect'       => true,
        'use_buffered_query' => true,
        'throw_exception'    => true,
    ),

    # qiniu account
    'qbox' => array(
        'access_key' => '<Please apply your access key>',
        'secret_key' => '<Dont send your secret key to anyone>',
        'bucket'     => '',
        'domain' => '',
        'up_host' => '',
        'callback_url' => HOST . 'callback.php',
        'callback_body' => 'uid=($x:uid)&file_key=($x:file_key)&file_name=($x:file_name)&file_size=($x:file_size)&file_type=($x:file_type)',
    ),

);
