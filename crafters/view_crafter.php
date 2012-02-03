<?php

class view_crafter extends template_crafter {
  protected $post;
  
  public function __construct() {
    parent::__construct();
    
    // unlike other pages, don't default to displaying the index but a 404
    // only display the index if a valid post id is provided
    $this->request = '_404';
  }
  
  protected function _index() {
    $this->title = $this->post->title;
    
    ob_start();
    require $this->post_path($this->post->directory, $this->post->time_first_published).'content.php';
    $this->content = ob_get_clean();
    
    $this->use_template = (isset($no_template) ? 0 : 1);
    $this->use_header = (isset($no_header) ? 0 : 1);
    
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
  
  public function request($page) {
    if (!is_string($page))
      throw error::expecting_string();
    
    $id = (int)$page;
    if (!empty($id)) {
      $this->post = Post::find($id);
      if (!empty($this->post))
        $this->request = '_index';
    }
  }
}

?>