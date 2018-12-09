<?php 
require_once "common/bootstrap.php";

$authcode = isset($_POST['authcode']) ? $_POST['authcode'] : -1;
$sessionCode = isset($_SESSION['authcode']) ? $_SESSION['authcode'] : null;

if ($sessionCode) {
    //验证码只用一次
    unset($_SESSION['authcode']);
}
if ($sessionCode != $authcode) {
    
    R(41, "请输入正确验证码");
}
//检查用户
if (!isset($_POST['account']) || !isset($_POST['password'])) {
    R(42, "请检查参数");
}
$pattern = "/^[a-zA-Z][a-zA-Z0-9]{3,48}$/";
if (!preg_match($pattern, $_POST['account'])) {
    R(42, "名称不合法");
}
$account = trim($_POST['account']);
$_POST['password'] = trim($_POST['password']);
if (strlen($_POST['password']) < 6 || strlen($_POST['password']) > 48) {
    R(42, "密码非法");
}

//检查用户是否存在
$dboHanlder = DBFactory::getReadDb();
$userDao = new UsersEntity();
$user = $userDao->getByAccount($_POST['account']);
if ($user->row) {
    //数据库中已经有这个用户
    R(502, "帐号已存在");
} 
$user->isNewRecord = true;
$salt = genSalt(16);
$user->row = [
    'id' => genUUID(),
    'account' => $_POST['account'],
    'password' => passCrypt(encodePassword($_POST['password']), $salt),
    'salt' => $salt,
];
$ret = $user->save();

if ($ret !== false) {
    R(0, "注册成功 :)");
} else {
    R(500, "系统异常 :(");
}

 