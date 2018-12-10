<?php
require_once "common/bootstrap.php";
//获取活动信息
$eventId = $_GET['eventId'] ?? "";
//获取已参加人数

if ($eventId == "") {
    R(-1, "活动不存在");
}
$eventId = substr($eventId, 0, 36);
$redis = RedisFactory::getInstance();
$eventKey = "event-detail:$eventId";
$evetFromCache = $redis->get($eventKey); 
if ($evetFromCache) {
    if ($evetFromCache == "NotFound") {
        R(-2, "活动不存在"); //避免查询数据库
    }
    //解析的活动信息
    $eventInfo = json_decode($evetFromCache, true);
} else {
    $eventModel = new EventsEntity();
    $event = $eventModel->get($eventId);
    if ($event->row == false || $event->row['status'] != 1) {
        //不存在的活动
        $redis->setex($eventKey, 30, "NotFound");
        R(-2, "活动不存在"); 
    }
    $row = $event->row;
    $eventInfo = [
        'id' => $row['id'],
        'name' => $row['name'],
        'stage_id' => $row['stage_id'],
        'seats_per_person' => $row['seats_per_person'],
        'start_time' => date('Y-m-d H:i:s', $row['start_time']),
        'end_time' => date('Y-m-d H:i:s', $row['end_time']),
    ];
    
    //查询会场信息
    $stageEntity = new StageEntity();
    $stageSeatsEntity = new StageSeatsEntity();
    $stageInfo = $stageEntity->get($row['stage_id']);
    $eventInfo['stage_info'] = $stageInfo->row;
    //查询座位情况
    $seatsInfo = $stageSeatsEntity->getStageSeats($row['stage_id']);
    
    $eventInfo['seats'] = $seatsInfo;
    $redis->setex($eventKey, 60, json_encode($eventInfo, JSON_UNESCAPED_UNICODE));
}
$seatsInfo = $eventInfo['seats'];

//查询还有多少座位剩下
$eventSeatsQueueKey = "queue:$eventId";

//不在前端显示这些座位情况
unset($eventInfo['seats']);

$eventInfo['seats_left'] = $redis->llen($eventSeatsQueueKey);
R(0, "ok", $eventInfo);