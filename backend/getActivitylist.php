<?php
require_once "common/bootstrap.php";
//��ȡ��б�
//ֻȡ100��¼
$redisKey = "act:list";
$redis = RedisFactory::getInstance();
if ($str = $redis->get($redisKey)) {

    //�ӻ����ȡ
    $arrStr = json_decode($str, true);
    R(0, 'ok', $arrStr);
}

//��ѯ��б�
//fetchList
$EventObj = new EventsEntity();
$StageObj = new StageEntity();
$list = $EventObj->fetchList("status=1 limit 100", null);
$data = [];
$stageIds = [];
foreach ($list as $one) {
    $data[] = [
        'id' => $one['id'],
        'name' => $one['name'],
        'stage_id' => $one['stage_id'],
        'seats_per_person' => $one['seats_per_person'],
        'start_time' => date('Y-m-d H:i:s', $one['start_time']),
        'end_time' => date('Y-m-d H:i:s', $one['end_time']),
    ];
    $stageIds[] = $one['stage_id'];
}
//��ѯ��̨��Ϣ
$qdata = array_unique($stageIds);
$s = array_fill(0,count($qdata), "?");
$sql = "id in(".implode(',', $s).")";
$list = $StageObj->fetchList($sql, $qdata);
$map = [];
foreach ($list as $one ) {
    $map[$one['id']] = $one;
}
foreach ($data as &$v) {
    $v['stage_name'] = $map[$v['stage_id']] ? $map[$v['stage_id']]['name'] : "";
}
//д�뻺��
$redis->setex($redisKey, 20, json_encode($data));

R(0, "ok", $data);
