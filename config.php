<?php
/**
 * stages things to give more detailed information useful for debugging
 * (e.g. ajax calls, database connects, etc)
 * sets php error_reporting to E_ALL
 * template uses development JS and CSS instead of compressed versions
 */
define('DEBUG', 0);

/**
 * loads all javascript inline instead of via deferment
 */
define('INLINE_JS', 0);

define('BASE_JS','base-js');
define('ADMIN_JS','admin.js');
define('PUBLIC_MESHED_JS','public-meshed.packed.js');
define('ADMIN_MESHED_JS','admin-meshed.packed.js');

$GLOBALS[BASE_JS] = array('jquery-1.7.1.min.js','jquery-ui-1.8.17.min.js','base.js');

define('BASE_CSS', 'base');
define('ADMIN_CSS', 'admin');

define('TIMEZONE', 'US/Eastern');

define('BASE_PATH', dirname(__FILE__).'/');
define('LIBS_PATH', BASE_PATH.'libs/');
define('CRAFTERS_PATH', BASE_PATH.'crafters/');
define('MODELS_PATH', BASE_PATH.'models/');
define('JS_PATH', BASE_PATH.'static/js/');
define('CSS_PATH', BASE_PATH.'static/css/');

define('PUBLISHED_POSTS_DIR','posts/');
define('UNPUBLISHED_POSTS_DIR','unpublished/');

define('PUBLISHED_POSTS_PATH', BASE_PATH.PUBLISHED_POSTS_DIR);
define('UNPUBLISHED_POSTS_PATH', BASE_PATH.UNPUBLISHED_POSTS_DIR);

define('LOCALHOST', $_SERVER['HTTP_HOST'] == 'localhost' ? 1 : 0);

define('BASE_URL', 'http://'.$_SERVER['HTTP_HOST'].(LOCALHOST ? '/afeique.com/' : '/'));
define('STATIC_URL', 'http://'.(LOCALHOST ? 'localhost/afeique.com/static/' : 'static.afeique.com/'));
define('CSS_URL', STATIC_URL.'css/');
define('JS_URL', STATIC_URL.'js/');

// kinda stupid... hey, at least it saves typing quotes :D
define('LOGOUT', 'logout');
define('EXTRA', 'extra'); // extra queries

define('META_REFRESH_TIME', 3);
define('POST_DATE_FORMAT', 'M j, Y @ H:i');

define('MYSQL_CREDENTIALS', BASE_PATH.'mysql_credentials.php');

?>