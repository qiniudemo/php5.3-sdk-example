<?php
/**
 * 删除文件
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

    # check user is valid or not
    $userRow = $db->getOne("SELECT id FROM users WHERE id='$uid' LIMIT 1");
    # if is this user ok, then
    if ((int)$userRow["id"] > 0 && !empty($id)) {
        $fileRow = $db->getOne("SELECT file_key FROM uploads WHERE id='$id' LIMIT 1");
        list($code, $error) = $rs->Delete($fileRow["file_key"]);
        if ($code == 200) {
            $res = $db->execute("DELETE FROM uploads WHERE id='$id' AND user_id='$uid'");
        }
    }
}

header("Location: index.php");
exit;
