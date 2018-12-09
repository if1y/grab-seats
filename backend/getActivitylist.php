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
//fetchList
$EventObj = new EventsEntity();
$list = $EventObj->fetchList("status=1 limit 100", null);
$data = [];
foreach ($list as $one) {
    $data[] = [
        'id' => $one['id'],
        'name' => $one['name'],
        'start_time' => date('Y-m-d H:i:s', $one['start_time']),
        'end_time' => date('Y-m-d H:i:s', $one['end_time']),
    ];
}
R(0, "ok", $data);
