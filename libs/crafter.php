<?php

require_once 'error.php';

/**
 * abstract crafter is parent of all crafters
 * 
 * ::__construct method reflects upon instance to determine
 * all protected/private method whose names are prefixed with 
 * an _ (underscore)
 * 
 * it classifies these methods as pages (in array ->pages)
 * additionally, it sets the default request to _index so a
 * crafter can be rendered without making an explicit request
 * 
 * the ::_index and ::_404 methods are left abstract for child 
 * crafters to define their own index and 404 pages
 * 
 * when a request for a page is made externally (via ::request)
 * checks whether the requested page is in the ::$pages array
 * (automatically prepends underscore to passed request if it
 * isn't present)
 * 
 * if the requested page isn't found, the request is automatically
 * altered to a _404
 * 
 * the ::craft method is left abstract for child crafters to
 * define their own method of crafting
 * 
 * typically:
 * the ::craft method executes the "page method" 
 * corresponding to the request made
 * 
 * the page method then stores its output in $this->content
 * which is in turn "rendered" by the ::craft method
 * 
 * the "render" process just consists of casting
 * $this->content to a string and returning that
 * 
 * the ::__toString method simply returns the output
 * of the craft method, which is the content rendered
 * as a string
 * 
 */
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