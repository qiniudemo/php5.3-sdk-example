<?php

namespace QBox\RS;

require_once('oauth.php');
require_once('utils.php');
require_once('fileop.php');

/**
 * Resource Storage (Key-Value) Service
 * 资源存储(键值对)。基本特性为：每个账户可创建多个表，每个表包含多个键值对(Key-Value对)，Key是任意的字符串，Value是一个文件。
 */
class Service
{
	public $Conn;
	public $Bucket;

	public function __construct($conn, $bucketName = '') {
		$this->Conn = $conn;
		$this->Bucket = $bucketName;
	}

	/**
	 * func PutAuth() => (data PutAuthRet, code int, err Error)
	 * 上传授权（生成一个短期有效的可匿名上传URL）
	 */
	public function PutAuth() {
		$url = QBOX_IO_HOST . '/put-auth/';
		return \QBox\OAuth2\Call($this->Conn, $url);
	}

	/**
	 * func Put(key string, mimeType string, fp File, bytes int64) => (data PutRet, code int, err Error)
	 * 上传一个流
	 */
	public function Put($key, $mimeType, $fp, $bytes) {
		global $QBOX_PUT_TIMEOUT;
		if ($mimeType === '') {
			$mimeType = 'application/octet-stream';
		}
		$entryURI = $this->Bucket . ':' . $key;
		$url = QBOX_IO_HOST . '/rs-put/' . \QBox\Encode($entryURI) . '/mimeType/' . \QBox\Encode($mimeType);
		return \QBox\OAuth2\CallWithBinary($this->Conn, $url, $fp, $bytes, $QBOX_PUT_TIMEOUT);
	}

	/**
	 * func Put2(key string, mimeType string, fp File, bytes int64) => (data PutRet, code int, err Error)
	 * 上传一个流
	 */
	public function Put2($key, $mimeType, $fp, $bytes) {
		global $QBOX_PUT_TIMEOUT;
		if ($mimeType === '') {
			$mimeType = 'application/octet-stream';
		}
		$entryURI = $this->Bucket . ':' . $key;
		$url = QBOX_UP_HOST . '/rs-put/' . \QBox\Encode($entryURI) . '/mimeType/' . \QBox\Encode($mimeType);
		return \QBox\OAuth2\CallWithBinary($this->Conn, $url, $fp, $bytes, $QBOX_PUT_TIMEOUT);
	}

	/**
	 * func PutFile(key string, mimeType string, localFile string) => (data PutRet, code int, err Error)
	 * 上传文件
	 */
	public function PutFile($key, $mimeType, $localFile) {
		$fp = fopen($localFile, 'rb');
		if (!$fp)
			return array(null, -1, array('error' => 'open file failed'));
		$fileStat = fstat($fp);
		$fileSize = $fileStat['size'];
		$result = $this->Put($key, $mimeType, $fp, $fileSize);
		fclose($fp);
		return $result;
	}

	/**
	 * func PutFile2(key string, mimeType string, localFile string) => (data PutRet, code int, err Error)
	 * 上传文件
	 */
	public function PutFile2($key, $mimeType, $localFile) {
		$fp = fopen($localFile, 'rb');
		if (!$fp)
			return array(null, -1, array('error' => 'open file failed'));
		$fileStat = fstat($fp);
		$fileSize = $fileStat['size'];
		$result = $this->Put2($key, $mimeType, $fp, $fileSize);
		fclose($fp);
		return $result;
	}

	/**
	 * func Get(key string, attName string) => (data GetRet, code int, err Error)
	 * 下载授权（生成一个短期有效的可匿名下载URL）
	 * attName为可选参数
	 */
	public function Get($key, $attName) {
		$entryURI = $this->Bucket . ':' . $key;
		$url = QBOX_RS_HOST . '/get/' . \QBox\Encode($entryURI);
		if (!empty($attName)) {
			$url = $url . '/attName/' . \QBox\Encode($attName);
		}
		return \QBox\OAuth2\Call($this->Conn, $url);
	}

	/**
	 * func BatchGet(params array) => (result array, code int, err Error)
	 * 批量下载授权（生成一堆短期有效的可匿名下载URL）
	 * paramsarray 的元素可以是两种情况：1. string 类型。表示 key 2. array 类型。要求是：array('key' => $key, 'attName' => $attName, 'expires' => 3600)，其中 'attName'、'expires' 为可选。
	 * result 是一个这样 {code, GetRet} 的数组
	 */
	public function BatchGet(array $params) {
		$ops = "";
		foreach($params as $obj) {
			if (!empty($ops)) {
				$ops = $ops . '&';
			}

			if (is_string(array_shift($params))) {
				$entryURI = $this->Bucket . ':' . $obj;
				$ops = $ops . 'op=/get/' . \QBox\Encode($entryURI);
			} else {
				$entryURI = $this->Bucket . ':' . $obj['key'];
				$ops = $ops . 'op=/get/' . \QBox\Encode($entryURI);
				if (!empty($obj['attName'])) {
					$ops = $ops . '/attName/' . \QBox\Encode($obj['attName']);
				}
				if (!empty($obj["expires"])) {
					$ops = $ops . '/expires/' . $obj["expires"];
				}
			}
		}

		$url = QBOX_RS_HOST . '/batch';
		return \QBox\OAuth2\CallWithParams($this->Conn, $url, $ops);
	}

	/**
	 * func GetIfNotModified(key string, attName string, base string) => (data GetRet, code int, err Error)
	 * 下载授权（生成一个短期有效的可匿名下载URL），如果服务端文件没被人修改的话（用于断点续传）
	 */
	public function GetIfNotModified($key, $attName, $base) {
		$entryURI = $this->Bucket . ':' . $key;
		$url = QBOX_RS_HOST . '/get/' . \QBox\Encode($entryURI) . '/attName/' . \QBox\Encode($attName) . '/base/' . $base;
		return \QBox\OAuth2\Call($this->Conn, $url);
	}

	/**
	 * func Stat(key string) => (entry Entry, code int, err Error)
	 * 取资源属性
	 */
	public function Stat($key) {
		$entryURI = $this->Bucket . ':' . $key;
		$url = QBOX_RS_HOST . '/stat/' . \QBox\Encode($entryURI);
		return \QBox\OAuth2\Call($this->Conn, $url);
	}

	/**
	 * func Publish(domain string) => (code int, err Error)
	 * 将本 Table 的内容作为静态资源发布。静态资源的url为：http://domain/key
	 */
	public function Publish($domain) {
		$url = QBOX_RS_HOST . '/publish/' . \QBox\Encode($domain) . '/from/' . $this->Bucket;
		return \QBox\OAuth2\CallNoRet($this->Conn, $url);
	}

	/**
	 * func Unpublish(domain string) => (code int, err Error)
	 * 取消发布
	 */
	public function Unpublish($domain) {
		$url = QBOX_RS_HOST . '/unpublish/' . \QBox\Encode($domain);
		return \QBox\OAuth2\CallNoRet($this->Conn, $url);
	}

	/**
	 * func Delete(key string) => (code int, err Error)
	 * 删除资源
	 */
	public function Delete($key) {
		$entryURI = $this->Bucket . ':' . $key;
		$url = QBOX_RS_HOST . '/delete/' . \QBox\Encode($entryURI);
		return \QBox\OAuth2\CallNoRet($this->Conn, $url);
	}

	/**
	 * func Drop() => (code int, err Error)
	 * 删除整个表（慎用！）
	 */
	public function Drop() {
		$url = QBOX_RS_HOST . '/drop/' . $this->Bucket;
		return \QBox\OAuth2\CallNoRet($this->Conn, $url);
	}

	/**
	 * 图像处理接口（可持久化存储缩略图）
	 * func ImageMogrifyAs(<DestKey>, <SourceImageDownloadURL>, <opts>, <callbackFunc>) => Entry
	 * opts = {
	 *   "thumbnail": <ImageSizeGeometry>,
	 *   "gravity": <GravityType>, =NorthWest, North, NorthEast, West, Center, East, SouthWest, South, SouthEast
	 *   "crop": <ImageSizeAndOffsetGeometry>,
	 *   "quality": <ImageQuality>,
	 *   "rotate": <RotateDegree>,
	 *   "format": <DestinationImageFormat>, =jpg, gif, png, tif, etc.
	 *   "auto_orient": <TrueOrFalse>
	 * }
	 */
	public function ImageMogrifyAs($key, $source_img_url, $opts) {
		$mogrifyParams = \QBox\FileOp\mkImageMogrifyParams($opts);
		return $this->SaveAs($key, $source_img_url, $mogrifyParams);
	}

	/**
	 * 持久化存储一个经过云端服务处理过后的资源
	 */
	public function SaveAs($key, $source_url, $opWithParams) {
		$entryURI = $this->Bucket . ':' . $key;
		$saveAsEntryURI = \QBox\Encode($entryURI);
		$saveAsParam = "/save-as/" . $saveAsEntryURI;
		$newurl = $source_url . '?' . $opWithParams . $saveAsParam;
		return \QBox\OAuth2\Call($this->Conn, $newurl);
	}

	/**
	 * 对 Bucket 设置保护，使资源本身不能被直接访问（只能访问被授权的经处理过的资源，比如打水印的图片）
	 */
	public function SetProtected($protectedMode){
		$url = QBOX_PU_HOST . "/accessMode/" . $this->Bucket . "/mode/" . $protectedMode;
		return \QBox\OAuth2\Call($this->Conn, $url);
	}

	/**
	 * 设置分隔符
	 */
	public function SetSeparator($sep){
		$url = QBOX_PU_HOST . "/separator/" . $this->Bucket . "/sep/" .\QBox\Encode($sep);
		return \QBox\OAuth2\Call($this->Conn, $url);
	}

	public function SetStyle($name, $style){
		$url = QBOX_PU_HOST . "/style/" . $this->Bucket . "/name/" . \QBox\Encode($name) . "/style/" .\QBox\Encode($style);
		return \QBox\OAuth2\Call($this->Conn, $url);
	}

	public function UnsetStyle($name){
		$url = QBOX_PU_HOST . "/unstyle/" . $this->Bucket . "/name/" . \QBox\Encode($name);
		return \QBox\OAuth2\Call($this->Conn, $url);
	}
}

/**
 * func NewService(conn *Client, bucketName string) => (rs *Service)
 * 创建 RS 资源存储服务
 */
function NewService($conn, $bucketName = '') {
	return new Service($conn, $bucketName);
}
