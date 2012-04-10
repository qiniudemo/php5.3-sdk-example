<?php
/**
 * 浏览图片
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
$userinfo = $db->getOne("SELECT username FROM users WHERE id='$uid' LIMIT 1");
$username = $userinfo["username"];

if (isset($_GET["id"])) {
    $id = trim($_GET["id"]);
    $fileRow = $db->getOne("SELECT id, file_key, file_name, file_size, created_at FROM uploads WHERE id='$id' LIMIT 1");

    $key = $fileRow["file_key"];
    $attName = $fileRow["file_name"];

    if (!empty($id)) {
        list($result, $code, $error) = $rs->Get($key, $attName);
        if ($code == 200) {
            $previewURL = QBox\FileOp\ImagePreviewURL($result['url'], 0);
        } else {
            $errnum = $code;
            $errmsg = QBox\ErrorMessage($code, $error);
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>相册 - 浏览图片</title>
</head>
<body>

<p>欢迎您，<?php echo $username; ?></p>
<h4>
  <a href="index.php">返回列表</a>
  <a href="upload.php">上传照片</a>
  <a href="logout.php">注销退出</a>
</h4>

<?php
if ($previewURL) {
?>

<p>文件名：<?php echo $fileRow["file_name"]; ?></p>
<p>文件大小：<?php echo parse_bytes($fileRow["file_size"]); ?></p>
<p>上传时间：<?php echo date("Y-m-d H:i:s", $fileRow["created_at"]); ?></p>
<p>
  <a href="download.php?id=<?php echo $fileRow["id"]; ?>" title="点击下载原始尺图片">下载</a>
  <a href="delete.php?id=<?php echo $fileRow["id"]; ?>" title="点击将该图片删除">删除</a>
</p>
<img src="<?php echo $previewURL; ?>" />

<?php
} else {
?>

<p>出错啦，请您稍后再试！</p>
<br />
<p>错误码：<?php echo $errnum; ?></p>
<p>出错信息：<?php echo $errmsg; ?></p>

<?php
}
?>

<body>
</html>
