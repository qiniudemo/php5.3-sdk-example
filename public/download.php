<?php
/**
 * 下载文件
 *
 * @version $VersionId$ @ $UpdateTime$
 * @author 404 <why404@gmail.com>
 * @copyright Copyright (c) 2011-2012 404 <why404@gmail.com>
 * @license MIT License {@link http://www.opensource.org/licenses/mit-license.php}
 */

require_once 'bootstrap.php';

if (empty($_COOKIE["uid"]) || (int)$_COOKIE["uid"] < 1) {
    header("Location: login.php");
    exit;
}
$uid = $_COOKIE["uid"];

if (isset($_GET["id"])) {
    $id = trim($_GET["id"]);
    $fileRow = $db->getOne("SELECT file_key, file_name FROM uploads WHERE id='$id' AND user_id='$uid' LIMIT 1");

    $key = $fileRow["file_key"];
    $attName = $fileRow["file_name"];

    if (!empty($key)) {
        list($ret, $err) = Qiniu_RS_Stat($client, $bucket, $key);
        if ($err == null) {            
            $base_url = Qiniu_RS_MakeBaseUrl($domain, $key);
            header("Location: $base_url");
            exit;
        }
    }
}

header("Location: index.php");
exit;
