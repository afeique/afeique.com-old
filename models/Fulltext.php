<?php

class Fulltext extends ActiveRecord\Model {
  static $belongs_to = array(
      array('post')    
  );
}

?>