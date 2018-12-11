<?php 
require_once "common/bootstrap.php";
$user = assertLogin();
//$user = ['account' => $_POST['account']];
$eventId = $_POST['eventId'] ?? "";

$eventInfo = assertEvent($eventId);

//用户想要的座位数
$seatsUserWanted = $_POST['num'] ?? 0;

//接口防刷，控制每秒只有2000次请求，然后等待2秒
/**
 *  $eventRateLimterKey = "g:e:limit:$eventId";
 *  rateLimter($eventRateLimterKey, 2000, 2);
 **/
if (0 ==$seatsUserWanted || $eventInfo['seats_per_person'] < $seatsUserWanted) {
    R(402, "每人只能购买1-{$eventInfo['seats_per_person']}张票", $eventInfo['seats_per_person']);
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
//尝试分配
$seats = [];
for ($i=1; $i <= $seatsUserWanted; $i++) {
    $seat = $redis->lpop($eventSeatsQueueKey);
    if ($seat) {
        $seats[] = $seat;
    } else {
        break;
    }
}
if (!$seats) {
    R(402, "没有更多座位了", $cnt);
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
if ($userSeatCnt['cnt'] >= $eventInfo['seats_per_person']) {
    //用户已经抢到足够多的票了
    $backToQueue = $seats;
    $seats = [];
} else if ($userSeatCnt['cnt'] + $allowedSeatsCnt > $eventInfo['seats_per_person']) {
    //个人用户超发了,吐回一些票
    $seatsCanGet = $eventInfo['seats_per_person'] - $userSeatCnt['cnt'];
    for ($i = $seatsCanGet; $i <  $allowedSeatsCnt; $i++) {
        $backToQueue[] = array_pop($seats);
    }
}

//插入数据库
$dbh = DBFactory::getWriteDb();
$seatGets = 0;
foreach ($seats as $one) {
    try {
        $userSeat = new UserSeatsEntity();
        $userSeat->row = [
            "id" => genUUID(),
            'account' => $user['account'],
            'seat_info' => $one,
            'event_id' => $eventId,
        ];
        //保存用户得到的票到数据库
        $userSeat->save();
        $seatGets ++;
    } catch (DBException $e) {
         if ("23000" == $e->getDbErrorCode()) {
            //重复键，说明座位已经分配过，不处理
            
         } else {
            //其他数据库异常，座位重新插入队列
            $backToQueue[] = $one;
            
         }
    }
}
//多余的票或失败的插回队列
foreach ($backToQueue as $one){
    $redis->rpush($eventSeatsQueueKey, $one);
}

if (!$seats) {
    //没有分配到票
    R(0, "每人最多购买{$eventInfo['seats_per_person']}张票", $eventInfo['seats_per_person']);
}

$userSeartKey = "us:$eventId:{$user['account']}";
$redis->del($userSeartKey); //删除用户座位缓存

R(0, "一共抢到{$seatGets}张票", $seatGets);
