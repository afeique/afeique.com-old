<?

class Tag extends ActiveRecord\Model {
  static $has_many = array(
      array('post_tag_relations'),
      array('posts', 'through' => 'post_tag_relations')
  );
}

?>