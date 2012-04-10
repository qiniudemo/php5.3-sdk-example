<?php
/**
 * 公共函数
 *
 * @version $VersionId$ @ $UpdateTime$
 * @author 404 <why404@gmail.com>
 * @copyright Copyright (c) 2011-2012 404 <why404@gmail.com>
 * @license MIT License {@link http://www.opensource.org/licenses/mit-license.php}
 */

/* 文件大小转换 */
function parse_bytes($fileSize, $unit=1024)
{
    $k=$unit;
    $m=$unit*$k;
    $g=$unit*$m;
    $fileSize = (int)$fileSize;
    if($fileSize > $k){
      if($fileSize > $m){
        if($fileSize > $g){
          $fileSize = round(($fileSize/$g), 2).' GB';
        }else{
          $fileSize = round(($fileSize/$m), 2).' MB';
        }
      } else {
        $fileSize = round(($fileSize/$k), 2).' KB';
      }
    } else if($fileSize > 0) {
      $fileSize = $fileSize.' B';
    }
    return $fileSize;
}

function url_safe_base64_encode($str)
{
    $find = array("+","/");
    $replace = array("-", "_");
    return str_replace($find, $replace, base64_encode($str));
}

function url_safe_base64_decode($str)
{
    $find = array("-","_");
    $replace = array("+", "/");
    return base64_decode(str_replace($find, $replace, $str));
}

function generate_salt()
{
    return md5(uniqid(mt_rand(), true));
}

function encrypt_password($password)
{
    $salt = generate_salt();
    return encrypt($salt, $password);
}

function encrypt($salt, $password)
{
    return sha1("--$salt--$password--");
}
