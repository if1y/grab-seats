<?php 
require_once "common/bootstrap.php";
assertLogin();

$data = [
    'account' => $_SESSION['user']['account'],
];
R(0, "ok", $data);