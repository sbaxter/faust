<?php
if ( ! defined('FAUST') ) {
 header('HTTP/1.1 403 Forbidden', 403);
 die( 'No direct script access allowed' );
}

class daemonError extends stdClass
{
  public function daemonError($errorMessage)
  {
    $this->created = microtime(TRUE);
    $this->originPID = posix_getpid();
    $this->originIP = (PHP_SAPI !== 'cli') ? $_SERVER['SERVER_ADDR'] : $this->originIP = getIPs();
    $this->DATA = NULL;
    $this->method = 'error';
    $this->DEBUG = array();
    $this->DEBUG['error'] = TRUE;
    $this->DEBUG['message'] = $errorMessage;
  }
}

function askDaemon($messageString, $port=PORT)
{
  $foundEOM = FALSE;
  $sock = fsockopen(HOST, $port, $err, $errMsg, 5);
  if ($sock === FALSE)
  {
    if ($err)
    {
      $o = new daemonError('fsockopen failure on <' . HOST . ":" . $port . "> : " . $err . ", " . $errMsg . '"}');
      return json_encode($o);
    }
    else
    {
      $o = new daemonError("fsockopen succeeded but failed to connect(). PIR-daemon may be down");
      return json_encode($o);
    }
  }
  else
  {
    $result = fwrite($sock, $messageString . EOM);
    if ($result === FALSE)
    {
      $o = new daemonError("fwrite failure on socket");
      return json_encode($o);
    }

    $result = "";
    do
    {
      $result .= fread($sock, MSG_PAGE_SIZE);
      $foundEOM = substr($result, -3, 3) === EOM;
    } while (!feof($sock) && !$foundEOM);
    if ($foundEOM)
    {
      $result = substr($result, 0, -3);
    }
  }
  return $result;
}

function socketTCPping($host, $port, &$err = 0, &$errMsg = "")
{
  $sock = NULL;

  try {
    $sock = fsockopen("tcp://" . $host, $port, $err, $errMsg, 1);
  }
  catch (Exception $o) // There might be something wrong with the arguments ...
  {
    return FALSE;
  }

  // if the open fails it cannot be closed.
  if ($sock === FALSE) return FALSE;
  fclose($sock);
  return TRUE;
}

function PHPDaemonError($str = "")
{
  if (empty($str)) $str = socket_strerror(socket_last_error());
  // This is the daemon name
  $phpdaemon_name = 'PIR-daemon';

  // Open log file
  $handle = fopen("/tmp/$phpdaemon_name.log", "a+");

  // Obtain an exclusive lock (so that this is thread safe)
  flock($handle, LOCK_EX, $wouldblock);

  // Write
  $output = date("Y-m-d h:i:s", time())." $str\r\n";
  fwrite($handle, $output);

  // Release lock
  flock($handle, LOCK_UN);

  // Close file
  fclose($handle);
  return true;
}
