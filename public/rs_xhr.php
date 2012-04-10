<?php
/**
 * 演示 - 从七牛云存储资源表里增删查数据
 *
 * @version $VersionId$ @ $UpdateTime$
 * @author 404 <why404@gmail.com>
 * @copyright Copyright (c) 2011-2012 404 <why404@gmail.com>
 * @license MIT License {@link http://www.opensource.org/licenses/mit-license.php}
 */
header('Pragma: no-cache');
header('Cache-Control: no-store');
header('Content-type: application/json');

require_once 'bootstrap.php';

/**
 * 生成 JSON 格式的输出
 */
function generate_output_data($result, $code, $error)
{
    $result = $code == 200 ? $result : array("errmsg" => QBox\ErrorMessage($code, $error));
    return json_encode(array("code" => $code, "data" => $result));
}

/**
 * 响应并分发请求
 */
$key = isset($_POST["key"]) ? trim($_POST["key"]) : "";
$act = isset($_POST["action"]) ? strtolower(trim($_POST["action"])) : "";

switch ($act) {
    # 获取一个文件的属性信息
    case "stat":
        list($result, $code, $error) = $rs->Stat($key);
        $resp = generate_output_data($result, $code, $error);
        break;
    # 取得一个文件的下载授权
    case "get":
        $attName = $key; # @TODO
        list($result, $code, $error) = $rs->Get($key, $attName);
        $resp = generate_output_data($result, $code, $error);
        break;
    # 删除一个文件
    case "delete":
	list($code, $error) = $rs->Delete($key);
        $resp = generate_output_data(array(), $code, $error);
        break;
    # 删除整个资源表（清空数据）
    case "drop":
	list($code, $error) = $rs->Drop();
        $resp = generate_output_data(array(), $code, $error);
        break;
    default:
        $resp = generate_output_data(array(), 400, array("error" => "Invalid URL, Unknow <action>: $act"));
}

die($resp);
