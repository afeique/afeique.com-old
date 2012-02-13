<?php

class view_crafter extends template_crafter {
  protected $post;
  protected $debug;
  
  public function __construct() {
    parent::__construct();
    
    // unlike other crafters, don't default to displaying the index
    $this->request = '_400';
    
    // this instance var contains additional debug info
    $this->debug = array();
  }
  
  protected function _index() {
    $this->title = $this->post->title;
    $this->content = $this->view_posts(array($this->post));
  }
  
  protected function _400() {
    header('Status: 400 Bad Request'); header('HTTP/1.0 400 Bad Request');
    $this->title = 'No Post ID';
    $this->content = p(
        'Require a nonzero positive integer post id appended to the url.'
    );
  }
  
  protected function _404() {
    header('Status: 404 Not Found'); header('HTTP/1.0 404 Not Found');
    $this->title = 'Post Not Found';
    
    $debug = '';
    if (DEBUG && !empty($this->debug))
      $debug = o(
          l('h2')->_c('code')->__('DEBUG INFO'),
          l('p')->_c('code')->__(implode(ll('br'), $this->debug))
      );
    
    $this->content = o(
        p('Sorry, no post could be found. Either no post with the given id exists in the database, or its content 
            could not be found.'),
        $debug
    );
  }
  
  public function request($page) {
    $id = (int)$page;
    if (empty($id)) {
      $this->_request = '_400';
      return;
    }
    
    try {
      $this->post = Post::find($id);
    } catch (Exception $e) {
      unset($this->post);
      if (DEBUG)
        $this->debug[] = $e->getMessage();
    }
    
    if (empty($this->post)) {
      $this->request = '_404';
      return;
    }
    
    $path = $this->post_path($this->post->directory, $this->post->time_first_published);
    if (!is_file($path.'content.php')) {
      $this->debug[] = 'content.php either not a file or not found in '.$path;
      $this->request = '_404';
      return;
    }
    
    $this->request = '_index';
  }
}

?>