<?php
require_once "common/bootstrap.php";
//��ȡ��б�
//ֻȡ100��¼
$redisKey = "act:list";
$redis = RedisFactory::getInstance();
if ($str = $redis->get($redisKey)) {
    $arrStr = json_decode($str, true);
    R(0, 'ok', $arrStr);
}

//��ѯ��б�
$dbh = DBFactory::getReadDb();
