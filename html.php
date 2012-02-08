<?php

require_once 'config.php';
require_once 'libs/tag.php';

/**
 * this file simply contains numerous helper methods
 * used to generate oohtml
 */

/**
 * doesn't o look like a container?
 */
function o() {
  return new container();
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
  return new tag($name);
}

/**
 * self-closing element
 * aka a self-blocking l
 * aka l-blocking-l
 * aka ll
 */
function ll($name) {
  return new tag("$name", $self_closing=1);
}

function shortcut($name, array $contents) {
  $l = l($name);
  foreach ($contents as $content)
    $l->__($content);
  
  return $l;
}

function html() {
  $args = func_get_args();
  return shortcut('html', $args);
}

function head() {
  $args = func_get_args();
  return shortcut('head', $args);
}

function body() {
  $args = func_get_args();
  return shortcut('body', $args);
}

function h1() {
  $args = func_get_args();
  return shortcut('h1', $args);
}

function h2() {
  $args = func_get_args();
  return shortcut('h2', $args);
}

function h3() {
  $args = func_get_args();
  return shortcut('h3', $args);
}

function ul() {
  $args = func_get_args();
  return shortcut('ul', $args);
}

function li() {
  $args = func_get_args();
  return shortcut('li', $args);
}


function p() {
  $args = func_get_args();
  return shortcut('p', $args);
}

function em() {
  $args = func_get_args();
  return shortcut('em', $args);
}

function strong() {
  $args = func_get_args();
  return shortcut('strong', $args);
}

function html5_doctype() {
  return '<!DOCTYPE html>';
}

function title($text) {
  return shortcut('title', array( htmlentities($text) ));
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