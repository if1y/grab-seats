<?php 
require_once "common/bootstrap.php";

checkAuthCode();
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
    'is_admin' => 0,
    'password' => passCrypt(encodePassword($_POST['password']), $salt),
    'salt' => $salt,
];
$ret = $user->save();

if ($ret !== false) {
    R(0, "注册成功 :)");
} else {
    R(500, "系统异常 :(");
}

 