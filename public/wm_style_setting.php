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

	error_log(print_r($_POST,true));
	
$wmstyle = trim($_POST["wmstyle"]);
$width = trim($_POST["width"]);
$height = trim($_POST["height"]);
$format = trim($_POST["format"]);
$quality = trim($_POST["quality"]);
$sharpen = trim($_POST["sharpen"]);

if (!empty($wmstyle) && !empty($width) && !empty($height)) {
	
	$param = "imageView/0";
	
	if (!empty($wmstyle)) {
		$style = $wmstyle;
	}
	if (!empty($width)) {
		$param .= "/w/" . $width;
	}
	if (!empty($height)) {
		$param .= "/h/" . trim($_POST['height']);
	}	
	if (!empty($format)) {
		$param .= "/format/" . $format;
	}
	if (!empty($quality)) {
		$param .= "/q/" . $quality;
	}
	if (!empty($sharpen)) {
		$param .= "/sharpen/" . $sharpen;
	}
	if (isset($_POST["watermark"])) {
		$param .= "/watermark/1";
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
      <label>(必填。图片预览风格的名称)</label>
     </p>
     <p>      
      <label for="width">宽度：</label>
      <input name="width" id="width" type="text" tabindex="2" />
      <label>(必填)</label>      
     </p>
     <p>      
      <label for="height">高度：</label>
      <input name="height" id="height"  type="text" tabindex="3" />
      <label>(必填)</label>      
     </p>
     <p>
      <label for="format">格式：</label>
      <input name="format" id="format" value="jpg" type="text" tabindex="4" />
      <label>(可选。包含jpg,png等图片格式)</label>      
     </p>
     <p>      
      <label for="quality">品质：</label>
      <input name="quality" id="quality" value="80" type="text" tabindex="5" />
      <label>(可选。范围为：0-100)</label>      
     </p>
     <p>      
      <label for="sharpen">锐度：</label>
      <input name="sharpen" id="sharpen" type="text" tabindex="6" />
      <label>(可选)</label>
     </p>
     <p>
      <label for="watermark">水印：</label>
      <input name="watermark" id="watermark" type="checkbox" tabindex="7" />
      <label>(可选，是否要给该预览风格加水印)</label>      
     </p>
     <p>
      <input name="submit" type="submit" value="保存" tabindex="9" />
     </p>
  </fieldset>
</form>

</body>
</html>
