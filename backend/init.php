<?php 
die('');
require_once "common/bootstrap.php";
//初始化
$user = new UsersEntity();
$stage = new StageEntity();
$event = new EventsEntity();

$salt = genSalt(16);
$user->row = [
    'id' => genUUID(),
    'account' => 'tencent',
    'password' => passCrypt(encodePassword('12345'), $salt),
    'salt' => $salt,
];
$user->save();
$stageId = genUUID();
$stage->row = [
    'id' => $stageId,
    'name' => '深圳市体育馆',
    'status' => 1,
];
$stage->save();

$eventId = genUUID();
$event->row = [
    'id' => $eventId,
    'stage_id'=> $stageId,
    'name' => '腾讯公司年会',
    'status' => 1,
    'seats_per_person' => 5,
    'start_time' => strtotime('2019-01-02 19:30:00'),
    'end_time' => strtotime('2019-01-02 23:30:00'),
];
$event->save();
//产生座位
$groups = ['A', 'B', 'C'];
$seatsInfo = [];
foreach ($groups as $k => $v) {
    $rowid = 0;
    for ($i = 50; $i <= 100; $i += 2) {
        $rowid++;
        $stageSeat = new StageSeatsEntity();
        $stageSeat->row = [
            'id' => genUUID(),
            'stage_id' => $stageId,
            'group_tag' => $v,
            'row_idx' => $rowid,
            'col_numbs' => $i,
        ];
        $seatsInfo[] = $stageSeat->row;
        $stageSeat->save();
    }
}
//初始化,将活动的座位录入队列中
$eventSeatsQueueKey = "queue:$eventId";
if (!$redis->exists($eventSeatsQueueKey)) {
    //初始化抢座队列
    $redis->multi();
    foreach ($seatsInfo as $oneRow){
        for ($i = 1; $i <= $oneRow['col_numbs']; $i++) {
            $oneSeat = $oneRow['group_tag'].":"
                     . $oneRow['row_idx'] . ":" .$i;
            $redis->rpush($eventSeatsQueueKey, $oneSeat);
        };
    }
    $redis->exec();
}

echo "ok";
