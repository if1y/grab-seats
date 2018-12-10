<?php 
define ("COMMON_PATH", __DIR__);
ini_set("date.timezone","Asia/shanghai");
ini_set('magic_quotes_gpc', 'Off');
require_once "Consts.php";
require_once "config.php";
require_once "Exceptions.php";
require_once "DBFactory.php";
require_once "cache/CacheFactory.php";
require_once "RedisFactory.php";
//面向过程的通用函数相关定义
require_once "common.functions.php";
//初始化相关
RedisFactory::init($CFG['redis']);


function classLazyLoad($className) {
   //目前只自动加载dao里面的
   if (preg_match( "/Entity$/", $className)) {
       $classPath = COMMON_PATH."/dao/".$className.".php";
       require_once $classPath;
   }
}
//注册自动加载类
spl_autoload_register("classLazyLoad");

function exceptionHandler($e) {
    $code = $e->getCode() ? $e->getCode() : -1;
    reponseJson($code, $e->getMessage(), null);
}
//set_exception_handler("exceptionHandler");


//单个ip限速，限制刷接口
$ip = getUserIp();
rateLimter(ip2long($ip), 10);
session_start();
