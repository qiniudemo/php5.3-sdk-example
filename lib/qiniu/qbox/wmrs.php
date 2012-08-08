<?php
namespace QBox\WMRS;

class Service{
	public $Conn;
	
	public function __construct($conn){
		$this->Conn = $conn;
	}
	
	public function get($customer){
		$url = QBOX_WM_HOST . '/wmget';
		$params = array("customer"=>$customer);
		return \QBox\OAuth2\CallWithParams($this->Conn, $url, $params);
	}
	
	public function set($customer, array $tpl){
		$url = QBOX_WM_HOST . '/wmset';
		$tpl['customer'] = $customer;
		return \QBox\OAuth2\CallWithParams($this->Conn, $url, $tpl);
	}
	
		
}
function NewService($conn) {
	return new Service($conn);
}






