<?php
namespace AkariFC;

use Akari\system\http\FileUpload;
use Akari\system\ioc\Injectable;
use Akari\system\security\FilterFactory;
use Akari\system\util\TextUtil;

class Request extends Injectable implements ICanFree {

    protected static $request;
    public static function setFcRequest($request) {
        self::$request = $request;
    }

    /**
     * 获得请求的字符串
     * @return string
     */
    public function getQueryString() {
        return self::$request->getQueryString();
    }

    public function getPathInfo() {
        return self::$request->getAttribute('path');
    }

    public function getReferrer() {
        return self::$request->getAttribute('referrer');
    }

    public function getUserIP() {
        return self::$request->getAttribute('clientIP');
    }

    public function getRequestURI() {
        return self::$request->getAttribute('requestURI');
    }

    public function getHost() {
        return self::$request->getAttribute('host');
    }

    public function getRequestMethod() {
        return strtoupper(self::$request->getMethod());
    }

    /**
     * 是否是POST请求
     *
     * @return bool
     */
    public function isPost() {
        return $this->getRequestMethod() == 'POST';
    }

    /**
     * @return bool
     */
    public function isGet() {
        return $this->getRequestMethod() == 'GET';
    }

    protected function getQueryParams() {
        return self::$request->getQueryParams();
    }

    public function has($key) {
        return $this->hasPost($key) || $this->hasQuery($key);
    }

    public function hasQuery($key) {
        return array_key_exists($key, $this->getQueryParams());
    }

    public function getQuery($key, $filter = "default", $defaultValue = NULL, $allowArray = NULL) {
        return $this->_filterValue($key, $this->getQueryParams(), $filter, $defaultValue, $allowArray);
    }

    public function getRawBody() {
        return self::$request->getBody()->getContent();
    }

    public function getJsonRawBody($assoc = TRUE) {
        return json_decode($this->getRawBody(), $assoc);
    }

    protected $multiRes;
    public function getParsedRequest() {
        if ($this->multiRes === NULL) {
            $this->multiRes = (new MultipartParser())->parse(self::$request);
        }

        return $this->multiRes;
    }

    public function getPost($key, $filter = "default", $defaultValue = NULL, $allowArray = NULL) {
        $postValues = $this->getParsedRequest()->getParsedBody();
        return $this->_filterValue($key, $postValues, $filter, $defaultValue, $allowArray);
    }

    public function hasPost($key) {
        return array_key_exists($key, $this->getParsedRequest()->getParsedBody());
    }

    protected function _filterValue($key, $values, $filter, $defaultValue, $allowArray) {
        if ($key === NULL) {
            $result = [];
            foreach ($values as $key => $value) {
                // 取全部值的时候只有明确FALSE才禁止array
                if ($allowArray === FALSE && is_array($value)) {
                    $value = NULL;
                }

                $result[$key] = FilterFactory::doFilter($value, $filter);
            }

            return $result;
        }

        if (is_array($values) && array_key_exists($key, $values)) {
            if (($allowArray === NULL || $allowArray === FALSE) && is_array($values[$key])) {
                return $defaultValue;
            }

            return FilterFactory::doFilter($values[$key], $filter);
        }

        return $defaultValue;
    }

    public function hasServer($key) {
        return array_key_exists($key, $this->getParsedRequest()->getServerParams());
    }

    public function getServer($key, $filter = "default", $defaultValue = NULL) {
        if ($this->hasServer($key)) {
            return FilterFactory::doFilter(
                $this->getParsedRequest()->getServerParams()[$key], $filter);
        }

        return $defaultValue;
    }

    public function getUploadedFiles($skipNoFiles = TRUE) {
        $files = [];

        /**
         * @var  $fileKey
         * @var UploadedFile $file
         */
        foreach ($this->getParsedRequest()->getUploadedFiles() as $fileKey => $file) {
            if ($file->getError()) {
                if ($file->getError() == UPLOAD_ERR_NO_FILE && $skipNoFiles) {
                    continue;
                }
            }

            $files[] = new FileUpload([
                'name' => $file->getClientFilename(),
                'data' => $file->getStream()->getContents()
            ], $fileKey);
        }

        return $files;
    }

    public function getUploadedFile(string $name, $skipNoFile = TRUE) {
        $files = self::getUploadedFiles($skipNoFile);

        if (TextUtil::exists($name, '[]')) {
            $result = [];
            $keyword = substr($name, 0, -2);

            foreach ($files as $file) {
                if ($file->getName(TRUE) == $keyword) {
                    $result[] = $file;
                }
            }

            return $result;
        }

        foreach ($files as $file) {
            if ($file->getName() == $name) {
                return $file;
            }
        }

        return NULL;
    }

    /**
     * @return bool
     */
    public function hasFiles() {
        return count($this->getParsedRequest()->getUploadedFiles()) > 0;
    }

    public function isXhr() {
        $serverVar = $this->getServer('HTTP_X_REQUESTED_WITH');

        return strtolower($serverVar) == 'xmlhttprequest';
    }

    protected $values = [];
    public function setValue(string $key, $value) {
        $this->values[$key] = $value;
    }

    public function getValue(string $key, $defaultValue = NULL) {
        return array_key_exists($key, $this->values) ? $this->values[$key] : $defaultValue;
    }

    public function freeRes() {
        $this->values = [];
        $this->multiRes = NULL;
    }
}