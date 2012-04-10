<?php
/**
 * 文件上传成功后执行的回调处理
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

if (empty($_COOKIE["uid"]) || (int)$_COOKIE["uid"] < 1) {
    die(json_encode(array("code" => 401, "data" => array("errmsg" => "unauthorized"))));
}
$uid = $_COOKIE["uid"];

/**
 * 响应并分发请求
 */

# 取得将要执行操作的类型
$act = isset($_POST["action"]) ? strtolower(trim($_POST["action"])) : "";

switch ($act) {

    # 如果是写表操作
    case "insert":
    
        # 首先接值
        $filekey = isset($_POST["file_key"]) ? trim($_POST["file_key"]) : "";
        $filename = isset($_POST["file_name"]) ? trim($_POST["file_name"]) : "";
        $filesize = isset($_POST["file_size"]) ? (int)trim($_POST["file_size"]) : 0;
        $filetype = isset($_POST["file_type"]) ? trim($_POST["file_type"]) : "";
        
        # 然后检查有效性
        if($filekey == "" || $filename == ""){
            $resp = json_encode(array("code" => 400, "data" => array("errmsg" => "Invalid Params, <file_key> and <file_name> cannot be empty")));
            die($resp);
        }
        
        # 再写表
        $timenow = time();
        $insertSQL = "INSERT INTO uploads(user_id, file_key, file_name, file_size, file_type, created_at)
                        VALUES('$uid', '$filekey', '$filename', '$filesize', '$filetype', '$timenow')";
        $lastInsertId = $db->insert($insertSQL);
        
        # 最后返回处理结果
        if ($lastInsertId > 0) {
            die(json_encode(array("code" => 200, "data" => array("success" => true))));
        } else {
            die(json_encode(array("code" => 499, "data" => array("errmsg" => "Insert Failed"))));
        }
        break;

    # 如果是未知操作，返回错误
    default:
        $resp = json_encode(array("code" => 400, "data" => array("errmsg" => "Invalid URL, Unknow <action>: $act")));
        die($resp);
}
