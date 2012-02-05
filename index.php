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

$request = trim($request,'/');

$request = str_replace('-', '_', $request);
$request_parts = explode('/', $request);

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

if (!empty($extra)) {
  $extra = array_reverse($extra);
  $GLOBALS[EXTRA] = $extra;
}

if (!isset($crafter)) {
  $crafter = new root_crafter;
  if (!empty($request_parts))
    $page = $request_parts[0];
}

if (!empty($page))
  $crafter->request($page);

//$crafter->craft();
echo $crafter;

//var_dump($_SERVER);

?>