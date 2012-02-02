<?php

require_once 'error.php';

abstract class crafter {
  protected $pages;
  protected $request;

  public function __construct() {
    $this->pages = array();

    $methods = get_class_methods($this);
    foreach($methods as $method) {
      if (strpos($method,'__') === 0)
        continue;
      	
      if (strpos($method,'_') !== 0)
        continue;
      
      $reflect = new ReflectionMethod($this, $method);
      if ($reflect->isProtected() || $reflect->isPrivate())
        $this->pages[$method] = 1;
    }

    $this->request = '_index';
  }

  public function request($page) {
    if (!is_string($page))
      throw error::expecting_string();

    if (strpos($page,'_') !== 0)
      $page = '_'.$page;

    if (isset($this->pages[$page])) {
      $this->request = $page;
      return 1;
    }

    $this->request = '_404';
    return 0;
  }
  
  public function __toString() {
    return $this->craft();
  }
  
  abstract public function craft();
  abstract protected function _index();
  abstract protected function _404();
}

?>