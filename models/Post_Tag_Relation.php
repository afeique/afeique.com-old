<?

class Post_Tag_Relation extends ActiveRecord\Model {
  static $belongs_to = array(
      array('post'),
      array('tag')    
  );
}