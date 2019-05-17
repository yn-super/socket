<?php
$address = "127.0.0.1";
$port = 9997; //调试的时候，可以多换端口来测试程序！

set_time_limit(0);
$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_block($sock);
socket_bind($sock, $address, $port);
socket_listen($sock, 4);

$redis = new Redis();
$redis->connect('127.0.0.1', 6379); //连接Redis
$redis->select(2);//选择数据库2

do{
    echo "Waiting for Connection...\n";
    $msgsock = socket_accept($sock);
    echo "Waiting for Request...\n";
    $buf = socket_read($msgsock, 8192);     //读取请求
    echo "Request Received: $buf\n";
    $response = hand_shake($buf);
    socket_write($msgsock,$response,strlen($response)); //发送响应

    //正式开始通信...
    $buf = socket_read($msgsock, 8192);
    //刷队列，取数据
    while ($val = $redis->rPop("socket_test"))
    {
        $msg = $val;
        $msg = code($msg);
        sleep(1);
        socket_write($msgsock, $msg, strlen($msg));
    }
    socket_close($msgsock);
}while(true);

socket_close($sock);

function hand_shake($buf){
    $buf  = substr($buf,strpos($buf,'Sec-WebSocket-Key:')+18);
    $key  = trim(substr($buf,0,strpos($buf,"\r\n")));

    $new_key = base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));

    $new_message = "HTTP/1.1 101 Switching Protocols\r\n";
    $new_message .= "Upgrade: websocket\r\n";
    $new_message .= "Sec-WebSocket-Version: 13\r\n";
    $new_message .= "Connection: Upgrade\r\n";
    $new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
    return $new_message;
}
function code($msg){
    $msg = preg_replace(array('/\r$/','/\n$/','/\r\n$/',), '', $msg);
    $frame = array();
    $frame[0] = '81';
    $len = strlen($msg);
    $frame[1] = $len<16?'0'.dechex($len):dechex($len);
    $frame[2] = ord_hex($msg);
    $data = implode('',$frame);
    return pack("H*", $data);
}
function ord_hex($data)  {
    $msg = '';
    $l = strlen($data);
    for ($i= 0; $i<$l; $i++) {
        $msg .= dechex(ord($data{$i}));
    }
    return $msg;
}

