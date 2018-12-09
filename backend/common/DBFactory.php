<?php 
require_once "config.php";
require_once "DBHandler.php";

class DBFactory {

    static $dbHanlders = [];
    public static function getWriteDb() {
        global $CFG;
        return self::getHanlder($CFG['db']) ;
    }
    
    
    public static function getReadDb() {
        global $CFG;
        return self::getHanlder($CFG['db']) ;
    }
    
    private static function getHanlder($cfg) {
        //相同配置的数据库只new一个实例
        $key = self::getCfgKey($cfg);
        if (isset($dbHanlders[$key])) {
            return $dbHanlders[$key];
        }
        $dbHanlders[$key] = new DBHanlder($cfg);
        return $dbHanlders[$key];
    }
    
    public static function getCfgKey($cfg) {
        $key = "default";
        if (is_string($cfg)) {
            $key .= $cfg;
        }
        foreach ($cfg as $k => $v) {
            $key .= $k ."=".$v;
        }
        return md5($key);
        
    }
    
};