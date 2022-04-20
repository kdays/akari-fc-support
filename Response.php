<?php
namespace AkariFC;

use Akari\system\http\HttpCode;
use Akari\system\ioc\Injectable;
use RingCentral\Psr7\Response as FcResponse;

class Response extends Injectable implements ICanFree {

    private $isSent = FALSE;
    private $responseCode = HttpCode::OK;

    private $headers = [];
    private $content;

    public function setStatusCode($code = HttpCode::OK, $msg = NULL) {
        $this->responseCode = $code;

        if ($code == HttpCode::UNAVAILABLE_FOR_LEGAL_REASON && $msg == NULL) {
            $msg = HttpCode::$statusCode[HttpCode::UNAVAILABLE_FOR_LEGAL_REASON];
        }
        $this->responseCodeMessage = $msg;

        return $this;
    }

    public function getStatusCode() {
        return $this->responseCode;
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function setJsonContent($content) {
        $this->content = json_encode($content);
    }

    public function getContent() {
        return $this->content;
    }

    public function appendContent($content) {
        $this->content .= $content;
    }

    public function useNoCache() {
        $this->setHeader('Pragma', 'no-cache');
        $this->setHeader('Cache-Control', 'no-cache');

        return $this;
    }

    public function setCacheTime($time) {
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }

        $this->setHeader("Cache-Control", "max-age=" . $time);

        return $this;
    }

    public function setHeader($key, $value) {
        $this->headers[$key] = $value;

        return $this;
    }

    public function resetHeaders() {
        $this->headers = [];
    }

    public function setHeaders($headers) {
        $this->headers += $headers;

        return $this;
    }

    public function setContentType($contentType = 'text/html') {
        $this->setHeader('Content-Type', $contentType);

        return $this;
    }

    public function redirect($location, $statusCode = HttpCode::FOUND) {
        $this->setStatusCode($statusCode);
        $this->setHeader("location", $location);
    }

    public function toFcResponse() {
        $setCookies = [];
        foreach ($this->cookie->getHeaders() as $header) {
            $setCookies[] = $header;
        }

        if (!empty($setCookies)) {
            $this->setHeader('set-cookie', $setCookies);
        }

        return new FcResponse(
            $this->getStatusCode(),
            $this->headers,
            $this->getContent()
        );
    }

    public function isSent() {
        return $this->isSent;
    }

    public function freeRes() {
        $this->resetHeaders();
        // TODO: Implement freeRes() method.
    }
}