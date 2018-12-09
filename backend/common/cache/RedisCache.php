<?php
require_once "Cache.php";
class RedisCache implements Cache {

    private $_instance;
    public function __construct () {
        $this->_instance = RedisFactory::getInstance();
    }
    
    public function get($key) {
        return $this->_instance->get($key);
    }
    
    public function set($key, $value, $expire= 30) {
        return $this->_instance->setex($key,$expire ,$value);
    }
    
    public function __call($name, $args) {
        return call_user_func_array(array($this->_instance, $name), $args);
    }
    
    
}