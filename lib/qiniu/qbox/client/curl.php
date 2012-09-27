<?php

namespace QBox;

/**
 * HTTP Methods
 */
const HTTP_METHOD_GET    = 'GET';
const HTTP_METHOD_POST   = 'POST';
const HTTP_METHOD_PUT    = 'PUT';
const HTTP_METHOD_DELETE = 'DELETE';
const HTTP_METHOD_HEAD   = 'HEAD';

/**
 * HTTP Form content types
 */
const HTTP_FORM_CONTENT_TYPE_APPLICATION = 0;
const HTTP_FORM_CONTENT_TYPE_MULTIPART = 1;

function ExecuteRequest(
	$url,
	array $parameters = array(),
	$http_method = \QBox\HTTP_METHOD_GET,
	$http_headers = null,
	$form_content_type = \QBox\HTTP_FORM_CONTENT_TYPE_MULTIPART,
	$curl_extra_options = null
	)
{
	$curl_options = array(
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_SSL_VERIFYPEER => true,
		    CURLOPT_CUSTOMREQUEST  => $http_method
		    );
	if (!empty($curl_extra_options)) {
		foreach ($curl_extra_options as $k => $v)
			$curl_options[$k] = $v;
	}

	switch($http_method)
	{
		case \QBox\HTTP_METHOD_POST:
		    $curl_options[CURLOPT_POST] = true;
		    /* No break */
		case \QBox\HTTP_METHOD_PUT:
		    /**
		     * Passing an array to CURLOPT_POSTFIELDS will encode the data as multipart/form-data, 
		     * while passing a URL-encoded string will encode the data as application/x-www-form-urlencoded.
		     * http://php.net/manual/en/function.curl-setopt.php
		     */
		    if (!isset($curl_options[CURLOPT_UPLOAD])) {
				if (\QBox\HTTP_FORM_CONTENT_TYPE_APPLICATION === $form_content_type) {
					$parameters = http_build_query($parameters);
				}
				$curl_options[CURLOPT_POSTFIELDS] = $parameters;
		    }
		    break;
		case \QBox\HTTP_METHOD_HEAD:
		    $curl_options[CURLOPT_NOBODY] = true;
		    /* No break */
		case \QBox\HTTP_METHOD_DELETE:
		case \QBox\HTTP_METHOD_GET:
		    $url .= '?' . http_build_query($parameters, null, '&');
		    break;
		default:
		    break;
	}

	$curl_options[CURLOPT_URL] = $url;

	if (is_array($http_headers)) 
	{
		$header = array();
		foreach($http_headers as $key => $parsed_urlvalue) {
		    $header[] = "$key: $parsed_urlvalue";
		}
		$curl_options[CURLOPT_HTTPHEADER] = $header;
	}

	$ch = curl_init();
	curl_setopt_array($ch, $curl_options);
	$result = curl_exec($ch);
	//var_dump($result);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
	curl_close($ch);

	if ($content_type === "application/json") {
		$json_decode = json_decode($result, true);
	} else {
		$json_decode = null;
	}
	return array(
		    'result' => (null === $json_decode) ? $result : $json_decode,
		    'code' => $http_code,
		    'content_type' => $content_type
		    );
}

