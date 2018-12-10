<?php 

// 返回接口内容
function reponseJson($code, $message, $data = null) {
    $arr = [
        'code' => $code,
        'message' => $message,
        'data' => $data,
    ];
    
    header('Content-Type:application/json; charset=utf-8');
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit();
}

function R($code, $message, $data = null) {
    reponseJson($code, $message, $data);
}

function isLogin() {
    return isset($_SESSION['user']);
}



//产生随机的salt
function genSalt($length = 16) {
    $strs="QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm";
    $newStr = str_shuffle($strs);
    $mStr = "";
    $strLen = strlen($strs) - 1;
    for ($i=0; $i < $length; $i++) {
        $idx = mt_rand(0, $strLen);
        $mStr .= $newStr{$idx};
    }
    return $mStr;
}
function encodePassword($password) {
    return md5($password);
}
function passCrypt ($md5Pwd, $salt) {
    return md5($md5Pwd.$salt);
}

function assertLogin() {
    if (!isLogin()) {
        reponseJson(32, "请先登录", time());
    }
    return $_SESSION['user'];
}

//生成唯一id，这里做了简化处理
function genUUID() {
    $time = time(); //10位
    $redis = RedisFactory::getInstance();
    $counterKey = "counter:global";
    $nId = $redis->incr($counterKey);
    return "U". ($time + $nId);
}

function getParamsKey($params) {
    
    $key = "default";
    
    if (is_array($params)) {
        foreach ($params as $k => $v) {
            $key .= "${k}={$v}";
        }
    } else {
        $key .= $params;
    }
    
    return md5($key);
}

/**
 * 接口限速，每秒只能N次请求
 **/
function rateLimter($ip , $times = 20, $timewait = 2) {
    $redis = RedisFactory::getInstance();
    $key = "Limiter:$ip";
    $expire = 1; //1秒
    $cnt = $redis->incr($key);
    if ($cnt >= $times) {
        
        if ($cnt == $times && $timewait) {
            //刚好到达阈值，设置等待，之后才允许请求
            $redis->expire($key, $timewait); //暂停2秒
        }
        reponseJson(-1,"请求太频繁了,请稍重试");
    }
    if ($cnt == 1) {
        $redis->expire($key, 1);
    }
    
    return true;
    
}

function getUserIp() {
    if (getenv("HTTP_X_FORWARDED_FOR")){
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    }
    elseif (getenv("HTTP_CLIENT_IP")){
        $ip = getenv("HTTP_CLIENT_IP");
    }
    elseif (getenv("REMOTE_ADDR")){
        $ip = getenv("REMOTE_ADDR");
    } else {
       $ip = false;
    }
    
    return $ip;
}

/**
 * 检查验证码 
 **/
function checkAuthCode() {
    $authcode = isset($_POST['authcode']) ? $_POST['authcode'] : -1;
    $sessionCode = isset($_SESSION['authcode']) ? $_SESSION['authcode'] : null;

    if ($sessionCode) {
        //你要重新刷新验证码了
        unset($_SESSION['authcode']);
    }
    if ($sessionCode != $authcode) {
        
        R(41, "请输入正确验证码");
    }
}

function assertEvent($eventId) {
    //获取已参加人数
    if ($eventId == "") {
        R(-1, "活动不存在");
    }
    $eventId = substr($eventId, 0, 36);
    $redis = RedisFactory::getInstance();
    $eventKey = "event:$eventId";
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
            'start_time' =>  $row['start_time'],
            'end_time' =>  $row['end_time'],
        ];
    }
    return $eventInfo;
}