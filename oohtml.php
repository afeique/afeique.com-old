<?php

require_once 'config.php';
require_once 'libs/element.php';

/**
 * this file simply contains numerous helper methods
 * used to generate oohtml
 */

/**
 * doesn't o look like a container?
 */
function o() {
  $o = new container();
  $args = func_get_args();
  foreach ($args as $content) {
    $o->__($content);
  }
  
  return $o;
}

/**
 * blocks are chunks of static oohtml
 * 
 * for example, most pages have the same basic
 * styles in the head, so that html is good to
 * place into a block
 * 
 * content that changes between each page, for
 * example the page title, is not good to put into
 * a block
 */
function b($block) {
  ob_start();
  require "blocks/$block.php";
  $content = ob_get_clean();
  
  return $content;
}

/**
 * 'l' stands for 'element'
 * as in 'html element'
 */
function l($name) {
  return new element($name);
}

/**
 * self-closing element
 * aka a self-blocking l
 * aka l-blocking-l
 * aka ll
 */
function ll($name) {
  return new element($name, $self_closing=1);
}

function html() {
  $args = func_get_args();
  return l('html')->__($args);
}

function head() {
  $args = func_get_args();
  return l('head')->__($args);
}

function body() {
  $args = func_get_args();
  return l('body')->__($args);
}

function h1() {
  $args = func_get_args();
  return l('h1')->__($args);
}

function h2() {
  $args = func_get_args();
  return l('h2')->__($args);
}

function h3() {
  $args = func_get_args();
  return l('h3')->__($args);
}

function ul() {
  $args = func_get_args();
  return l('ul')->__($args);
}

function ol() {
  $args = func_get_args();
  return l('ol')->__($args);
}

function li() {
  $args = func_get_args();
  return l('li')->__($args);
}

function span() {
  $args = func_get_args();
  return l('span')->__($args);
}

function p() {
  $args = func_get_args();
  return l('p')->__($args);
}

function em() {
  $args = func_get_args();
  return l('em')->__($args);
}

function strong() {
  $args = func_get_args();
  return l('strong')->__($args);
}

function sub() {
  $args = func_get_args();
  return l('sub')->__($args);
}

function sup() {
  $args = func_get_args();
  return l('sup')->__($args);
}

function html5_doctype() {
  return '<!DOCTYPE html>';
}

function title($text) {
  return l('title')->__(htmlentities($text));
}

function css_link($file, $media='all') {
  return ll('link')->_('rel','stylesheet')->_t('text/css')->_h(CSS_URL.$file)->_('media', $media);
}

function script_src($src) {
  return l('script')->_t('text/javascript')->_('src', JS_URL.$src);
}

function meta_charset($charset='utf-8') {
  return ll('meta')->_('http-equiv','Content-Type')->_('content',"text/html; charset=$charset");
}

function html_if($condition, $content) {
  return "<!--[if $condition]>$content<![endif]-->";
}

function a_link($href, $content) {
  return l('a')->_h($href)->__($content);
}

function b_link($href, $content) {
  return a_link($href, $content)->_('target','_blank');
}

function l_link($href, $content='') {
  if (empty($content))
    $content = $href;
  return l('a')->_h(BASE_URL.$href)->__($content);
}

function checkbox() {
  return l('input')->_t('checkbox');
}

function header_redirect($rel_url) {
  header('Location: '.BASE_URL.$rel_url);
}

function code($text) {
  if (!is_string($text))
    throw error::expecting_string();
  
  return l('span')->_c('code')->__(htmlentities($text));
}