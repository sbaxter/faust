<?php if ( php_sapi_name() !== 'cli' || ! defined('STDIN') ) die( 'Access Restricted' );

define( 'FAUST', 'TEST' );

require_once('./../etc/config.php');
require_once('./../lib/daemon.php');
require_once('./../lib/cli.php');
require_once('./../lib/socket.php');

if ($argc < 3)
  die('Usage: daemontester {port file-of-messages.json}');

$i = 1;
$f = mustOpen($argv[2], 'r');
emit ($argv[2] . ' opened.');

$port = intval($argv[1]);
while(!feof($f))
{
  // read a line.
  $s = trim(fgets($f));

  // skip comments and blank lines.
  if (empty($s) || $s[0] === '#') { emit($s); continue; }

  // Format the request for readability, and echo it.
  emit(PHP_EOL . '+++++++++++ BEGIN +++++++++++++');
  emit("$i ::: request  : << " . readable_json($s) . ' >>');
  emit('----------------------' . PHP_EOL);

  // Format the result for readability, and echo it.
  emit("$i ::: reply    : << " . readable_json(askDaemon($s, $port)) . ' >>');
  emit('------------ END --------------' . PHP_EOL);
  $i++;
}
