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
define('QBOX_SDK_DIR', LIB_DIR . 'qiniu' . DIRECTORY_SEPARATOR);

/**
 * 加载配置文件
 */
require_once LIB_DIR . 'config.php';
require_once LIB_DIR . 'helper.php';
require_once LIB_DIR . 'pdo.class.php';

require_once QBOX_SDK_DIR . 'rs.php';

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
$qiniu_access_key = $config["qbox"]["access_key"];
$qiniu_secret_key = $config["qbox"]["secret_key"];

$qiniu_up_host = $config['qbox']['up_host'];

$bucket = $config["qbox"]["bucket"];
Qiniu_SetKeys($qiniu_access_key, $qiniu_secret_key);
$putPolicy = new Qiniu_RS_PutPolicy($bucket);
$putPolicy->CallbackUrl = $config['qbox']['callback_url'];
$putPolicy->CallbackBody = $config['qbox']['callback_body'];
$upToken = $putPolicy->Token(null);

$client = new Qiniu_MacHttpClient(null);

$domain = $config['qbox']['domain'];
