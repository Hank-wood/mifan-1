<?php
namespace mifan\net;

class Response {
    protected $status = 200;
    protected $headers = array();
    protected $body = "";
    public static $codes = array(
        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        203 => "Non-Authoritative Information",
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content",

        300 => "Multiple Choices",
        301 => "Moved Permanently",
        302 => "Found",
        303 => "See Other",
        304 => "Not Modified",
        305 => "Use Proxy",
        307 => "Temporary Redirect",

        400 => "Bad Request",
        401 => "Unauthorized",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        407 => "Proxy Authentication Required",
        408 => "Request Timeout",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        412 => "Precondition Failed",
        413 => "Request Entity Too Large",
        414 => "Request-URI Too Long",
        415 => "Unsupported Media Type",
        416 => "Requested Range Not Satisfiable",
        417 => "Expectation Failed",

        500 => "Internal Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Service Unavailable",
        504 => "Gateway Timeout",
        505 => "HTTP Version Not Supported"
    );

    public function status($code) {
        if (array_key_exists($code, self::$codes)) {
            $this->status = $code;
        }
        else {
            throw new \Exception("Invalid status code.");
        }

        return $this;
    }

    public function header($name, $value = NULL) {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->headers[$k] = $v;
            }
        }
        else {
            $this->headers[$name] = $value;
        }

        return $this;
    }

    public function write($str) {
        $this->body .= $str;
        return $this;
    }

    public function sendHeaders() {
        header(
            sprintf(
                'Status: %d %s',
                $this->status,
                self::$codes[$this->status]
            ),
            TRUE
        );

        foreach ($this->headers as $field => $value) {
            header("{$field}: {$value}");
        }

        return $this;
    }

    public function send() {
        if (ob_get_length() > 0) {
            ob_end_clean();
        }

        if (!headers_sent()) {
            $this->sendHeaders();
        }

        exit($this->body);
    }
}