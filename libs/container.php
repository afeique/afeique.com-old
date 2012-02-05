<?php

require_once 'error.php';

/**
 * a container contains other renderable objects (anything with a __toString() method)
 * used to group multiple renderable objects together into a single object to be passed around
 */
class container {
  protected $content;

  public function __construct() {
    $this->content = array();
  }

  /**
   * embed renderable content
   * 
   * enter each piece of content as
   * as a separate arg
   * 
   * returns $this for chaining
   */
  public function __() {
    $contents = func_get_args();

    foreach ($contents as $content) {
      if ($this->is_renderable($content)) {
        if (is_array($content)) {
          foreach ($content as $c)
            $this->embed($c);
        } else
          $this->content[] = $content;
      }
    }

    return $this;
  }

  protected function is_renderable($content) {
    if (is_scalar($content))
      return 1;
    elseif (is_array($content))
      return 1;
    elseif (is_object($content) && method_exists($content, '__toString'))
      return 1;

    throw error::expecting_renderable();
  }

  public function __toString() {
    if (empty($this->content))
      return '';

    return implode('', $this->content);
  }
}