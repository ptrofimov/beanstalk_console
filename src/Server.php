<?php
class Server
{
    /** @var string */
    private $host;
    /** @var int */
    private $port;
    /** @var int */
    private $ttl;

    public function __construct($host, $port, $ttl)
    {
        $this->host = (string) $host;
        $this->port = (int) $port;
        $this->ttl = (int) $ttl;
    }

    /** @return string */
    public function getHost()
    {
        return $this->host;
    }

    /** @return int */
    public function getPort()
    {
        return $this->port;
    }

    /** @return int */
    public function getTtl()
    {
        return $this->ttl;
    }
}
