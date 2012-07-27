#!/usr/bin/env php
<?php

require('rs.php');
require('client/rs.php');

$QBOX_ACCESS_KEY = '<Please apply your access key>';
$QBOX_SECRET_KEY = '<Dont send your secret key to anyone>';

$client = QBox\OAuth2\NewClient();

$tblName = 'tblName';
$rs = QBox\RS\NewService($client, $tblName);

$key = 'put_demo.php';
$localFile = __FILE__;

list($code, $error) = $rs->Drop();
echo "===> Drop bucket result:\n";
if ($code == 200) {
	echo "Drop ok!\n";
} else {
	$msg = QBox\ErrorMessage($code, $error);
	echo "Drop failed: $code - $msg\n";
}

list($result, $code, $error) = $rs->PutAuth();
echo "===> PutAuth result:\n";
if ($code == 200) {
	var_dump($result);
} else {
	$msg = QBox\ErrorMessage($code, $error);
	echo "PutFile failed: $code - $msg\n";
	exit(-1);
}

list($result, $code, $error) = QBox\RS\PutFile($result['url'], $tblName, $key, 'image/jpg', $localFile, '', array('key' => $key));
echo "===> PutFile $key result:\n";
if ($code == 200) {
	var_dump($result);
} else {
	$msg = QBox\ErrorMessage($code, $error);
	echo "PutFile failed: $code - $msg\n";
	exit(-1);
}

list($result, $code, $error) = $rs->Stat($key);
echo "===> Stat $key result:\n";
if ($code == 200) {
	var_dump($result);
} else {
	$msg = QBox\ErrorMessage($code, $error);
	echo "Stat failed: $code - $msg\n";
	exit(-1);
}

