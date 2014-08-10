<?php
namespace mifan\net;

class Router {
    protected $routes = array();
    protected $index = 0;

    public function map($pattern, $callback) {
        $methods = array("*");
        $url = $pattern;

        if (strpos($pattern, " ") !== FALSE) {
            list($method, $url) = explode(" ", $pattern, 2);
            $methods = explode("|", $method);
        }

        $this->routes[] = new Route($url, $callback, $methods);
    }

    public function route(Request $request) {
        while ($route = $this->current()) {
            if ($route->matchMethod($request->method) && 
                $route->matchUrl($request->url)) {
                return $route;
            }
            $this->next();
        }
        return NULL;
    }

    public function current() {
        return isset($this->routes[$this->index]) ? 
            $this->routes[$this->index] : 
            NULL;
    }

    public function next() {
        $this->index++;
    }
}