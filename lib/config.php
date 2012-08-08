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
        'access_key' => 'bE21M6FW9V7zAFrBY5psgKOKJQLiBj12qMWTpc57',
        'secret_key' => 'uMo7Nyq_eDK_CuQ8_FYCxoTHQZqjiaPh-cbiKO7L',
        'bucket'     => 'photo360_albums',
    	'img_bucket' => "img_photo360_albums",
    ),

);
