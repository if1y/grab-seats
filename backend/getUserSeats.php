<?php 
require_once "common/bootstrap.php";
$user = assertLogin();
$eventId = $_GET['eventId'] ?? "";
$eventInfo = assertEvent($eventId);
$userSeartKey = "us:$eventId:{$user['account']}";
$redis = RedisFactory::getInstance();
$cacheInfo = $redis->get($userSeartKey);
// 可以使用本地缓存，减少后端压力
if ($cacheInfo) {
    $searts = json_decode($cacheInfo, true);
} else {
    $uSeatModel = new UserSeatsEntity();
    $searts = $uSeatModel->getSeatsByUser($eventId, $user['account']);
    $redis->setex($userSeartKey, 30, json_encode($searts));
}


R(0, "ok", $searts);