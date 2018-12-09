<?php 

// 返回接口内容
function reponseJson($code, $message, $data = null) {
    $arr = [
        'code' => $code,
        'message' => $message,
        'data' => $data,
    ];
    header('Content-Type:application/json; charset=utf-8');
    exit(json_encode($arr));
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
 * 接口限速，统一ip，每秒只能N次请求
 **/
function rateLimter($ip , $times = 20) {
    $redis = RedisFactory::getInstance();
    $key = "Limiter:$ip";
    $expire = 1; //1秒
    $cnt = $redis->incr($key);
    if ($cnt > $times) {
        $redis->expire($key, 2); //暂停2秒
        reponseJson(-1,"请求太频繁了,2秒后重试");
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