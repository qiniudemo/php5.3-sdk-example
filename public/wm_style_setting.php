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

if (isset($_POST["wmstyle"]) && isset($_POST["width"]) && isset($_POST["height"])) {
	
	$param = "imageView/0";

	if (!empty($_POST["wmstyle"])) {
		$style = trim($_POST['wmstyle']);
	}
	if (!empty($_POST["width"])) {
		$param .= "/w/" . trim($_POST['width']);
	}
	if (!empty($_POST["height"])) {
		$param .= "/h/" . trim($_POST['height']);
	}	
	if (isset($_POST["format"])&&!empty($_POST["format"])) {
		$param .= "/format/" . trim($_POST['format']);
	}
	if (isset($_POST["quality"])&&!empty($_POST["quality"])) {
		$param .= "/q/" . trim($_POST['quality']);
	}
	if (isset($_POST["sharpen"])&&!empty($_POST["sharpen"])) {
		$param .= "/sharpen/" . trim($_POST['sharpen']);
	}
	if (isset($_POST["watermark"])&&!empty($_POST["watermark"])) {
		$param .= "/watermark/" . trim($_POST['watermark']);
	}
	
	
	
	list($result, $code, $error) = $rs->setProtected(0);
	if ($code != 200) {
		$msg = QBox\ErrorMessage($code, $error);
		echo "set Protected code failed: $code - $msg\n";
	}
	
	list($result, $code, $error) = $rs->setSeparator("_");
	if ($code == 200) {
		$msg = QBox\ErrorMessage($code, $error);
		echo "set Separator failed: $code - $msg\n";
	}

	list($result, $code, $error) = $rs->setStyle($style, $param);
	if ($code == 200) {
		$sql = "INSERT INTO `wmstyles`(`user_id`, `style`, `value`)
			 VALUES ('$uid', '$style', '$param')";	
		$db->insert($sql);
   		header("Location: index.php");
	} else {
		$msg = QBox\ErrorMessage($code, $error);
		echo "set samll.jpg Style failed: $code - $msg\n";
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

<form action="wm_style_setting.php" method="post">
  <fieldset>
    <legend>设置预览风格</legend>
     <p>
      <label for="wmstyle">名称：</label>
      <input name="wmstyle" id="wmstyle" type="text" tabindex="1" />
      <label for="width">宽度：</label>
      <input name="width" id="width" type="text" tabindex="2" />
      <label for="height">高度：</label>
      <input name="height" id="height" type="text" tabindex="3" />
    </p>
    <p>
      <label for="format">格式：</label>
      <input name="format" id="format" type="text" tabindex="4" />
      <label for="quality">品质：</label>
      <input name="quality" id="quality" type="text" tabindex="5" />
      <label for="sharpen">锐度：</label>
      <input name="sharpen" id="sharpen" type="text" tabindex="6" />
    </p>
    <p>
      <label for="watermark">水印：</label>
      <input name="watermark" id="watermark" type="text" tabindex="7" />
    </p>
    <p>
      <input name="submit" type="submit" value="保存" tabindex="9" />
    </p>
  </fieldset>
</form>

</body>
</html>
