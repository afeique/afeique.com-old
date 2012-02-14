<?php

class PostTagRelation extends ActiveRecord\Model {
  static $belongs_to = array(
      array('post'),
      array('tag')    
  );
}

?>