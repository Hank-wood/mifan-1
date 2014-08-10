<?php
namespace mifan\net;

class Route {
    public $pattern;
    public $callback;
    public $methods = array();
    public $params = array();

    public function __construct($pattern, $callback, $methods) {
        $this->pattern = $pattern;
        $this->callback = $callback;
        $this->methods = $methods;
    }

    public function matchMethod($method) {
        return count(array_intersect(array($method, "*"), $this->methods)) > 0;
    }

    public function matchUrl($url) {
        if ($this->pattern === '*' || $this->pattern === $url) {
            return TRUE;
        }

        $ids = array();
        $char = substr($this->pattern, -1);

        $this->splat = substr($url, strpos($this->pattern, '*'));
        $this->pattern = str_replace(array(')', '*'), array(')?', '.*?'), 
            $this->pattern
        );

        $regex = preg_replace_callback(
            '#@([\w]+)(:([^/\(\)]*))?#',
            function ($matches) use (&$ids) {
                $ids[$matches[1]] = NULL;
                if (isset($matches[3])) {
                    return "(?P<{$matches[1]}>{$matches[3]})";
                }
                return "(?P<{$matches[1]}>[^/\?]+)";
            },
            $this->pattern
        );

        if ($char === "/") {
            $regex .= "?";
        }
        else {
            $regex .= "/?";
        }

        if (preg_match('#^'.$regex.'(?:\?.*)?$#i', $url, $matches)) {
            foreach ($ids as $k => $v) {
                $this->params[$k] = (array_key_exists($k, $matches)) ?
                    urldecode($matches[$k]) :
                    NULL;
            }
            return TRUE;
        }
        return FALSE;
    }
}