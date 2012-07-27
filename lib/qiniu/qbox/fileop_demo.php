#!/usr/bin/env php
<?php

require_once('rs.php');
require_once('fileop.php');

$client = QBox\OAuth2\NewClient();

$tblName = 'tblName';
$rs = QBox\RS\NewService($client, $tblName);

$key = '2.jpg';

list($result, $code, $error) = $rs->Get($key, $key);
echo "===> Get $key result:\n";
if ($code == 200) {
	var_dump($result);
} else {
	$msg = QBox\ErrorMessage($code, $error);
	echo "Get failed: $code - $msg\n";
	exit(-1);
}

$urlImageInfo = QBox\FileOp\ImageInfoURL($result['url']);

echo "===> ImageInfo of $key:\n";
echo file_get_contents($urlImageInfo) . "\n";


$targetKey = 'cropped-' . $key;
$source_img_url = $result['url'];
$opts = array("thumbnail" => "!120x120r",
              "gravity" => "center",
              "crop" => "!120x120a0a0",
              "quality" => 85,
              "rotate" => 45,
              "format" => "jpg",
              "auto_orient" => true);

$mogrifyPreviewURL = QBox\FileOp\ImageMogrifyPreviewURL($source_img_url, $opts);
echo "===> ImageMogrifyPreviewURL result:\n";
var_dump($mogrifyPreviewURL);

$imgrs = QBox\RS\NewService($client, "test_thumbnails_bucket");
list($result, $code, $error) = $imgrs->ImageMogrifyAs($targetKey, $source_img_url, $opts);
echo "===> ImageMogrifyAs $key result:\n";
if ($code == 200) {
	var_dump($result);
} else {
	$msg = QBox\ErrorMessage($code, $error);
	echo "ImageMogrifyAs failed: $code - $msg\n";
	exit(-1);
}
