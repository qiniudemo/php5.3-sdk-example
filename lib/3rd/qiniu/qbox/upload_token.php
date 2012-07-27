<?php

namespace QBox;

require_once('config.php');
require_once('utils.php');


class UploadToken {

    private $opts = array();
    private $access_key;
    private $secret_key;
    private $signature;
    private $encoded_digest;

    function __construct($access_key, $secret_key)
    {
        $this->access_key = $access_key;
        $this->secret_key = $secret_key;
    }

    public function set($key, $value)
    {
        if (is_string($key) && preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $key))
            $this->opts[$key] = $value;
    }

    private function _get($key)
    {
        return isset($this->opts[$key]) ? $this->opts[$key] : "";
    }

    private function _generate_signature()
    {
        $params = array("scope" => $this->_get("scope"), "deadline" => time() + $this->_get("expires_in"));
        $callback_url = $this->_get("callback_url");
        if ($callback_url != "")
            $params["callbackUrl"] = $callback_url;
        $return_url = $this->_get("return_url");
        if ($return_url != "")
            $params["returnUrl"] = $return_url;
        $this->signature = \QBox\Encode(json_encode($params));
    }

    private function _generate_encoded_digest()
    {
        $digest = hash_hmac('sha1', $this->signature, $this->secret_key, true);
        $this->encoded_digest = \QBox\Encode($digest);
    }

    public function generate_token()
    {
        $this->_generate_signature();
        $this->_generate_encoded_digest();
        return $this->access_key . ':' . $this->encoded_digest . ':' . $this->signature;
    }
}

function NewUploadToken(array $opts)
{
    global $QBOX_ACCESS_KEY, $QBOX_SECRET_KEY;
    $tokenObj = new UploadToken($QBOX_ACCESS_KEY, $QBOX_SECRET_KEY);
    $tokenObj->set("scope", $opts["scope"]);
    $tokenObj->set("expires_in", $opts["expires_in"]);
    $tokenObj->set("callback_url", $opts["callback_url"]);
    $tokenObj->set("return_url", $opts["return_url"]);
    return $tokenObj->generate_token();
}

