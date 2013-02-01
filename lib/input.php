<?php
if ( ! defined('FAUST') ) {
 header('HTTP/1.1 403 Forbidden', 403);
 die( 'No direct script access allowed' );
}

/**
 * Fetch from array
 *
 * This is a helper function to retrieve values from global arrays
 *
 * @access  private
 * @param array
 * @param string
 * @return  string
 */
function _fetch_from_array(&$array, $index = '')
{
  if ( ! isset($array[$index]))
  {
    return FALSE;
  }

  //return $this->security->xss_clean($array[$index]);

  return $array[$index];
}


/**
* Fetch an item from the GET array
*
* @access public
* @param  string
* @return string
*/
function get($index = NULL)
{
  // Check if a field has been provided
  if ($index === NULL AND ! empty($_GET))
  {
    $get = array();

    // loop through the full _GET array
    foreach (array_keys($_GET) as $key)
    {
      $get[$key] = _fetch_from_array($_GET, $key);
    }
    return $get;
  }

  return _fetch_from_array($_GET, $index);
}
