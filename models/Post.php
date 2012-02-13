<?php

class Post extends ActiveRecord\Model {
  public function get_tags() {
    $tags = Post::find('all', array(
        'select' => 't.id as id, t.tag as tag',
        'conditions' => 'post.id = '.$this->id,
        'join' => 'RIGHT JOIN post_tag_rel ptr ON(ptr.post_id = post.id) RIGHT JOIN tags t ON(t.id = ptr.tag_id)'
        ));
    
    $this->tags = array();
    foreach ($tags as $tag) {
      $this->tag[$tag->id] = $tag->tag;
    }
  }
}

?>