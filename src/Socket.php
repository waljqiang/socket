<?php
namespace Waljqiang\Socket;
use Waljqiang\Socket\Exceptions\SocketException;

class Socket {
    /**
     * domain 套接字使用协议.AF_INET,IPv4 网络协议。TCP 和 UDP 都可使用此协议;AF_INET6,IPv6 网络协议。TCP 和 UDP 都可使用此协议;AF_UNIX,本地通讯协议。具有高性能和低成本的 IPC(进程间通讯)
     * type 套接字使用的类型.SOCK_STREAM,提供一个顺序化的、可靠的、全双工的、基于连接的字节流。支持数据传送流量控制机制。TCP 协议即基于这种流式套接字;SOCK_DGRAM,提供数据报文的支持。(无连接，不可靠、固定最大长度).UDP协议即基于这种数据报文套接字;SOCK_SEQPACKET,提供一个顺序化的、可靠的、全双工的、面向连接的、固定最大长度的数据通信；数据端通过接收每一个数据段来读取整个数据包;SOCK_RAW,提供读取原始的网络协议。这种特殊的套接字可用于手工构建任意类型的协议。一般使用这个套接字来实现 ICMP 请求(例如 ping);SOCK_RDM,提供一个可靠的数据层，但不保证到达顺序。一般的操作系统都未实现此功能
     * protocol 指定 domain 套接字下的具体协议.icmp,Internet Control Message Protocol 主要用于网关和主机报告错误的数据通信;udp,User Datagram Protocol 是一个无连接的、不可靠的、具有固定最大长度的报文协议;tcp,Transmission Control Protocol 是一个可靠的、基于连接的、面向数据流的全双工协议。TCP 能够保障所有的数据包是按照其发送顺序而接收的。如果任意数据包在通讯时丢失，TCP 将自动重发数据包直到目标主机应答已接收。因为可靠性和性能的原因，TCP 在数据传输层使用 8bit 字节边界。因此，TCP 应用程序必须允许传送部分报文的可能;SOL_TCP,TCP协议;SOL_UDP,UDP协议
     */
    private $config = [
        'ip' => '255.255.255.255',
        'port' => 8091,
        'timeOut' => 10,
        'domain' => AF_INET,
        'type' => SOCK_DGRAM,
        'protocol' => SOL_UDP
    ];

    public function __construct($config = []){
        $this->config = array_merge($this->config,$config);
    }

    public function setConfig($config){
        $this->config = array_merge($this->config,$config);
        return true;
    }

    /**
     * 发送UDP请求
     *
     * @param array  $data 消息
     * @return boolean
     * @throws Waljqiang\Socket\Exceptions\SocketException
     */
    public function broadcast($data){
        $socket = @socket_create($this->config['domain'], $this->config['type'], $this->config['protocol']);
        if (!$socket) {
            throw new SocketException(socket_strerror(socket_last_error()),socket_last_error());
        }

        $timeOuter = ['sec' => $this->config['timeOut'], 'usec' => 0];

        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, $timeOuter);
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeOuter);
        socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1); //设置为广播方式

        if (@!socket_connect($socket, $this->config['ip'], $this->config['port'])) {
            throw new SocketException(socket_strerror(socket_last_error()),socket_last_error());
        }

        $jsonData = json_encode($data,JSON_UNESCAPED_UNICODE);
        $sendLen = strlen($jsonData);
        $sent = @socket_write($socket,$jsonData,$sendLen);

        if (!$sent || $sent != $sendLen) {
            throw new SocketException(socket_strerror(socket_last_error()),socket_last_error());
        }

        @socket_close($socket);
        return $sent;
    }

    public function broadcasts($datas){
        $flag = true;
        $socket = @socket_create($this->config['domain'], $this->config['type'], $this->config['protocol']);
        if (!$socket) {
            throw new SocketException(socket_strerror(socket_last_error()),socket_last_error());
        }

        $timeOuter = ['sec' => $this->config['timeOut'], 'usec' => 0];

        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, $timeOuter);
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeOuter);
        socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1); //设置为广播方式

        if (@!socket_connect($socket, $this->config['ip'], $this->config['port'])) {
            throw new SocketException(socket_strerror(socket_last_error()),socket_last_error());
        }
        foreach ($datas as $data) {
            $jsonData = json_encode($data,JSON_UNESCAPED_UNICODE);
            $sendLen = strlen($jsonData);
            $sent = @socket_write($socket,$jsonData,$sendLen);

            if (!$sent || $sent != $sendLen) {
                $code = socket_last_error();
                $msg = socket_strerror(socket_last_error());
                $flag = false;
                break;
            }
        }

        @socket_close($socket);

        if(!$flag){
            throw new SocketException($msg,$code);
        }
        return true;
    }

    public function revBroadcast($callable = ''){
        ob_implicit_flush();
        $socket = @socket_create($this->config['domain'], $this->config['type'], $this->config['protocol']);
        if (!$socket) {
            throw new SocketException(socket_strerror(socket_last_error()),socket_last_error());
        }

        socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1); //设置为广播方式
        
        if(@!socket_bind( $socket, '0.0.0.0',8091)){
            throw new SocketException(socket_strerror(socket_last_error()),socket_last_error());
        }

        while(true){
            $from = "";
            $prot = 0;
            @socket_recvfrom( $socket, $buff, 1024, 0, $from,$port);
            call_user_func_array($callable,[$from,$port,$buff]);
        }

        @socket_close($socket);
    }

}