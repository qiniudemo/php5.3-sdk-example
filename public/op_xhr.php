<?php
/**
 * 演示 - 七牛云存储图像处理接口使用
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
 * 响应并分发请求
 */
$key = isset($_POST["key"]) ? trim($_POST["key"]) : "";
$act = isset($_POST["action"]) ? strtolower(trim($_POST["action"])) : "";

$fileRow = $db->getOne("SELECT file_name FROM uploads WHERE file_key='$key' AND user_id=0 LIMIT 1");
$attName = $fileRow["file_name"];

list($result, $code, $error) = $rs->Get($key, $attName);
if($code != 200) {
    echo json_encode(array("code" => $code, "data" => array("errmsg" => QBox\ErrorMessage($code, $error))));
    exit(-1);
}
$opURL = $result['url'];

switch ($act) {
    case "image_info":
        $url = QBox\FileOp\ImageInfoURL($opURL);
        break;
    case "image_preview":
        $thumbType = isset($_POST["type"]) ? (int)trim($_POST["type"]) : 1;
        $url = QBox\FileOp\ImagePreviewURL($opURL, $thumbType);
        break;
    case "make_style":
        $templPngFile = isset($_POST["imagefile"]) ? trim($_POST["imagefile"]) : "";
        $paramStr = isset($_POST["imagedesc"]) ? trim($_POST["imagedesc"]) : "";
        $quality = isset($_POST["quality"]) ? (int)trim($_POST["quality"]) : 85;
        $url = QBox\FileOp\StylePreviewURL($opURL, $templPngFile, $paramStr, $quality);
        break;
    default:
        $resp = json_encode(array("code" => 400, "data" => array("errmsg" => "Invalid URL, Unknow <action>: $act")));
        die($resp);
        exit(-1);
}

$resp = json_encode(array("code" => 200, "data" => array("url" => $url)));
die($resp);
