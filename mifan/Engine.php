<?php
namespace mifan;

use mifan\core\Dispatcher;
use mifan\core\Loader;
use mifan\core\Response;

class Engine {
    protected $dispatcher;
    protected $loader;
    protected $vars = array();

    public function __construct() {
        $this->dispatcher = new Dispatcher();
        $this->loader = new Loader();
        $this->initialize();
    }

    public function __call($name, $params) {
        $callback = $this->dispatcher->get($name);
        if (is_callable($callback)) {
            return $this->dispatcher->run($name, $params);
        }

        $shared = (!empty($params)) ? (bool)$params[0] : TRUE;
        return $this->loader->load($name, $shared);
    }

    public function handleErrors($enabled = TRUE) {
        if ($enabled) {
            set_error_handler(array($this, "handleError"));
            set_exception_handler(array($this, "handleException"));
        } else {
            restore_error_handler();
            restore_exception_handler();
        }
    }

    public function handleError($errno, $errstr, $errfile, $errline) {
        if ($errno & error_reporting()) {
            throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
        }
    }

    public function handleException(\Exception $e) {
        if ($this->get("mifan.log_errors")) {
            error_log($e->getMessage());
        }
        $this->error($e);
    }

    public function map($name, $callback) {
        if (method_exists($this, $name)) {
            throw new Exception("Cannot override an existing method.");
        }
        $this->dispatcher->set($name, $callback);
    }

    public function register($name, $class, array $params = array(),
        $callback = NULL) {
        if (method_exists($this, $name)) {
            throw new Exception("Cannot override an existing method.");
        }
        $this->loader->register($name, $class, $params, $callback);
    }

    public function set($key, $value = NULL) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->vars[$k] = $v;
            }
        } else {
            $this->vars[$key] = $value;
        }
    }

    public function get($key, $default = NULL) {
        return isset($this->vars[$key]) ? $this->vars[$key] : $default;
    }

    public function has($key) {
        return isset($this->vars[$key]);
    }

    public function clear($key = NULL) {
        if ($key !== NULL) {
            unset($this->vars[$key]);
        }
        else {
            $this->vars = array();
        }
    }

    public function _route($pattern, $callback) {
        $this->router()->map($pattern, $callback);
    }

    public function _render($file, $data=NULL, $key=NULL) {
        if ($key !== NULL) {
            $this->view()->$key = $this->view()->fetch($file, $data);
        }
        else {
            $this->view()->render($file, $data);
        }
    }

    public function _error(\Exception $e) {
        $msg = sprintf("<h1>500 Internal Server Error</h1>".
            "<h3>%s (%s)</h3>".
            "<pre>%s</pre>",
            $e->getMessage(),
            $e->getCode(),
            $e->getTraceAsString()
        );
        try {
            $this->response(FALSE)
                 ->status(500)
                 ->write($msg)
                 ->send();
        }
         catch (\Exception $ex) {
            exit($msg);
        }
    }

    public function _notFound() {
        $this->response(FALSE)
             ->status(404)
             ->write("<h1>404 Not Found</h1>")
             ->send();
    }

    public function _json($data, $code = 200) {
        $this->response(FALSE)
            ->status($code)
            ->header("Content-Type", "application/json")
            ->write(json_encode($data))
            ->send();
    }

    public function _redirect($url, $code=303) {
        $base = $this->request()->base;
        if ($base != "/") {
            $url = "{$base}{$url}";
        }
        $this->response(FALSE)
            ->status($code)
            ->header("Location", $url)
            ->send();
    }

    public function _start() {
        $self = $this;
        $dispatched = FALSE;
        $router = $this->router();
        $request = $this->request();
        $response = $this->response();

        if (ob_get_length() > 0) {
            $response->write(ob_get_clean());
        }

        ob_start();

        $this->handleErrors($this->get("mifan.handle_errors"));

        $this->after("start", function () use ($self) {
            $self->stop();
        });

        while ($route = $router->route($request)) {
            $continue = $this->dispatcher->execute(
                $route->callback,
                $route->params
            );

            $dispatched = TRUE;

            if ($continue != TRUE) break;

            $router->next();
        }

        if ($dispatched === FALSE) {
            $this->notFound();
        }
    }

    public function _stop($code = 200) {
        $this->response()
            ->status($code)
            ->write(ob_get_clean())
            ->send();
    }

    protected function initialize() {
        $self = $this;

        $this->set("mifan.views.path", "./templates");
        $this->set("mifan.log_errors", FALSE);
        $this->set("mifan.handle_errors", TRUE);

        $methods = array(
            "route", "start", "render", "notFound",
            "json", "stop", "error", "redirect",
        );
        foreach ($methods as $name) {
            $this->dispatcher->set($name, array($this, "_{$name}"));
        }

        $this->loader->register("router", '\mifan\net\Router');
        $this->loader->register("request", '\mifan\net\Request');
        $this->loader->register("response", '\mifan\net\Response');
        $this->loader->register("view", '\mifan\template\View', array(), function($view) use ($self) {
            $view->path = $self->get("mifan.views.path");
        });
    }
}