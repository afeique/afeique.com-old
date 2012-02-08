<?php

class root_crafter extends template_crafter {
  protected function _index() {
    $this->title = 'home';
    try {
      $posts = Post::find('all', array('order' => 'id desc'));
    } catch (Exception $e) {
      unset($posts);
    }
    if (!empty($posts)) {
      $this->content = $this->view_posts($posts);
    } else {
      $this->content = l('h2')->_c('text-center')->__(
          em('no posts to display')
      );
    }
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