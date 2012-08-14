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

if (isset($_POST['text']))
{
	$text = trim($_POST["text"]);
	$dx = trim($_POST["dx"]);
	$dy = trim($_POST["dy"]);
	$font = trim($_POST["font"]);
	$fill = trim($_POST["fill"]);
	$bucket = trim($_POST["bucket"]);
	$gravity = trim($_POST["gravity"]);
	$dissolve = trim($_POST["dissolve"]);
	$pointsize = trim($_POST["pointsize"]);
	
	error_log(print_r($_POST, true));
	
	if (!empty($text)) {
	
		$param = array();
	
		if (!empty($text)) {
			$param['text'] = $text;
		}
		if (!empty($dx)) {
			$param['dx'] = $dx;
		}
		if (!empty($dy)) {
			$param['dy'] = $dy;
		}
		if (!empty($font)) {
			$param['font'] = $font;
		}
		if (!empty($fill)) {
			$param['fill'] = $fill;
		}
		if (!empty($bucket)) {
			$param['bucket'] = $bucket;
		}
		if (!empty($gravity)) {
			$param['gravity'] = $gravity;
		}
		if (!empty($dissolve)) {
			$param['dissolve'] = $dissolve;
		}
	
		if (!empty($pointsize)) {
			$param['pointsize'] = $pointsize;
		}
	
		list($result, $code, $error) = $wmrs->SetWatermark('', $param);
	
		if ($code == 200) {
			header("Location: index.php");
		} else {
			$msg = QBox\ErrorMessage($code, $error);
			echo "set default watermark failed: $code - $msg\n";
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

<form action="wm_tpl_setting.php" method="post">
  <fieldset>
    <legend>添加水印模板</legend>
     <p>      
      <label for="text">水印文字：</label>
      <input name="text" id="text" type="text" tabindex="3" />
      <label>(必填。要打的水印的文字，其中 图片用 \0 - \9 占位，会去下面所填的表名中查找key从0到9的图片)</label>      
   	 </p>    
     <p>      
      <label for="dx">横向边距：</label>
      <input name="dx" id="dx" type="text" tabindex="7" />
      <label>(可选，默认值为10。)</label>
     </p>
     <p>      
      <label for="dy">纵向边距：</label>
      <input name="dy" id="dy" type="text" tabindex="7" /> 
      <label>(可选，默认值为10。)</label>           
   	 </p>    
   	 <p>
      <label for="pointsize">字体大小：</label>
      <input name="pointsize" id="pointsize" type="text" tabindex="7" />
      <label>(可选,0表示默认，单位: 缇，等于 1/20 磅)</label>
     </p>
     <p>
      <label for="fill">字体颜色：</label>
      <input name="fill" id="fill" type="text" tabindex="2" />
      <label>(可选)</label>
     </p>     
     <p>
      <label for="font">字体：</label>
      <input name="font" id="font" type="text" tabindex="1" />
      <label>(可选)</label>      
     </p>
  	  <p>
      <label for="bucket">表名：</label>
      <input name="bucket" id="bucket" type="text" tabindex="4" />
	  <label>(如果水印中有图片，需要指定图片所在的 RS Bucket 名。)</label>      
     </p>
     <p>      
      <label for="gravity">位置：</label>
      <input name="gravity" id="gravity" type="text" tabindex="6" />
      <label>(可选，字符串，默认为左上角（NorthWest）)</label>
   	 </p>
     <p>      
      <label for="dissolve">透明度 ：</label>
      <input name="dissolve" id="dissolve" type="text" tabindex="5" />
      <label>(可选，字符串，如50%)</label>
     </p>
    <p>
      <input name="submit" type="submit" value="保存" tabindex="9" />
    </p>
  </fieldset>
</form>

</body>
</html>
