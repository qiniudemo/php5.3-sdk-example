<?php

require('eu.php');
require('utils.php');

$QBOX_ACCESS_KEY = '<Please apply your access key>';
$QBOX_SECRET_KEY = '<Dont send your secret key to anyone>';

$customer = '001';

$client = QBox\OAuth2\NewClient();

$eu = new QBox\EU\Service($client);

list($code, $error) = $eu->SetWatermark($customer, array('text' => 'abc'));
echo time() . " ===> SetWatermark result:\n";
if ($code == 200) {
	echo "SetWatermark ok!\n";
} else {
	$msg = QBox\ErrorMessage($code, $error);
	echo "SetWatermark failed: $code - $msg\n";
}

list($tpl, $code, $error) = $eu->GetWatermark($customer);
echo time() . " ===> GetWatermark result:\n";
if ($code == 200) {
	var_dump($tpl);
} else {
	$msg = QBox\ErrorMessage($code, $error);
	echo "GetWatermark failed: $code - $msg\n";
}

