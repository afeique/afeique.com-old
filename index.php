<?php

date_default_timezone_set('US/Eastern');

session_start();

mb_language('uni');
mb_internal_encoding('utf-8');

$uri = $_SERVER['REQUEST_URI'];

// query lib
require 'libs/ActiveRecord/ActiveRecord.php';

// oohtml
require 'html.php';

// validator
require 'libs/validate.php';

// helper functions
require 'helpers.php';

// base crafters
require 'libs/crafter.php';
require 'crafters/template_crafter.php';
require 'crafters/root_crafter.php';

// parse query string
$request = $_SERVER['QUERY_STRING'];

// strip any additional data appended to the query string
$request = explode('&', $request);
$request = $request[0];

// clean up query string
$request = trim($request,'/');
$request = str_replace('-', '_', $request);

// explode query string for looping and path generation
$request_parts = explode('/', $request);

/**
 * determine what crafter file to call and what page to request
 * 
 * loop over the query string in reverse order;
 * 
 * starting with the full query string, each iteration generates
 * a new path with one less part than the previous part;
 * 
 * the last part of the current iteration is reserved as the
 * name of the crafter file and '_crafter.php' is appended to it;
 * 
 * if a crafter file is found in that path, reference the last part
 * from the previous iteration and use that as the page request;
 * 
 * unused parts (including the page-request) are stored to the
 * $extra array
 * 
 * example
 * query-string = something/arbitrarily/random
 * 
 * 1st iteration
 * path = CRAFTERS_PATH.'something/arbitrarily/'
 * crafter-file = 'random_crafter.php'
 * crafter-file doesn't exist: store 'random' to $extra, continue to next iteration
 * 
 * 2nd iteration
 * path = CRAFTERS_PATH.'something/'
 * crafter-file = 'arbitrarily_crafter.php'
 * crafter-file exists!
 * page request = 'random' (remember: $extra[0] = 'random' as well)
 * 
 * at which point an instance of arbitrarily_crafter (the crafter class
 * also needs to be named arbitrarily_crafter) will be created and the
 * page request passed to it
 * 
 */
$page = '';
$crafter = null;
$extra = array();
foreach (array_reverse($request_parts, $preserve_keys=1) as $i => $p) {
  $crafter_name = $p.'_crafter';
  $crafter_path = CRAFTERS_PATH.implode('/', array_slice($request_parts, 0, $i)).$crafter_name.'.php';
  
  if (!file_exists($crafter_path)) {
    $extra[] = $p;
    continue;
  }
  
  require $crafter_path;
  
  if (!class_exists($crafter_name)) {
    $extra[] = $p;
    continue;
  }
    
  $crafter = new $crafter_name;
  if (!empty($request_parts[$i+1]))
    $page = $request_parts[$i+1];
  
  break;
}

// if $extra array nonempty, make it
// superglobal for easy access
if (!empty($extra)) {
  $extra = array_reverse($extra);
  $GLOBALS[EXTRA] = $extra;
}

/**
 * if no crafter was found, create an instance
 * of root_crafter
 * 
 * if there was a query string, pass on the first
 * part of that request as the page request
 */
if (!isset($crafter)) {
  $crafter = new root_crafter;
  if (!empty($request_parts))
    $page = $request_parts[0];
}

// make the page request if it exists
if (!empty($page))
  $crafter->request($page);

/**
 * render the crafter
 * 
 * note that if no page request was made,
 * the crafter defaults to rendering the index
 */
//$crafter->craft();
echo $crafter;

?>