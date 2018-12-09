<?php
// 配置文件 
global $CFG;

$CFG = [

'db' => [
     'host' => '127.0.0.1',
     'port' => '3306',
     'user' => 'root',
     'dbName' => 'tx_exam',
     'passwd' => '',
     'charset' => 'utf8',
    ],
    
 'cache' => [
    'type' => 'redis',
    
 ],
 'redis' => [
    'host' => '127.0.0.1',
    'port' => 6379,
    'timeout' => 3,
    'auth' => null,
 ],
];