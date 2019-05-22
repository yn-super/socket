<?php

$redis = new Redis();

$redis->connect('127.0.0.1', 6379); //连接Redis


$redis->select(2);//选择数据库2

$redis->lPush( "socket_test" , "goods1"); //设置测试key
$redis->lPush( "socket_test" , "goods2"); //设置测试key
$redis->lPush( "socket_test" , "goods3"); //设置测试key
$redis->lPush( "socket_test" , "goods4"); //设置测试key
$redis->lPush( "socket_test" , "goods5"); //设置测试key
$redis->lPush( "socket_test" , "goods6"); //设置测试key

?>