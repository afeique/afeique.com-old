<?

class Tag extends ActiveRecord\Model {
  static $has_many = array(
      array('post_tag_relations', 'class_name' => 'Post_Tag_Relation'),
      array('posts', 'through' => 'post_tag_relations')
  );
}

?>