<?php

class root_crafter extends template_crafter {
  protected function _index() {
    $this->title = 'home';
    $this->ppp = 1;
    $this->post_can_override_heading = 1;
    
    $page = $this->get_page();
    $posts = Post::find('all', array('order' => 'id desc', 'limit' => $this->ppp, 'offset' => ($page-1)*$this->ppp));
    $this->content = $this->read_posts($posts);
  }
  
  protected function _source() {
    $this->title = 'site source';
    $this->content = b('content/source');
  }
  
  protected function _contact() {
    $this->title = 'contact card';
    $this->content = b('content/contact');
  }
}

?>