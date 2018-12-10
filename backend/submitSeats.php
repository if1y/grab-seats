<?php 
require_once "common/bootstrap.php";
$user = assertLogin();
$eventId = $_POST['eventId'] ?? "";
$eventInfo = assertEvent($eventId);

//�û���Ҫ����λ��
$seatsUserWanted = $_POST['num'] ?? 0;

//�ӿڷ�ˢ������ÿ��ֻ��2000������Ȼ��ȴ�2��
/**
 *  $eventRateLimterKey = "g:e:limit:$eventId";
 *  rateLimter($eventRateLimterKey, 2000, 2);
 **/
if ($eventInfo['seat_per_person'] < $seatsUserWanted) {
    R(402, "ÿ�����ֻ�ܹ���{$eventInfo['seat_per_person']}��Ʊ", $eventInfo['seat_per_person']);
}

$redis = RedisFactory::getInstance();
$eventSeatsQueueKey = "queue:$eventId";
$cnt = $redis->llen($eventSeatsQueueKey);
if ($cnt < 1 || $cnt < $seatsUserWanted) {
    R(402, "û�и�����λ��", $cnt);
}
/**
  1) ʹ��redis ���� ������λ�� [actiity=>������λ���]
     pop 1-5 ��,���ʧ�ܣ�������push
**/
//���Է���,�ȷ��䣬����������
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
 ֻ����һ����
if (count($seats) < $seatsUserWanted) {
    //��Ҫ��,����ض��� 
}
**/

//���mysql�������ռ��
$backToQueue = []; //��Ҫ���ض��е�Ʊ
$userSeatsEntity = new UserSeatsEntity();
$userSeatCnt = $userSeatsEntity->getUserSeatCnt($eventId, $user['account']);
$allowedSeatsCnt = count($seats); //�����Ʊ��
if ($userSeatCnt + $allowedSeatsCnt > $eventInfo['seat_per_person']) {
    //�����û�������,�»�һЩƱ
    $seatsCanGet = $eventInfo['seat_per_person'] - $allowedSeatsCnt;
    for ($i = $seatsCanGet -1; $i <  $allowedSeatsCnt; $i++) {
        $backToQueue[] = array_pop($seats);
    }
}

//������ݿ⣬�Ƿ��Ѿ�������������λ�������ϲ���
//�����ЩƱ�Ѿ������ݿ��б������ˣ��ⲿ�ֹ��˵�
$allotedSeats = $userSeatsEntity->getAllotedSeats($eventId, $seats);
if ($allotedSeats) {
    //��ĳ����쳣����λ�Ѿ�����
    $seats = array_diff($seats, $allotedSeats);
}


