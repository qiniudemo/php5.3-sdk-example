<?php
/**
 * 网站入口文件
 *
 * @version $VersionId$ @ $UpdateTime$
 * @author 404 <why404@gmail.com>
 * @copyright Copyright (c) 2011-2012 404 <why404@gmail.com>
 * @license MIT License {@link http://www.opensource.org/licenses/mit-license.php}
 */

/**
 * 定义时区
 */
date_default_timezone_set('Asia/Shanghai');

/**
 * 定义网站目录
 */
define('ROOT_DIR', str_replace(array('\\\\', '//'), DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR);
define('LIB_DIR', ROOT_DIR . 'lib' . DIRECTORY_SEPARATOR);
define('QBOX_SDK_DIR', LIB_DIR . 'qiniu' . DIRECTORY_SEPARATOR . 'qbox' . DIRECTORY_SEPARATOR);

/**
 * 加载配置文件
 */
require_once LIB_DIR . 'config.php';
require_once LIB_DIR . 'helper.php';
require_once LIB_DIR . 'pdo.class.php';

require_once QBOX_SDK_DIR . 'rs.php';
require_once QBOX_SDK_DIR . 'wmrs.php';
require_once QBOX_SDK_DIR . 'fileop.php';
require_once QBOX_SDK_DIR . 'client/rs.php';

/**
 * 设置错误报告级别
 */
error_reporting($config['error']['reporting']);

/**
 * 初始化数据库连接句柄
 */
$db = Core_Db::getInstance($config["db"]);

/**
 * 配置七牛云存储密钥信息
 */
$QBOX_ACCESS_KEY = $config["qbox"]["access_key"];
$QBOX_SECRET_KEY = $config["qbox"]["secret_key"];

/**
 * 初始化 OAuth Client Transport
 */
$client = QBox\OAuth2\NewClient();

/**
 * 初始化 Qbox Reource Service Transport
 */
$bucket = $config["qbox"]["bucket"];
$img_bucket = $config["qbox"]["img_bucket"];
$rs = QBox\RS\NewService($client, $bucket);
$img_rs = QBox\RS\NewService($client, $img_bucket);
$wmrs  = QBox\WMRS\NewService($client);
