<?php
if ( ! defined('FAUST') ) {
 header('HTTP/1.1 403 Forbidden', 403);
 die( 'No direct script access allowed' );
}

//---
// emit() writes a message to STDOUT if the level is high enough to
//  clear the fence. By default all emit() messages **will** print
//  on STDOUT;
//---
$fence = 0;
$emitFile = STDOUT;
stream_set_blocking($emitFile, FALSE);

function emit($s, $lf = true, $level = 1)
{
  $s = is_array($s) ? implode(' | ', array_values($s)) : $s;
  global $fence;
  global $emitFile;
  if ($level > $fence)
    fwrite($emitFile, $s . ($lf ? PHP_EOL : ' '));
  return true;
}

function emitToFile($f, $s, $lf=true)
{
  fwrite($f, $s . ($lf ? PHP_EOL : ' '));
  return true;
}

// Note that it returns the old value so that you can reset the
// fence to what it used to be.
function setEmitFence($newFenceHeight)
{
  global $fence;
  $oldValue = $fence;
  $fence = $newFenceHeight;
  return $oldValue;
}

// You can send in a file you have already opened, or just the
// name of a file.
function setEmitFile($newFile)
{
  global $emitFile;
  if (!is_resource($newFile)) $newFile = mustOpen($newFile, 'w+');
  $oldEmitFile = $emitFile;
  $emitFile = $newFile;
  return $oldEmitFile;
}

function getIPs($withV6 = true)
{
    preg_match_all('/inet'.($withV6 ? '6?' : '').' addr: ?([^ ]+)/', `ifconfig`, $ips);
    return $ips[1];
}

function mayOpen($filename, $mode='r')
{
  $f = fopen($filename, $mode);
  if ($f === FALSE)
    emit ('fopen() failed while trying to open <' . $filename . '> in mode <' . $mode . '>');
  return $f;
}

// Returns a valid file handle or dies.
function mustOpen($filename, $mode='r')
{
  $f = mayOpen($filename, $mode);
  return !$f ? die() : $f;
}

//---
// At times, it is more convenient to use array[notation] rather
// than object->notation. This function converts an obect to an
// array by recursive flattening of the object.
//
// Note that it returns "self" if the argument is a non-array-
// non-object such as a simple string.
//---
function objToArray($obj)
{
  $ret = array();
  if (is_object($obj))
  {
    foreach (get_object_vars($obj) as $key => $val)
      $ret[$key] = objToArray($val);
    return $ret;
  }
  elseif (is_array($obj))
  {
    if (!count($obj))
      $ret = 'empty-array';
    else
      foreach ($obj as $key => $val)
        $ret[$key] = objToArray($val);
    return $ret;
  }
  else
    return $obj;
}

//---
// This is a little tricky. In SQL and JSON, we don't want to quote
// a literal NULL. However, if the caller is looking for a backquote,
// then we shall assume the caller knows what to ask for.
//---
function q($s, $quoteType=1)
{
  if (is_object($s)) $s = objToArray($s);
  if (is_array($s))
  {
    $s = implode('|', array_values($s));
    emit($s);
  }
  if ($s === 'NULL' && ($quoteType - 3)) return $s;
  switch ($quoteType)
  {
    default:
    case 0:
      break;
    case 1:
      $s = str_replace('\\', '\\\\', $s);
      $s = str_replace("'", "''", $s);
      $s = "'" . $s . "'"; break;
    case 2:
      $s = '"' . $s . '"'; break;
    case 3:
      $s = '`' . $s . '`'; break;
  }
  return $s;
}

function qlike($s)
{
  return q('%' . $s . '%');
}

function qlikepost($s)
{
  return q($s . '%');
}

function qlikepre($s)
{
  return q('%' . $s);
}

//---
// Returns the current (microsecond) time in two formats
// as a array. The [0] element is suitable for arithmetic,
// and the [1] element is human readable.
//---
function timeStamp()
{
  $now = microtime(TRUE);
  return array($now, date('Y-m-d H:i:s',$now));
}

function readable_json($json)
{
  $tab = "  ";
  $new_json = "";
  $indent_level = 0;
  $in_string = false;

  $json_obj = json_decode($json);

  if($json_obj === false)
    return false;

  $json = json_encode($json_obj);
  $len = strlen($json);

  for($c = 0; $c < $len; $c++)
  {
    $char = $json[$c];
    switch($char)
    {
      case '{':
      case '[':
        if(!$in_string)
        {
          $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
          $indent_level++;
        }
        else
          $new_json .= $char;
        break;
      case '}':
      case ']':
        if(!$in_string)
        {
          $indent_level--;
          $new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
        }
        else
          $new_json .= $char;
        break;
      case ',':
        if(!$in_string)
          $new_json .= ",\n" . str_repeat($tab, $indent_level);
        else
          $new_json .= $char;
        break;
      case ':':
        if(!$in_string)
          $new_json .= ": ";
        else
          $new_json .= $char;
        break;
      case '"':
        if($c > 0 && $json[$c-1] != '\\')
          $in_string = !$in_string;
      default:
        $new_json .= $char;
        break;
    }
  }

  return $new_json;
}
