<?php
require_once "common/bootstrap.php";
//获取活动列表
//只取100记录
$redisKey = "act:list";
$redis = RedisFactory::getInstance();
if ($str = $redis->get($redisKey)) {
    $arrStr = json_decode($str, true);
    R(0, 'ok', $arrStr);
}

//查询活动列表
$dbh = DBFactory::getReadDb();
