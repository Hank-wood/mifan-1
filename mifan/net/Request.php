<?php
namespace mifan\net;

use mifan\util\Collection;

class Request {
    public $url;
    public $base;
    public $method;
    public $referrer;
    public $ip;
    public $ajax;
    public $scheme;
    public $user_agent;
    public $type;
    public $length;
    public $query;
    public $data;
    public $cookie;
    public $session;
    public $files;
    public $secure;
    public $accept;

    public function __construct(array $config = array()) {
        if (empty($config)) {
            $config = array(
                "url" => self::getVar("REQUEST_URI", "/"),
                "base" => dirname(self::getVar("SCRIPT_NAME")),
                "method" => self::getMethod(),
                "referrer" => self::getVar("HTTP_REFERER"),
                "ip" => self::getVar("REMOTE_ADDR"),
                "ajax" => self::getVar("HTTP_X_REQUESTED_WITH") == "XMLHttpRequest",
                "scheme" => self::getVar("SERVER_PROTOCOL", "HTTP/1.1"),
                "user_agent" => self::getVar("HTTP_USER_AGENT"),
                "type" => self::getVar("CONTENT_TYPE"),
                "length" => self::getVar("CONTENT_LENGTH", 0),
                "query" => new Collection($_GET),
                "data" => new Collection($_POST),
                "cookie" => new Collection($_COOKIE),
                "session" => isset($_SESSION) ? new Collection($_SESSION) : new Collection(),
                "files" => new Collection($_FILES),
                "secure" => self::getVar("HTTPS", "off") != "off",
                "accept" => self::getVar("HTTP_ACCEPT"),
            );
        }

        $this->init($config);
    }

    public function init($properties = array()) {
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }

        if (strpos($this->type, "application/json") === 0) {
            $body = $this->getBody();
            if ($body != "") {
                $data = json_decode($body, TRUE);
                if ($data != NULL) {
                    $this->data->setData($data);
                }
            }
        }
    }

    public static function getBody() {
        $method = self::getMethod();
        if ($method == "POST" || $method == "PUT") {
            return file_get_contents("php://input");
        }
        return "";
    }

    public static function getMethod() {
        if (isset($_SERVER["HTTP_X_HTTP_METHOD_OVERRIDE"])) {
            return $_SERVER["HTTP_X_HTTP_METHOD_OVERRIDE"];
        }
        else {
            return self::getVar("REQUEST_METHOD", "GET");
        }
    }

    public static function getVar($var, $default = "") {
        return isset($_SERVER[$var]) ? $_SERVER[$var] : $default;
    }
}
