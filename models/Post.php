<?php

class Post extends ActiveRecord\Model {
  static $has_many = array(
      array('post_tag_relations'),
      array('tags', 'through' => 'post_tag_relations'),
  );
  static $has_one = array(
      array('post_fulltext')
  );
}

?>