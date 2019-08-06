<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Waljqiang\Socket\Socket;

$config = [
	'ip' => '255.255.255.255',
	'port' => 8091
];

$udpClient = new Socket($config);
try{
	$udpClient->revBroadcast(function($remote,$port,$buff){
		echo "Receive $buff from remote address $remote and remote port $port" . PHP_EOL;
	});
}catch(\Exception $e){
	var_dump($e->getCode());
	var_dump($e->getMessage());
}