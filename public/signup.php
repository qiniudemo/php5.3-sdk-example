<?php
/**
 * 用户注册
 *
 * @version $VersionId$ @ $UpdateTime$
 * @author 404 <why404@gmail.com>
 * @copyright Copyright (c) 2011-2012 404 <why404@gmail.com>
 * @license MIT License {@link http://www.opensource.org/licenses/mit-license.php}
 */

require_once 'bootstrap.php';

if (isset($_POST["email"]) && isset($_POST["password"])) {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($email) || empty($password)) {
        echo 'ERROR: email and password cannot be blank';
        exit(-1);
    }

    $res = $db->getOne("SELECT id FROM users WHERE email='$email'");
    if ($res) {
        echo 'ERROR: user exists';
        exit(-1);
    }

    $passwordSalt = generate_salt();
    $encryptedPassword = encrypt($passwordSalt, $password);
    $timeNow = time();
    $insertSQL = "INSERT INTO users(username , email, encrypted_password, password_salt, created_at, updated_at)"
               . "  VALUES('$email', '$email', '$encryptedPassword', '$passwordSalt', '$timeNow', '$timeNow')";
    $lastInsertId = $db->insert($insertSQL);
    if ($lastInsertId > 0) {
        if (setcookie("uid", $lastInsertId)) {
            header("Location: index.php");
            exit;
        }
    } else {
        echo 'ERROR: Insert Faild';
        exit(-1);
    }

}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>相册 - 登录</title>
</head>

<body>

<h4>
  <a href="login.php">已有帐号？立即登录</a>
</h4>

<form action="signup.php" method="post">
  <fieldset>
    <legend>注册帐号</legend>
    <p>
      <label for="email">邮箱：</label>
      <input name="email" id="email" type="text" tabindex="1" />
    </p>
    <p>
      <label for="password">密码：</label>
      <input name="password" id="password" type="password" tabindex="2" />
    </p>
    <p>
      <input name="submit" type="submit" value="立即注册" tabindex="3" />
    </p>
  </fieldset>
</form>

<body>
</html>
