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
define('QBOX_SDK_DIR', LIB_DIR . '3rd' . DIRECTORY_SEPARATOR . 'qiniu' . DIRECTORY_SEPARATOR . 'qbox' . DIRECTORY_SEPARATOR);
define("TMP_TOKEN_FILE", sys_get_temp_dir() . DIRECTORY_SEPARATOR . '.qbox_tokens');

/**
 * 加载配置文件
 */
require_once LIB_DIR . 'config.php';
require_once LIB_DIR . 'helper.php';
require_once LIB_DIR . 'pdo.class.php';

require_once QBOX_SDK_DIR . 'rs.php';
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
 * 初始化 OAuth Client Transport
 */
$client = QBox\OAuth2\NewClient();

/**
 * 登录授权
 */
list($code, $result) = QBox\OAuth2\ExchangeByPasswordPermanently($client, $config["qbox"]["username"], $config["qbox"]["password"], TMP_TOKEN_FILE);
if ($code != 200) {
	$msg = QBox\ErrorMessage($code, $result);
	echo "Login failed: $code - $msg\n";
	exit(-1);
}

/**
 * 初始化 Qbox Reource Service Transport
 */
$tableName = $config["qbox"]["tb_name"];
$rs = QBox\RS\NewService($client, $tableName);
