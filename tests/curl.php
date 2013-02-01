<?php if ( php_sapi_name() !== 'cli' || ! defined('STDIN') ) die( 'Access Restricted' );

// Curl.php - a simple set of HTTP requests to send to Faust.

define( 'FAUST', 'TEST' );

require_once('./../etc/config.php');
require_once('./../lib/cli.php');
require_once('./../lib/input.php');

if ($argc < 3)
  die('Usage: faust {URL method}');

$url = $argv[1];
$method = $argv[2];

if ( !in_array( $method, $gManifest ) ) {
  emit( 'Invalid method!' );
  emit( 'Valid methods:' );
  foreach( $gManifest as $k => $mthd ) {
    emit( '  * ' . $mthd );
  }
  die();
}


// TEST ONE
emit( 'Test One: a bogus method should return a 400 error code...' );
$ch = curl_init( $url . '/?method=bogus' );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

$output = curl_exec( $ch );
curl_close( $ch );

$output = json_decode( $output, true );
if ( !empty($output['statusCode']) && $output['statusCode'] === 400 ) {
  emit( '...test passed!' );
} else {
  emit( json_encode( $output ) );
  emit( '...test FAILED!' );
}
emit('---');


// TEST TWO
emit( 'Test Two: a non-ajax request should return a 400 error code...' );
$ch = curl_init( $url . '/?method=' . $method );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

$output = curl_exec( $ch );
curl_close( $ch );

$output = json_decode( $output, true );
if ( !empty($output['statusCode']) && $output['statusCode'] === 400 ) {
  emit( '...test passed!' );
} else {
  emit( json_encode( $output ) );
  emit( '..test FAILED!' );
}
emit('---');

// Test Three
emit( 'Test three: faking an ajax request...');
$ch = curl_init( $url . '/?method=' . $method );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'X-Requested-With: XMLHttpRequest' ) );

$output = curl_exec( $ch );
curl_close( $ch );

$output = json_decode( $output, true );
if ( !empty($output['DEBUG']) ) {
  emit( '...test passed!' );
} else {
  emit( json_encode( $output ) );
  emit( '...test FAILED!' );
}
emit('---');

die( 'the end' );
