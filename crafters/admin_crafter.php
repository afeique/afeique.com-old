<?php

class admin_crafter extends template_crafter {
  public function __construct() {
    parent::__construct();
  }
  
  protected function _index() {
    $this->_browse();
  }
  
  protected function _publish() {
    $this->title = 'publish post';
    $this->use_header = 0;
    
    $errors = array();
    
    $vars = array('title','tags','description','directory');
    $title = $tags = $description = $directory = '';
    foreach ($vars as $var) {
      $$var = isset($_POST[$var]) ? $_POST[$var] : '';
      if (!empty($_POST)) {
        $errors[$var] = validate::string($$var)->trim()->not_empty()->max_length(250);
        if ($var == 'directory') {
          $unpub = BASE_PATH.UNPUBLISHED_POSTS_DIR;
          $errors[$var]->trim_slashes()->is_dir($unpub)->is_file($unpub, '/content.php');
        }
        
        $errors[$var] = $errors[$var]->go();
      } else
        $errors[$var] = '';
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
          l('div')->_c('span-9 prepend-8 append-7 last')->__(
              h1($this->title),
              l('label')->_f('post-title')->__('title', l('span')->_c('chars-left')->__('250')),
              ll('input')->_t('text')->_n('title')->_i('post-title')->_v($title),
              $errors['title'],
              l('label')->_f('post-tags')->__('space-separated tags', l('span')->_c('chars-left')->__('250')),
              ll('input')->_t('text')->_n('tags')->_i('post-tags')->_v($tags),
              $errors['tags'],
              l('label')->_f('post-description')->__('description', l('span')->_c('chars-left')->__('250')),
              l('textarea')->_n('description')->_i('post-description')->__($description),
              $errors['description'],
              l('label')->_f('post-directory')->__(trim_slashes(UNPUBLISHED_POSTS_DIR).' directory'),
              ll('input')->_t('text')->_n('directory')->_i('post-directory')->_v($directory),
              $errors['directory']
          ),
          l('div')->_c('span-8 prepend-8 append-8 last text-center')->__(
              ll('input')->_t('submit')->_n('submit')->_c('publish-button')->_v('publish')
          )
      );
    } else {
      $directory = trim_slashes($directory);
      
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
      
      $unpub = UNPUBLISHED_POSTS_PATH.$directory;
      exec("mv $unpub/ $pub/");
      
      $time = time();
      
      $post_data = array(
          'title' => $title,
          'tags' => $tags,
          'description' => $description,
          'path' => str_replace(BASE_PATH.PUBLISHED_POSTS_DIR, '', $pub.$directory.'/'),
          'time_first_published' => $time,
          'time_last_modified' => $time    
      );
      
      Post::create($post_data);
      
      $this->content = $this->notification(
          p('The post has been successfully published. Feel free to close this window.'), 
          'post successfully published'
      );
    }
  }
  
  protected function notification($notification, $header=null) {
    return
    l('div')->_c('span-12 prepend-6 append-6 last')->__(
        isset($header) ? h1($header) : '',
        $notification
    );
  }
  
  protected function _login() {
    $this->title = 'login';
    $this->use_commander = 0;
    
    $u = USERNAME;
    $p = PASSWORD;
    
    $$u = isset($_POST[$u]) ? $_POST[$u] : '';
    $$p = isset($_POST[$p]) ? $_POST[$p] : '';
    
    if (isset($_POST[$u], $_POST[$p])) {
      $this->db_connect($$u, $$p);
      
      if (empty($this->db_error)) {
        $_SESSION[USERNAME] = $$u;
        $_SESSION[PASSWORD] = $$p;
      }
    }
    
    if ($this->logged_in()) {
      $this->title = 'logged in';
      $this->meta_redirect = 'admin/index';
      $this->content = $this->notification(
          p(
              'You have successfully logged in. This page will reconstruct in '.META_REFRESH_TIME.' seconds. ',
              l_link('admin/index', 'Speed it up?')
          ),
          'logged in'
      );
    
      return 1;
    }
    
    $this->content = l('form')->_c('small-form')->_a('admin/login')->_m('post')->__(
        $this->db_error,
        l('div')->_c('span-9 prepend-8 append-7 last')->_i('login')->__(
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
   * Small note: we don't need to check if the user is logged in
   * The craft() method ensures that he/she is.
   */
  protected function _logout() {
    $this->title = 'logged out';
    $this->use_commander = 0;
    $this->meta_redirect = 'admin/';
    
    unset($_SESSION[USERNAME], $_SESSION[PASSWORD]);
    
    $this->content = $this->notification(
        p(
            'You have successfully logged out. This page will self-destruct in '.META_REFRESH_TIME.' seconds. ',
            l_link('admin/index', 'Explode now?')
        ),
        'logged out'
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