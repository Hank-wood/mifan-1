<?php
namespace mifan\util;

class Collection implements \ArrayAccess, \Iterator, \Countable {
    protected $container = array();

    public function __construct(array $data = array()) {
        $this->container = $data;
    }

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? 
            $this->container[$offset] :
            NULL;
    }

    public function offsetSet($offset, $value) {
        $this->container[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }

    public function current() {
        return current($this->container);
    }

    public function key() {
        return key($this->container);
    }

    public function next() {
        return next($this->container);
    }

    public function rewind() {
        reset($this->container);
    }

    public function valid() {
        $key = $this->key();
        return ($key !== FALSE) && ($key !== NULL);
    }

    public function count() {
        return count($this->container);
    }

    public function setData($data) {
        $this->container = $data;
    }

    public function __get($key) {
        return $this->offsetGet($key);
    }

    public function __set($key, $value) {
        $this->offsetSet($key, $value);
    }

    public function __isset($key) {
        return $this->offsetExists($key);
    }

    public function __unset($key) {
        $this->offsetUnset($key);
    }
}
