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

if (isset($_POST["text"]) && isset($_POST["dx"]) && isset($_POST["dy"])) {
	
	$param = array();

	if (!empty($_POST["text"])) {
		$param['text'] = trim($_POST['text']);
	}
	if (!empty($_POST["dx"])) {
		$param['dx'] =  trim($_POST['dx']);
	}
	if (!empty($_POST["dy"])) {
		$param['dy'] = trim($_POST['dy']);
	}	
	if (isset($_POST["font"])&&!empty($_POST["font"])) {
		$param['font'] = trim($_POST['font']);
	}
	if (isset($_POST["fill"])&&!empty($_POST["fill"])) {
		$param['fill'] = trim($_POST['fill']);
	}
	if (isset($_POST["bucket"])&&!empty($_POST["bucket"])) {
		$param['bucket'] = trim($_POST['bucket']);
	}
	if (isset($_POST["bissolve"])&&!empty($_POST["dissolve"])) {
		$param['dissolve'] = trim($_POST['dissolve']);
	}
	if (isset($_POST["pointsize"])&&!empty($_POST["pointsize"])) {
		$param['pointsize'] = trim($_POST['pointsize']);
	}
	
	list($result, $code, $error) = $wmrs->set('', $param);
	
	if ($code == 200) {
		header("Location: index.php");
	} else {
		$msg = QBox\ErrorMessage($code, $error);
		echo "set default watermark failed: $code - $msg\n";
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
   	 </p>    
     <p>      
      <label for="dx">横向边距：</label>
      <input name="dx" id="dx" type="text" tabindex="7" />
     </p>
     <p>      
      <label for="dy">纵向边距：</label>
      <input name="dy" id="dy" type="text" tabindex="7" />            
   	 </p>    
   	 <p>
      <label for="pointsize">字体大小：</label>
      <input name="pointsize" id="pointsize" type="text" tabindex="7" />
     </p>
     <p>
      <label for="fill">字体颜色：</label>
      <input name="fill" id="fill" type="text" tabindex="2" />
     </p>     
     <p>
      <label for="font">字体：</label>
      <input name="font" id="font" type="text" tabindex="1" />
     </p>
  	  <p>
      <label for="bucket">表名：</label>
      <input name="bucket" id="bucket" type="text" tabindex="4" />
     </p>
     <p>      
      <label for="gravity">位置：</label>
      <input name="gravity" id="gravity" type="text" tabindex="6" />
   	 </p>
     <p>      
      <label for="dissolve">透明度 ：</label>
      <input name="dissolve" id="dissolve" type="text" tabindex="5" />
     </p>
    <p>
      <input name="submit" type="submit" value="保存" tabindex="9" />
    </p>
  </fieldset>
</form>

</body>
</html>
