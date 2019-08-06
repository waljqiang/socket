<?php
//http://127.0.0.1/socket/examples/send.php
require_once __DIR__ . '/../vendor/autoload.php';
use Waljqiang\Socket\Socket;
$config = [
	'ip' => '255.255.255.255',
	'port' => 8091
];

/*$data = [
	'type' => '3',
	'key' => '04ddc2a549646110c28527272b19e69b',
	'ip' => '127.0.0.1'
];*/

$datas = [
	[
		'type' => '3',
		'key' => '04ddc2a549646110c28527272b19e69b',
		'ip' => '127.0.0.1'
	],
	[
		'type' => '1'
	]
];

$udpClient = new Socket($config);
try{
	//$rs = $udpClient->broadcast($data);
	$rs = $udpClient->broadcasts($datas);
	var_dump($rs);
}catch(\Exception $e){
	var_dump($e->getCode());
	var_dump($e->getMessage());
}