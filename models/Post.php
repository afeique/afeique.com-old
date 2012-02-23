<?php

class Post extends ActiveRecord\Model {
  static $has_many = array(
      array('post_tag_relations'),
      array('tags', 'through' => 'post_tag_relations')
  );
  static $has_one = array(
      array('fulltext')
  );
  
  public function set_timestamps() {
    $now = time();
    
    if (isset($old_last_modified))
      $this->updated_at = $now;
  
    if (isset($this->created_at) && $this->is_new_record())
      $this->created_at = $now;
  }
}

?>