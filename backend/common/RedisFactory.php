<?php 

class RedisFactory {
    
    static $cfg;
    static function init($cfg) {
        self::$cfg = $cfg;
    }
    static function getInstance() {
        
        static $instance;
        if ($instance) 
            return $instance;
        $cfg = self::$cfg;
        
        if (!$cfg) {
            //没有初始化
            throw new  RedisException(102, "please RedisFactory::init first");
        }
        $instance = new Redis();
        $ret = $instance->connect($cfg['host'], $cfg['port'], $cfg['timeout']);
         if ($ret == false) {
            throw new RedisException(101, "Could not connect to redis");
         }
         if ($cfg['auth']) {
            $instance->auth($cfg['auth']);
         }
         
         return $instance;
    }
}