<?php

class base_crafter extends template_crafter {
  protected function _index() {
    $this->title = 'home';
    $this->meta_description = "Afeique Sheikh's home on the web.";
    $this->ppp = 1;
    $this->post_can_override_heading = 1;
    
    $page = $this->get_page();
    $posts = Post::find('all', array('order' => 'id desc', 'limit' => $this->ppp, 'offset' => ($page-1)*$this->ppp));
    $this->content = $this->read_posts($posts);
  }
  
  protected function _browse() {
    $this->title = 'browse posts';
    $this->meta_description = 'Browse posts in descending order of date published.';
  
    $page = $this->get_page();
    $posts = Post::find('all', array('order' => 'id desc', 'limit' => $this->ppp, 'offset' => ($page-1)*$this->ppp));
  
    $page_bar = $this->page_bar(Post::count(), 'browse');
    $posts_html = $this->list_posts($posts);
  
    $middot = ' &middot; ';
    $this->content = o(
        p('Posts are displayed in descending order of date and time published.'),
        $posts_html,
        $page_bar
    );
  }
  
  protected function _source() {
    $this->title = 'site source';
    $this->content = b('content/source');
    $this->meta_description = 'Overview of the source-code and framework behind afeique.com.';
  }
  
  protected function _about() {
    $this->title = 'about';
    $this->content = b('content/about');
    $this->meta_description = 'Who is Afeique Sheikh?';
  }
}

?>