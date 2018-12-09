<?php
require_once "Cache.php";
//���湤��
class CacheFactory {
    
    public static function getCache() {
        global $CFG;
        static $cache;
        if ($cache) {
            return $cache;
        }
        switch ($CFG['cache']['type']) {
           case 'redis' : 
                 require_once "RedisCache.php";
                 $cache = new RedisCache($CFG['redis']);
            break;
           default : throw new CacheImplementsNotFound(1000, "cache not found");
        }
        return $cache;
    
    }

}
