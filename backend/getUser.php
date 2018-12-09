<?php 
require_once "common/bootstrap.php";
accertLogin();

$data = [
    'account' => $_SESSION['account'],
];

R(0, "ok", $data);