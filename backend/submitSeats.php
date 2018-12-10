<?php 
require_once "common/bootstrap.php";
$user = assertLogin();
$eventId = $_POST['eventId'] ?? "";
$eventInfo = assertEvent($eventId);

//用户想要的座位数
$seatsUserWanted = $_POST['num'] ?? 0;

//接口防刷，控制每秒只有2000次请求，然后等待2秒
/**
 *  $eventRateLimterKey = "g:e:limit:$eventId";
 *  rateLimter($eventRateLimterKey, 2000, 2);
 **/
if ($eventInfo['seat_per_person'] < $seatsUserWanted) {
    R(402, "每人最多只能购买{$eventInfo['seat_per_person']}张票", $eventInfo['seat_per_person']);
}

$redis = RedisFactory::getInstance();
$eventSeatsQueueKey = "queue:$eventId";
$cnt = $redis->llen($eventSeatsQueueKey);
if ($cnt < 1 || $cnt < $seatsUserWanted) {
    R(402, "没有更多座位了", $cnt);
}
/**
  1) 使用redis 队列 分配座位数 [actiity=>具体座位编号]
     pop 1-5 张,如果失败，则重新push
**/
//尝试分配,先分配，起到削峰作用
$seats = [];
for ($i=1; $i <= $seatsUserWanted; $i++) {
    $seat = $redis->lpop($eventSeatsQueueKey);
    if ($seat) {
        $seats[] = $seat;
    } else {
        break;
    }
}
/**
 只抢到一部分
if (count($seats) < $seatsUserWanted) {
    //不要了,插入回队列 
}
**/

//落地mysql，做最终检查
$backToQueue = []; //需要返回队列的票
$userSeatsEntity = new UserSeatsEntity();
$userSeatCnt = $userSeatsEntity->getUserSeatCnt($eventId, $user['account']);
$allowedSeatsCnt = count($seats); //分配的票数
if ($userSeatCnt + $allowedSeatsCnt > $eventInfo['seat_per_person']) {
    //个人用户超发了,吐回一些票
    $seatsCanGet = $eventInfo['seat_per_person'] - $allowedSeatsCnt;
    for ($i = $seatsCanGet -1; $i <  $allowedSeatsCnt; $i++) {
        $backToQueue[] = array_pop($seats);
    }
}

//检查数据库，是否已经有人抢到该座位，理论上不会
//检查哪些票已经在数据库中被分配了，这部分过滤掉
$allotedSeats = $userSeatsEntity->getAllotedSeats($eventId, $seats);
if ($allotedSeats) {
    //真的出现异常，座位已经被用
    $seats = array_diff($seats, $allotedSeats);
}


