<?php

class Post extends ActiveRecord\Model {
  static $has_many = array(
      array('post_tag_relations', 'class_name' => 'PostTagRelation'),
      array('tags', 'class_name' => 'PostTagRelation')
  );
}

?>