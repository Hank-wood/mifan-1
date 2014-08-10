<?php
namespace mifan\core;

class Loader {
    protected static $dirs = array();
    protected $classes = array();
    protected $instances = array();

    public function register($name, $class, array $params = array(), 
        $callback = NULL) {
        unset($this->instances[$name]);
        $this->classes[$name] = array($class, $params, $callback);
    }

    public static function autoload($enabled = TRUE, $dirs = array()) {
        if ($enabled) {
            spl_autoload_register(array(__CLASS__, "loadClass"));
        }
        else {
            spl_autoload_unregister(array(__CLASS__, "loadClass"));
        }

        self::addDir($dirs);
    }

    public static function loadClass($class) {
        $class_file = str_replace("\\", "/", $class);

        foreach (self::$dirs as $dir) {
            $file = "{$dir}/{$class_file}.php";
            if (file_exists($file)) {
                require $file;
            }
        }
    }

    public static function addDir($dir) {
        if (is_array($dir)) {
            foreach ($dir as $value) {
                self::addDir($value);
            }
        }
        else if (is_string($dir)) {
            if (!in_array($dir, self::$dirs)) self::$dirs[] = $dir;
        }
    }

    public function load($name, $shared = TRUE) {
        $instance = NULL;

        if (isset($this->classes[$name])) {

            list($class, $params, $callback) = $this->classes[$name];

            $exists = isset($this->instances[$name]);

            if ($shared) {
                $instance = $exists ? 
                    $this->getInstance($name):
                    $this->newInstance($class, $params);

                if (!$exists) {
                    $this->instances[$name] = $instance;
                }
            }
            else {
                $instance = $this->newInstance($class, $params);
            }

            if (is_callable($callback) && (!$shared || !$exists)) {
                call_user_func_array($callback, array($instance));
            }
        }
        return $instance;
    }

    public function getInstance($name) {
        return isset($this->instances[$name]) ? $this->instances[$name] : NULL;
    }

    public function newInstance($class, $params) {
        $refClass = new \ReflectionClass($class);
        return $refClass->newInstanceArgs($params);
    }
}