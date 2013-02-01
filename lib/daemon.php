<?php
if ( ! defined('FAUST') ) {
 header('HTTP/1.1 403 Forbidden', 403);
 die( 'No direct script access allowed' );
}

class DaemonRequest extends stdClass
{
  public function DaemonRequest($method = "")
  {
    //$this->DEBUG['start'] = timeStamp();
    $this->created = microtime(TRUE);
    $this->originPID = posix_getpid();
    $this->originIP = (PHP_SAPI !== 'cli') ? $_SERVER['SERVER_ADDR'] : $this->originIP = getIPs();
    $this->result = NULL;
    $this->method = $method;
  }
}

class DaemonReply extends stdClass
{
  public function DaemonReply($obj = NULL)
  {
    $this->created = microtime(TRUE);
    $this->originPID = posix_getpid();
    $this->originIP = (PHP_SAPI !== 'cli') ? $_SERVER['SERVER_ADDR'] : $this->originIP = getIPs();
    $this->result = NULL;

    if ($obj !== NULL)
      $this->sourceMsg = $obj;
  }
}
