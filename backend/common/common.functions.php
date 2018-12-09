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
    reponseJson($code, $message, $data = null);
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