<?php
if ( ! defined('FAUST') ) {
 header('HTTP/1.1 403 Forbidden', 403);
 die( 'No direct script access allowed' );
}

// The manifest of available daemon functions will limit what we
//    will cache (we do not want to create bogus cache files)
$gManifest = array(
  'echo'
);

// Host machine of the daemon.
define( 'HOST', '127.0.0.1' );

// Port used to communicate with the daemon.
define( 'PORT', 88888 );

// Time to cache feed files (in seconds).
define( 'CACHE_TIME', 60*60 );

// Default limit of records returned.
define( 'DEFAULT_LIMIT', 5 );

// When you read the socket, how much do you ask for at a time?
define( 'MSG_PAGE_SIZE', 4096 );

// This is the symbol that marks the end of a request.
define( 'EOM', '$$$' );
