<?php

class PostFulltext extends ActiveRecord\Model {
  static $belongs_to = array(
      array('post')    
  );
}

?>