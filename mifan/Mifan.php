<?php
require "core/Loader.php";

\mifan\core\Loader::autoload(TRUE, dirname(__DIR__));

class Mifan {
    static $engine;

    private function __construct() {}
    private function __destruct() {}
    private function __clone() {}

    public static function __callStatic($name, $params) {
        static $initialized = FALSE;

        if ($initialized === FALSE) {
            self::$engine = new \mifan\Engine();
            $initialized  = TRUE;
        }

        return \mifan\core\Dispatcher::execute(array(self::$engine, $name), 
            $params
        );
    }
}