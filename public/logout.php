<?php
/**
 * 用户登出网站
 *
 * @version $VersionId$ @ $UpdateTime$
 * @author 404 <why404@gmail.com>
 * @copyright Copyright (c) 2011-2012 404 <why404@gmail.com>
 * @license MIT License {@link http://www.opensource.org/licenses/mit-license.php}
 */

require_once 'bootstrap.php';

unset($_COOKIE["uid"]);
setcookie("uid", "0", time() - 3600);
header("Location: login.php");
exit;
