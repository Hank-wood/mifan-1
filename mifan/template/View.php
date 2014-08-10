<?php
namespace mifan\template;

class View {
    public $path;
    protected $vars = array();

    public function __construct($path = ".") {
        $this->path  = $path ;
    }

    public function render($file, $data = NULL) {
        $file = $this->getTemplate($file);

        if (!file_exists($file)) {
            throw new \Exception("Template file not found: {$file}.");
        }

        if (is_array($data)) {
            $this->vars = array_merge($this->vars, $data);
        }

        extract($this->vars);
        include $file;
    }

    public function fetch($file, $data) {
        ob_start();
        $this->render($file, $data);
        return ob_get_clean();
    }

    public function getTemplate($file) {
        if (substr($file, -4) != ".php") {
            $file .= ".php";
        }
        return "{$this->path}/{$file}";
    }

    public function set($key, $value) {
        $this->vars[$key] = $value;
    }

    public function get($key) {
        return isset($this->vars[$key])  ? $this->vars[$key] : NULL;
    }
}