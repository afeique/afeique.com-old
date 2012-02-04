<?php

class root_crafter extends template_crafter {
  protected function _index() {
    $this->title = 'home';
    try {
      $r = Post::find('all', array('limit' => 1, 'order' => 'id desc'));
      $post = $r[0];
    } catch (Exception $e) {
      unset($post);
    }
    if (empty($post)) {
      $this->content = l('h2')->_c('text-center')->__(
          em('no posts to display')
      );
    } else {
      $this->content = $this->view_post($post);
    }
  }
}

?>