<?php

class view_crafter extends template_crafter {
  protected $post;
  
  public function __construct() {
    parent::__construct();
    
    // unlike other crafters, don't default to displaying the index
    $this->request = '_400';
  }
  
  protected function _index() {
    $this->title = $this->post->title;
    
    ob_start();
    require $this->post_path($this->post->directory, $this->post->time_first_published).'content.php';
    $this->content = ob_get_clean();
    
    $this->use_template = (isset($no_template) ? 0 : 1);
    $this->alt_header = (isset($header) ? $header : '');
    
    if (isset($styles)) {
      if (!is_array($styles))
        throw error::expecting_array();
      $this->styles = array_merge($this->styles, $styles);
    }
    
    if (isset($scripts)) {
      if (!is_array($scripts))
        throw error::expecting_array();
      $this->scripts = array_merge($this->scripts, $scripts);
    }
  }
  
  protected function _400() {
    header('Status: 400 Bad Request'); header('HTTP/1.0 400 Bad Request');
    $this->title = 'No Post ID Provided';
    $this->content = p(
        strong('Require nonzero positive integer post id in url.'),' ',
        'A preemptive diagnosis indicates the possibility of this malformed url being generated by a human.
        This machine recommends you utilize '.a_link('en.wikipedia.org/wiki/Hyperlink', 'hyperlinks')->_('target','_blank')
        .' in order to obtain the optimal browsing experience. This machine is not the source of the error.'
    );
  }
  
  protected function _404() {
    header('Status: 404 Not Found'); header('HTTP/1.0 404 Not Found');
    $this->title = 'Post Not Found';
    $this->content = p(
        'Sorry, no post could be found. Either no post with the given id exists in the database, or its content file could not be found.'
    );
  }
  
  public function request($page) {
    $id = (int)$page;
    if (empty($id)) {
      $this->_400();
      return;
    }
    
    try {
      $this->post = Post::find($id);
    } catch (Exception $e) {
      unset($this->post);
    }
    if (empty($this->post)) {
      $this->_404();
      return;
    }
    
    $path = $this->post_path($this->post->directory, $this->post->time_first_published);
    if (!is_file($path.'content.php')) {
      $this->_404();
      return;
    }
    
    $this->request = '_index';
  }
}

?>