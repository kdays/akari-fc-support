<?php

namespace AkariFC;

use Akari\system\ioc\Injectable;
use Akari\system\util\helper\AppValueTrait;

class Cookie extends Injectable implements ICanFree {

    use AppValueTrait;

    protected $httpHeaders = [];
    protected $values = [];

    public function __construct() {
        $this->values = $this->request->getParsedRequest()->getCookieParams();
    }

    public function getHeaders() {
        return $this->httpHeaders;
    }

    protected function mergeCookieStr($name, $value, $expires = 0, $path = "", $domain = "", $secure = false, $http_only = false) {
        $value = rawurlencode($value);
        $date = gmdate("D, d-M-Y H:i:s",$expires) . ' GMT';
        $header = "{$name}={$value}";
        if($expires != 0) $header .= "; expires={$date}; Max-Age=".($expires - time());
        if($path != "") $header .= "; path=".$path;
        if($domain != "") $header .= "; domain=".$domain;
        if($secure) $header .= "; secure";
        if($http_only) $header .= "; HttpOnly";

        return $header;
    }

    public function exists(string $name, $prefix = TRUE) {
        if ($prefix) {
            $prefix = is_string($prefix) ? $prefix : $this->_getConfigValue("cookiePrefix", '');
        } else {
            $prefix = '';
        }

        return array_key_exists($prefix . $name, $this->values);
    }

    public function set(string $name, string $value, ?int $time = NULL, array $options = []) {
        if (is_numeric($time)) {
            $time += time();
        } else {
            $time = (empty($time) || $time == 'now') ? 0 : strtotime($time);
        }

        $path = $options['path'] ?? $this->_getConfigValue("cookiePath", '/');
        $domain = $options['domain'] ?? $this->_getConfigValue("cookieDomain", '');
        $prefix = $options['prefix'] ?? $this->_getConfigValue("cookiePrefix", '');

        $name = $prefix . $name;

        if ($value === FALSE) {
           // setcookie($name, '', TIMESTAMP - 3600, $path, $domain, false, $options['http_only'] ?? FALSE);
          //  unset($this->_values[$name]);
            unset($this->values[$name]);
            $this->httpHeaders[$name] = $this->mergeCookieStr(
                $name,
                '',
                TIMESTAMP - 3600,
                $path,
                $domain,
                false,
                $options['http_only'] ?? FALSE
            );
        } else {
           // setcookie($name, $value, $time, $path, $domain, false, $options['http_only'] ?? FALSE);
            $this->values[$name] = $value;
            $this->httpHeaders[$name] = $this->mergeCookieStr(
                $name,
                $value,
                $time,
                $path,
                $domain,
                FALSE,
                $options['http_only'] ?? FALSE
            );
        }
    }

    public function get(string $name, $prefix = TRUE, $defaultValue = NULL) {
        if ($prefix) {
            $prefix = is_string($prefix) ? $prefix : $this->_getConfigValue("cookiePrefix", '');
        } else {
            $prefix = '';
        }

        $name = $prefix . $name;

        return array_key_exists($name, $this->values) ? $this->values[$name] : $defaultValue;
    }

    public function remove(string $key, $prefix = TRUE) {
        if ($prefix) {
            $prefix = is_string($prefix) ? $prefix : $this->_getConfigValue("cookiePrefix", '');
        } else {
            $prefix = '';
        }

        return $this->set($key, FALSE, NULL, ['prefix' => $prefix]);
    }

    public function freeRes() {
        $this->values = [];
        $this->httpHeaders = [];
    }


}