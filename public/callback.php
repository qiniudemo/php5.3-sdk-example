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
$act = isset($_POST["action"]) ? strtolower(trim($_POST["action"])) : "";

switch ($act) {
    case "insert":
        $filekey = isset($_POST["file_key"]) ? trim($_POST["file_key"]) : "";
        $filename = isset($_POST["file_name"]) ? trim($_POST["file_name"]) : "";
        $filesize = isset($_POST["file_size"]) ? (int)trim($_POST["file_size"]) : 0;
        $filetype = isset($_POST["file_type"]) ? trim($_POST["file_type"]) : "";
        if($filekey == "" || $filename == ""){
            $resp = json_encode(array("code" => 400, "data" => array("errmsg" => "Invalid Params, <file_key> and <file_name> cannot be empty")));
            die($resp);
        }
        $timenow = time();
        $insertSQL = "INSERT INTO uploads(user_id, file_key, file_name, file_size, file_type, created_at)
                        VALUES('$uid', '$filekey', '$filename', '$filesize', '$filetype', '$timenow')";
        $lastInsertId = $db->insert($insertSQL);
        if ($lastInsertId > 0) {
            die(json_encode(array("code" => 200, "data" => array("success" => true))));
        } else {
            die(json_encode(array("code" => 499, "data" => array("errmsg" => "Insert Failed"))));
        }
        break;
    default:
        $resp = json_encode(array("code" => 400, "data" => array("errmsg" => "Invalid URL, Unknow <action>: $act")));
        die($resp);
}
