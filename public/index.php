<?php
/**
 * 网站首页
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

$fileRows = $db->getAll("SELECT id, file_key, file_name, file_size, created_at FROM uploads WHERE user_id='$uid' ORDER BY created_at DESC");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>相册- 上传列表</title>
</head>
<body>

<p>欢迎您，<?php echo $username; ?></p>
<h4>
    <a href="upload.php">上传照片</a>
    <a href="logout.php">注销退出</a>
</h4>

<table border="0" cellpadding="0" cellspacing="0">
  <tr height="50">
    <th align="left">文件名</th>
    <th width="100" align="center">文件大小</th>
    <th width="180" align="center">上传时间</th>
    <th width="120" align="center">操作</th>
  </tr>

<?php
foreach($fileRows as $row) {
?>
  <tr height="30">
    <td><a href="show.php?id=<?php echo $row["id"]; ?>" target="_blank" title="点击查看缩略图"><?php echo $row["file_name"]; ?></a></td>
    <td width="100" align="center"><?php echo parse_bytes($row["file_size"], 1024); ?></td>
    <td width="180" align="center"><?php echo date("Y-m-d H:i:s", $row["created_at"]); ?></td>
    <td width="120" align="center">
      <a href="show.php?id=<?php echo $row["id"]; ?>" target="_blank" title="点击查看缩略图">查看</a>
      <a href="download.php?id=<?php echo $row["id"]; ?>" title="点击下载原始尺图片">下载</a>
      <a href="delete.php?id=<?php echo $row["id"]; ?>" title="点击将该图片删除">删除</a>
    </td>
  </tr>
<?php
}
?>

</table>

</body>
</html>
