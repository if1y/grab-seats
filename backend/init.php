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
//长生作为
$groups = ['A', 'B', 'C'];
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
        $stageSeat->save();
    }
}

echo "ok";
