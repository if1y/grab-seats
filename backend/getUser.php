<?php 
require_once "common/bootstrap.php";
assertLogin();

$data = [
    'account' => $_SESSION['account'],
];

R(0, "ok", $data);