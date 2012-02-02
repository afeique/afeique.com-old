<?php

require_once 'error.php';

/**
 * this contains magic
 *
 */
class container {
  protected $content;

  public function __construct() {
    $this->content = array();
  }

  /**
   * Embed renderable content.
   * 
   * Enter each piece of content as
   * as a separate arg.
   * 
   * Returns $this for chaining.
   * 
   * @param mixed $content
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