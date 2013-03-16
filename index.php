<?php
// FAUST
// -----
// Faust version number. Defining this constant will unlock access
//    to our includes.
define( 'FAUST', '0.2' );

require_once( './etc/config.php' );
require_once( './lib/daemon.php' );
require_once( './lib/socket.php' );
require_once( './lib/input.php' );


// MAIN
// ----

// The Request
$gRequest = request();

// If we have a valid request, serve the response
if ( empty( $gRequest ) ) {
  respond( 400 );
} else {
  serve( $gRequest );
}

// END MAIN
// --------

// Faust Specific Functions
// ------------------------
// request()
//    returns an array of request data or false for invalid requests.
// respond()
//    sends JSON message back to the requesting client.
// serve( array )
//    loads a cached response or fetches a new one from the daemon.

function request() {
  global $gManifest; // defined in `./etc/config.php`

  // Check to see if the request is being made via AJAX
  $isAjax = !empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] )
            && $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] === 'XMLHttpRequest';

  // Parse the GET to form a request.
  $method  = get( 'method' );
  $limit   = get( 'limit' );
  $target  = get( 'target' );

  if ( !$isAjax || empty( $method ) || !in_array( $method, $gManifest ) ) {
    return false;
  }

  return array(
    'method'      => $method
    , 'limit'     => $limit ? $limit : DEFAULT_LIMIT
    , 'target'    => $target
  );
}

function respond( $status = 200, $data = '' ) {
  global $gStatus;

  // sets JSON headers and sends json to the requester.
  header( $gStatus[ $status ], $status );
  header( 'Content-type: application/json' );
  header( 'Cache-Control: no-cache, must-revalidate' );

  // If we already have JSON, no need to encode.
  echo $status === 200 ? $data : json_encode( array( 'error' => true ) );
}

function serve( $request ) {
// Decides whether to serve the cached version or contact the daemon
  // The cache file
  $cache = './cache/' . $request[ 'method' ];
  $cache.= !empty( $request[ 'target' ] ) ? '_'.$request[ 'target' ] : '';
  $cache.='.json';

  // Expiration time
  $expire = time() - CACHE_TIME;

  $fExists = file_exists( $cache );

  // Stat the cached file (if it exists)
  $stat = $fExists ? stat( $cache ) : array( 9 => $expire );

  if ( $stat[9] <= $expire ) {
    // File is old, refresh by calling the daemon
    $outboundObj = new DaemonRequest( $request[ 'method' ] );
    $outboundObj->target = $request[ 'target' ];
    $outboundObj->limit = $request[ 'limit' ];

    $json = askDaemon( json_encode( $outboundObj ) );

    if ( !empty( $json ) ) {
      // Write the json cache file.
      file_put_contents( $cache, $json );
    } elseif ( $fExists ) {
      // Something went wrong, use the cache file
      $json = file_get_contents( $cache );
    } else {
      // We do not have a cache file or a json response
      respond( 404 );

      unlink( $cache ); // just in case?
      return false;
    }
  } else {
    // Use the cached file
    $json = file_get_contents( $cache );
  }

  respond( 200, $json );
}
