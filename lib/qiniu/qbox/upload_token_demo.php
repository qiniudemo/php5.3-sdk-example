#!/usr/bin/env php
<?php

require_once('upload_token.php');

$QBOX_ACCESS_KEY = '<Please apply your access key>';
$QBOX_SECRET_KEY = '<Dont send your secret key to anyone>';

$opts = array(
    "scope"        => "test_bucket",
    "expires_in"   => 3600,
    "callback_url" => "http://example.com/callback?a=b&d=c",
    "return_url"   => "http://example.com/return?a=b&d=c"
);

$uploadToken = \QBox\NewUploadToken($opts);

var_dump($uploadToken);
