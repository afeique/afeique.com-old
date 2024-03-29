<?php

class admin_crafter extends template_crafter {
  public function __construct() {
    parent::__construct();
  }
  
  protected function _index() {
    header('Location: '.BASE_URL.'browse');
  }
  
  protected function _publish() {
    $this->title = 'publish post';
    $this->use_heading = 0;
    
    $errors = array();
    
    $fields = array('title','tags','description','directory');
    $title = $tags = $description = $directory = '';
    foreach ($fields as $field) {
      if (!empty($_POST)) {
        $validate_type = 'string';
        if ($field == 'tags') {
          $_POST['tags'] = explode(',', $_POST['tags']);
          $tags = validate::ray($_POST['tags']);
        } else
          $$field = validate::string($_POST[$field]);
        
        $$field->trim()->not_empty()->spacify()->max_length(250);
        
        if ($field == 'directory') {
          $unpub = UNPUBLISHED_POSTS_PATH;
          $errors['directory'] = $directory->trim_slashes()->is_dir($unpub)->is_file($unpub,'/content.php');
        }
        
        $errors[$field] = $$field->errors();
      } else
        $errors[$field] = '';
    }
    
    $error_check = implode('', $errors);
    if (empty($_POST) || !empty($error_check)) {
      $block_error = '';
      if (!empty($error_check))
        $block_error = $this->block_error('There are problems with your inputs. Check the error messages.');
      
      foreach ($errors as $key => $message) {
        if (!empty($message))
          $errors[$key] = $this->inline_error($message);
        else
          $errors[$key] = '';
      }
      
      $this->content = l('form')->_i('publish-post-form')->_c('small-form')->_a('admin/publish')->_m('post')->__(
          $block_error,
          l('div')->_c('span-9 prepend-7 append-8 last')->__(
              h1($this->title),
              l('div')->_c('span-24')->__(
                  l('label')->_f('post-title')->__('title', l('span')->_c('chars-left')->__('250')),
                  ll('input')->_t('text')->_n('title')->_i('post-title')->_v(htmlentities($title)),
                  $errors['title']
              ),
              l('div')->_c('span-24')->__(
                  l('label')->_f('post-tags')->__('tags, comma separated'),
                  ll('input')->_t('text')->_n('tags')->_i('post-tags')->_v(htmlentities($tags)),
                  $errors['tags']
              ),
              l('div')->_c('span-24')->__(
                  l('label')->_f('post-description')->__('description', l('span')->_c('chars-left')->__('250')),
                  l('textarea')->_n('description')->_i('post-description')->__(htmlentities($description)),
                  $errors['description']
              ),
              l('div')->_c('span-24')->__(
                  l('label')->_f('post-directory')->__(
                      trim(UNPUBLISHED_POSTS_DIR,'/').' directory',
                      l('span')->_c('chars-left')->__('250')
                  ),
                  ll('input')->_t('text')->_n('directory')->_i('post-directory')->_v(htmlentities($directory)),
                  $errors['directory']
              )
          ),
          l('div')->_c('span-8 prepend-8 append-8 last text-center')->__(
              ll('input')->_t('submit')->_n('submit')->_c('publish-button')->_v('publish')
          )
      );
    } else {
      $pub = PUBLISHED_POSTS_PATH;
      if (!is_dir($pub))
        mkdir($pub);
      $pub .= date('Y').'/';
      if (!is_dir($pub))
        mkdir($pub);
      $pub .= date('n').'/';
      if (!is_dir($pub))
        mkdir($pub);
      $pub .= date('j').'/';
      if (!is_dir($pub))
        mkdir($pub);
      
      $unpub = escapeshellarg(UNPUBLISHED_POSTS_PATH.$directory);
      exec("mv $unpub $pub");
      
      $time = time();
      
      $post = array(
          'directory' => $directory,
      );
      
      $post = Post::create($post);
      $text = $this->prepare_post_text($this->get_post_content($post));
      
      // set post fulltext
      $post->fulltext->title = $title;
      $post->fulltext->description = $description;
      $post->fulltext->content = $text;
      
      // save post fulltext
      $post->save();
      
      // create tags if they don't exist and create tag relations
      $post->tags = array();
      $tags = $tags->shine();
      foreach ($tags as $value) {
        $tag = Tag::find_by_value($value);
        if (!isset($tag))
          $tag = Tag::create(array('value' => $value));
        
        PostTagRelation::create(array('post_id' => $post->id, 'tag_id' => $tag->id));
      }
      
      $this->content = $this->notification(
          p('The post has been successfully published. Feel free to close this window.'), 
          'post successfully published'
      );
    }
  }
  
  protected function _login() {
    $this->title = 'login';
    $this->use_heading = 0;
    $this->use_heading_commander = 0;
    
    $u = USERNAME;
    $p = PASSWORD;
    $fields = array($u, $p);
    $errors = array();
    foreach ($fields as $field) {
      $errors[$field] = '';
      if (isset($_POST[$field])) {
        $$field = validate::string($_POST[$field])->trim()->not_empty()->spacify();
        $errors[$field] = $$field->errors();
        $$field = "{$$field}";
      } else
        $$field = '';
    }
    
    $db_error = '';
    if (isset($_POST[$u], $_POST[$p])) {
      $this->db_connect($$u, $$p);
      if (empty($this->db_error)) {
        $_SESSION[USERNAME] = $$u;
        $_SESSION[PASSWORD] = $$p;
      } else
        $db_error = $this->block_error($this->db_error);
    }
    
    if ($this->logged_in()) {
      $this->title = 'logged in';
      $this->meta_redirect = 'browse';
      $this->content = $this->notification(
          p('You have successfully logged in. This page will reconstruct in '.META_REFRESH_TIME.' seconds. ',
              l_link('admin/index','Speed it up?')),
          'logged in'
      );
      
      return;
    }
    
    $this->content = l('form')->_c('small-form')->_a('admin/login')->_m('post')->__(
        $db_error,
        l('div')->_c('span-9 prepend-7 append-8 last')->_i('login')->__(
            h1('admin login'),
            l('label')->_f("$u-input")->__($u),
            l('div')->_c('text-center')->__(
                l('input')->_t('text')->_n($u)->_i("$u-input")->_v($$u)
            ),
            l('label')->_f("$p-input")->__($p),
            l('div')->_c('text-center')->__(
                l('input')->_t('password')->_n($p)->_i("$p-input")->_v($$p)
            )
        ),
        l('div')->_c('span-8 prepend-8 append-8 text-center')->__(
            l('input')->_t('submit')->_n('submit')->_c('login-button')->_v('login')
        )
    );
  }
  
  /**
   * small note: we don't need to check if the user is logged in
   * the craft() method ensures that he/she is.
   */
  protected function _logout() {
    $this->title = 'logged out';
    $this->use_heading = 0;
    $this->use_heading_commander = 0;
    $this->meta_redirect = 'admin/';
    
    unset($_SESSION[USERNAME], $_SESSION[PASSWORD]);
    
    $this->content = $this->notification(
        p('You have successfully logged out. This page will self-destruct in '.META_REFRESH_TIME.' seconds. ',
            l_link('admin/index','Explode now?')),
        'logged out'
    );
  }
  
  protected function _update() {
    $this->use_template = 0;
    $this->content = '';
    
    $message = $this->updater_prechecks($id, $field, $post);
    if (!empty($message)) {
      $this->content = $this->json_error($message);
      return;
    }
    
    $_GET[$field] = utf8_encode(urldecode($_GET[$field]));
    if ($field == 'tags') {
      $tags = validate::ray(explode(',', $_GET['tags']));
    } else
      $$field = validate::string($_GET[$field]);
    
    $$field->trim()->not_empty()->spacify()->max_length(250);
    
    $errors = $$field->errors();
    if (empty($errors)) {
      if ($field == 'tags') {
        $new_tags = $tags->shine();
        
        // delete old tag relations that are no longer used
        foreach ($post->tags as $i => $old_tag) {
          if (!in_array($old_tag->value, $new_tags)) {
            unset($post->tags[$i]); //
            $relation = PostTagRelation::find('first', array('post_id' => $post->id, 'tag_id' => $old_tag->id));
            $relation->delete();
          }
        }
        
        // create an array of old tag values
        $old_tags = array();
        foreach ($post->tags as $old_tag)
          $old_tags[] = $old_tag->value;
        
        // create tags and tag relations if necessary
        foreach ($new_tags as $new_tag) {
          if (!in_array($new_tag, $old_tags)) {
            $tag = Tag::find_by_value($new_tag);
            if (!isset($tag))
              $tag = Tag::create(array('value' => $new_tag));
            
            $relation = PostTagRelation::find('first', array('post_id' => $id, 'tag_id' => $tag->id));
            if (!isset($relation))
              PostTagRelation::create(array('post_id' => $id, 'tag_id' => $tag->id));
          }
        }
      } elseif ($field == 'directory') {
        $current_path = escapeshellarg(rtrim($this->post_path($post->directory, $post->time_first_published),'/'));
        $new_path = escapeshellarg(rtrim($this->post_path($directory, $post->time_first_published),'/'));
        
        exec("mv $current_path $new_path");
        
        $prepend = rtrim($this->post_path('', $post->time_first_published),'/').'/';
        $directory->trim_slashes()->is_dir($prepend)->is_file($prepend,'/content.php');
        
        $errors = $directory->errors();
        if (!empty($errors)) {
          $message = 'problem moving file to new directory';
          $this->content = $this->json_error($message);
          return;
        }
        
        $post->directory = $directory;
        $post->save();
      } elseif (in_array($field, array('title','description'))) {
        $post->fulltext->$field = $$field;
        $post->fulltext->save();
      }
    } else {
      if (!DEBUG) { 
        header('Status: 400 Bad Request'); header('HTTP/1.0 400 Bad Request');
      }
      
      $this->content = $this->json_error($message);
      return;
    }
  
    if ($field != 'tags') {
      $this->content = json_encode(array($field => "{$$field}"));
    } else {
      $this->content = json_encode(array($field => $tags->shine()));
    }
    
  }
  
  protected function json_error($message) {
    return json_encode(array('error' => $message));
  }
  
  protected function updater_prechecks(&$id, &$field, &$post) {
    /**
     * most of these errors really only apply for debugging.
     * a stable production build should never encounter them.
     */
    if (!isset($GLOBALS[EXTRA][1])) {
      if (!DEBUG) {
        header('Status: 400 Bad Request'); header('HTTP/1.0 400 Bad Request');
      }
      return 'field to update not specified in url';
    }
    
    $field = $GLOBALS[EXTRA][1];
    $valid_fields = array('title','tags','description','directory');
    if (!in_array($field, $valid_fields)) {
      if (!DEBUG) {
        header('Status: 400 Bad Request'); header('HTTP/1.0 400 Bad Request');
      }
      return 'invalid field "'.$field.'" specified in url; valid fields are: '.implode(', ', $valid_fields);
    }
    
    if (!isset($GLOBALS[EXTRA][2])) {
      if (!DEBUG) {
        header('Status: 400 Bad Request'); header('HTTP/1.0 400 Bad Request');
      }
      return 'no post id specified in url';
    }
    
    $id = (int)$GLOBALS[EXTRA][2];
    if (empty($id)) {
      if (!DEBUG) {
        header('Status: 400 Bad Request'); header('HTTP/1.0 400 Bad Request');
      }
      return 'invalid post id "'.$id.'" specified in url; require nonzero positive integer';
    }
    
    $post = Post::find($id);
    if (empty($post)) {
      if (!DEBUG) {
        header('Status: 404 Not Found'); header('HTTP/1.0 404 Not Found');
      }
      return 'post with id "'.$id.'" not found';
    }
    
    if (!isset($_GET[$field])) {
      if (!DEBUG) {
        header('Status: 400 Bad Request'); header('HTTP/1.0 400 Bad Request');
      }
      return 'no data given for '.$field.' update via GET';
    }
  }
  
  protected function _compress_assets() {
    $this->title = 'compress assets';
    $this->use_heading = 0;
    $this->use_heading_commander = 0;
    $this->content = $this->notification(
        p('JavaScript assets have been packed and meshed; CSS assets have been minified!'),
        'success!'
    );
    
    /**
     * pack and mesh scripts
     */
    require LIBS_PATH.'JavaScriptPacker.php';
    
    // grab contents of base scripts
    $scripts = '';
    foreach ($GLOBALS[BASE_JS] as $filename) 
      $scripts .= file_get_contents(JS_PATH.$filename);
    
    // initialize packer for base scripts, and create public mesh
    $packer = new JavaScriptPacker($scripts);
    $mesh = $packer->pack();
    
    // write mesh into public meshed file
    $meshfile = fopen(JS_PATH.PUBLIC_MESHED_JS,'w+');
    fwrite($meshfile, $mesh);
    fclose($meshfile);
    
    // append admin script, init new packer, and create admin mesh
    $scripts .= file_get_contents(JS_PATH.ADMIN_JS);
    $packer = new JavaScriptPacker($scripts);
    $mesh = $packer->pack();
    
    // write admin mesh to admin meshed file
    $meshfile = fopen(JS_PATH.ADMIN_MESHED_JS,'w+');
    fwrite($meshfile, $mesh);
    fclose($meshfile);
    
    /**
     * minify css
     */
    require LIBS_PATH.'cssmin.php';
    
    // minify base css
    $minfile = fopen(CSS_PATH.BASE_CSS.'.min.css','w+');
    fwrite($minfile, CssMin::minify(file_get_contents(CSS_PATH.BASE_CSS.'.css')));
    fclose($minfile);
    
    // minify admin css
    $minfile = fopen(CSS_PATH.ADMIN_CSS.'.min.css','w+');
    fwrite($minfile, CssMin::minify(file_get_contents(CSS_PATH.ADMIN_CSS.'.css')));
    fclose($minfile);
  }
  
  protected function notification($notification, $heading=null) {
    return
    l('div')->_c('span-12 prepend-6 append-6 last')->__(
        isset($heading) ? h1($heading) : '',
        $notification
    );
  }
  
  public function craft() {
    if (!$this->logged_in()) {
      $this->request('login');
    }
    
    $this->no_robots = 1;
    return parent::craft();
  }
}

?>