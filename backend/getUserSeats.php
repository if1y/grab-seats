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
    $data = json_decode($cacheInfo, true);
} else {
    $uSeatModel = new UserSeatsEntity();
    $searts = $uSeatModel->getSeatsByUser($eventId, $user['account']);
    $seatsLeft = $eventInfo['seats_per_person'] - count($searts);
    $seatsLeft = $seatsLeft > 0 ? $seatsLeft : 0; 
    $data = [
        'list' => $searts,
        'seats_left' => $seatsLeft, //还能订购多少张
    ];
    $redis->setex($userSeartKey, 30, json_encode($data));
}


R(0, "ok", $data);