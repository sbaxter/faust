#Faust

## Overview
Faust is a service that converts AJAX requests into PHP-Daemon requests.

```
.
|-- .htaccess
|-- index.php
|-- cache
|-- docs
|-- `-- readme.md
|
|-- etc
|   `-- config.php
|
|-- lib
|   |-- cli.php
|   |-- daemon.php
|   |-- input.php
|   `-- socket.php
|
`-- tests
    |-- daemon.php
    |-- curl.php
    |-- echo.json
    `-- get_content_article.json
```

## Dependencies:
 1. PHP-Daemon (soon to appear on github)
 1. PHP 5.3.x

## The Engine: index.php
When a request is made of Faust, he will parse the request to make sure:

 1. It is an AJAX request.
 1. There is a valid `method` parameter in the query string.

If Faust determines the request to be invalid, he will respond with a `400` HTTP error code (bad request). If Faust considers the request worthy enough, he will first check his available cache files. When a cache file is available, he will send the contents back to the requesting client. When the cache file is out of date (or if it does not exist), Faust will call on the daemon, cache the response, and serve the response to the client.

## Configuration
The `./etc/config.php` file contains key PHP constants and variables used by specific implementations of Faust.

## Cache
Faust does not want to talk to the daemon unless he has to. Therefore, we cache daemon responses (expiration defined in `./etc/config.php`). Keeping a list of available daemon operations (`$gManifest` defined in `./etc/config.php`) allows Faust to only cache useful requests.

## Libraries
 * **cli** - Command line helpers written by George Flanagin (aka helper_helpers).
 * **daemon** - Daemon helpers written by George Flanagin.
 * **input** - Input helpers taken from the CodeIgniter framework.
 * **socket** - Helpers used in working with the socket connection—also written by George Flanagin.

## Tests
`./tests/curl.php` is a command line script used to send HTTP requests to Faust.

The `./tests/daemon.php` is a command line script used to check daemon operation. The `.json` files are individual tests. Usage:
```
$ php daemon.php {port file-of-messages.json}
```
Example:
```
$ php daemon.php 33333 echo.json

echo.json opened.
# Let's see if echo works, and what it returns.

+++++++++++ BEGIN +++++++++++++
1 ::: request  : << {
  "method": "echo"
} >>
----------------------

1 ::: reply    : << {
  "method": "echo",
  "start": [
    1359040166.4399,
    "2013-01-24 10:09:26"
  ],
  "version": null,
  "error_number": [

  ],
  "error_message": [

  ],
  "message": null,
  "records": -1,
  "METHODS": [
    "bump",
    "echo",
    "get_content_article",
    "signal",
    "post",
    "prep",
    "showlog",
    "unknown",
    "unsupported"
  ],
  "stop": [
    1359040166.44,
    "2013-01-24 10:09:26"
  ],
  "elapsedtime_ms": 0.09,
  "responder_pid": 28126
} >>
------------ END --------------
```
