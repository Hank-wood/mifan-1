<?php
namespace mifan\core;

class Dispatcher {
    protected $events = array();

    public function get($name) {
        return isset($this->events[$name]) ? $this->events[$name] : NULL;
    }

    public function set($name, $callback) {
        $this->events[$name] = $callback;
    }

    public function run($name, array &$params) {
        return $this->execute($this->get($name), $params);
    }

    public static function execute($callback, array &$params) {
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }
        else {
            throw new \Exception("Invalid callback specified.");
        }
    }
}
