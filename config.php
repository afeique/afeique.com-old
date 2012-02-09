<?php

// stages things to give more detailed information useful for debugging
// (e.g. ajax calls, database connects, etc)
// sets php error_reporting to E_ALL
define('DEBUG', 0);

define('TIMEZONE', 'US/Eastern');

define('BASE_PATH', dirname(__FILE__).'/');
define('CRAFTERS_PATH', BASE_PATH.'crafters/');
define('MODELS_PATH', BASE_PATH.'models/');
define('JS_PATH', BASE_PATH.'js/');

define('PUBLISHED_POSTS_DIR','posts/');
define('UNPUBLISHED_POSTS_DIR','unpublished/');

define('PUBLISHED_POSTS_PATH', BASE_PATH.PUBLISHED_POSTS_DIR);
define('UNPUBLISHED_POSTS_PATH', BASE_PATH.UNPUBLISHED_POSTS_DIR);

define('BASE_URL', 'http://'.$_SERVER['HTTP_HOST'].($_SERVER['HTTP_HOST'] == 'localhost' ? '/afeique.com/' : '/'));
define('CSS_URL', BASE_URL.'css/');
define('JS_URL', BASE_URL.'js/');

// kinda stupid... hey, at least it saves typing quotes :D
define('LOGOUT', 'logout');
define('USERNAME','username');
define('PASSWORD','password');

define('EXTRA', 'extra'); // extra queries



define('META_REFRESH_TIME', 3);
define('POST_DATE_FORMAT', 'M j, Y @ H:i');

?>