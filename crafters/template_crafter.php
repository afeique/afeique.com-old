<?php

class template_crafter extends crafter {
  protected $title;
  protected $styles;
  
  // array containing urls of javascript files relative to JS_URL
  // loads all scripts via deferment
  protected $scripts;
  
  // array of css links
  protected $content;
  
  // whether or not to use the page template
  protected $use_template;
  
  // whether or not to use the commander
  protected $use_commander;
  
  // whether or not to use the commander header
  protected $use_header;
  
  // enables anti-robot sentries
  protected $no_robots;
  
  // contains db errors, prim. connect errors
  protected $db_error;
  
  // used to redirect using a meta http-equiv="refresh" with time META_REFRESH_TIME
  protected $meta_redirect;
  
  // posts per page
  protected $ppp;
  
  public function __construct() {
    parent::__construct();
  
    $this->title = 'Fipple';
    $this->content = o()->__(
        p('This page currently has no content. Try again some other time?')
    );
    
    $this->use_template = 1;
    $this->use_commander = 1;
    $this->use_header = 1;
    $this->no_robots = 0;
    
    $this->db_error = '';
    $this->db_access_level = 'public';
    $this->meta_redirect = '';
    $this->ppp = 30;
    
    $this->styles = array();
    $this->scripts = array();
    
    if ($this->logged_in())
      $this->db_connect($_SESSION['username'], $_SESSION['password']);
    else
      $this->db_connect();
  }
  
  protected function _index() {
  }
  
  protected function _browse() {
    $this->title = 'browse';
  
    $pagination = $this->paginate('Post','admin/index/', $page, $total_pages, $offset);
    if ($total_pages <= 1)
      $pagination = '&nbsp;';
  
    $posts = Post::find('all', array('limit' => $this->ppp, 'offset' => $offset, 'order' => 'time_last_modified desc'));
    $posts_html = o();
    if (!empty($posts)) {
      foreach ($posts as $post) {
        $posts_html->__($this->post_row($post));
      }
    } else {
      $posts_html->__(
          l('h2')->_c("span-8 prepend-8 append-8 text-center")->__(
              em('no posts to display')
          )
      );
    }
  
    $middot = ' &middot; ';
    $this->content = o()->__(
        p('Posts are displayed in descending order of date last modified.'),
        $posts_html,
        l('div')->_c('span-8 prepend-8 append-8 last text-center')->__($pagination)
    );
  }
  
  protected function _404() {
    header('Status: 404 Not Found'); header('HTTP/1.0 404 Not Found');
    $this->title = '404 Not Found';
    $this->content = p('The requested page does not seem to exist. Check the URL or try again some other time.');
  }
  
  public function craft() {
    $this->{$this->request}();
    
    if (!$this->use_template)
      return "{$this->content}";
    
    header('Content-Type: text/html; charset=utf-8');
    return "{$this->page_template()}";
  }

  protected function page_template() {
    if (!is_array($this->styles))
      throw error::expecting_array();
    if (!is_array($this->scripts))
      throw error::expecting_array();
    
    if ($this->logged_in())
      array_unshift($this->scripts, 'admin.js');
    array_unshift($this->scripts, 'jquery-1.7.1.min.js', 'jquery-ui-1.8.17.min.js', 'buttons.js');
    
    foreach ($this->scripts as $i => $script) {
      $this->scripts[$i] = '"'.$script.'"';
    }
    
    $this->meta_redirect = trim_slashes($this->meta_redirect);
    
    return o()->__(
        html5_doctype(),
        html(
            head(
                b('main/brains'),
                implode('', $this->styles),
                ($this->no_robots ? ll('meta')->_n('robots')->_('content','noindex, noarchive, nofollow') : ''),
                (!empty($this->meta_redirect) ? ll('meta')->_('http-equiv','refresh')->_('content', META_REFRESH_TIME.'; '.BASE_URL.$this->meta_redirect) : ''),
                title($this->title.' @ afeique.com'),
                l('script')->_t('text/javascript')->__('
                    // Add a script element as a child of the body
                    function deferred_js() {
                      var scripts = ['.implode(',', $this->scripts).']
                      for (var i=0; i<scripts.length; i++) {
                        var script = document.createElement("script");
                        script.src = "'.JS_URL.'"+scripts[i];
                        document.body.appendChild(script);
                      }
                    }
                
                    // Check for browser support of event handling capability
                    if (window.addEventListener)
                      window.addEventListener("load", deferred_js, false);
                    else if (window.attachEvent)
                      window.attachEvent("onload", deferred_js);
                    else 
                      window.onload = deferred_js;
                ')
            ),

            body(
                l('div')->_i('main-container')->_c('container')->__(
                    b('main/header'),
                    l('div')->_i('main-content')->_c('span-24')->__(
                        $this->commander($this->title),
                        $this->content
                    ),
                    b('main/footer')
                )
            )
        )
    );
  }
  
  protected function db_connect($admin_user=null, $admin_pass=null) {
    require BASE_PATH.'../mysql_credentials.php';
    
    if (isset($admin_user, $admin_pass)) {
      if (!is_string($admin_user))
        throw error::expecting_string();
      if (!is_string($admin_pass))
        throw error::expecting_string();
      
      $mysql_user = $admin_user;
      $mysql_pass = $admin_pass;
    } else {
      $mysql_user = $mysql_public_user;
      $mysql_pass = $mysql_public_pass;
    }
    
    $this->db_error = '';
    $db = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
    if ($db->connect_error) {
      if (!DEBUG)
        $this->db_error = 'Problem connecting to database.';
      else
        $this->db_error = '('.$db->connect_errno.') '.$db->connect_error;
    } else {
      $db->close();
      
      if (isset($admin_user, $admin_pass)) {
        $_SESSION[USERNAME] = $admin_user;
        $_SESSION[PASSWORD] = $admin_pass;
      }
      
      $GLOBALS['mysql'] = array($mysql_user, $mysql_pass, $mysql_host, $mysql_db);
      ActiveRecord\Config::initialize(function($config) {
        foreach (array('user','pass','host','db') as $i => $v)
          ${'mysql_'.$v} = $GLOBALS['mysql'][$i];
        
        $config->set_model_directory(BASE_PATH.'models');
        $config->set_connections(array(
            'auto' => "mysql://$mysql_user:$mysql_pass@$mysql_host/$mysql_db;charset=utf8",
        ));
        $config->set_default_connection('auto');
      });
      unset($GLOBALS['mysql']);
    }
  }
  
  protected function block_error($message) {
    return
    l('div')->_c('span-24 text-center')->__(
        l('p')->_c('error')->__($message)
    );
  }
  
  protected function inline_error($message) {
    return
    l('span')->_c('error')->__($message);
  }
  
  protected function paginate($class, $rel_url, &$page, &$total_pages, &$offset) {
    $total = (int)($class::count());
    $total_pages = (int)ceil($total / $this->ppp);
    
    $offset = 0;
    if (isset($GLOBALS[EXTRA]['page']))
      $page = (int)$GLOBALS[EXTRA]['page'];
    if (empty($page))
      $page = 1;
    
    $page_buttons = o();
    $e = 2; // epsilon
    if ($total_pages > 1) {
      if (!empty($page) && $page > 1)
        $offset = $page * $this->ppp;
      
      $ellipses = 0;
      for ($i=1; $i<=$total_pages; $i++) {
        if ($i == 1 || $i == $total_pages || $i >= $page-$e || $i <= $page+$e) {
          if ($i != $page)
            $page_link = a_link(BASE_URL.$rel_url.'page/'.$i, $i);
          else
            $page_link = $i;
          $page_buttons->__(l('li')->_c('page-button')->__($page_link));
        } else {
          if (($page-$e) > 1 && $i > 1 && $i < ($page-$e)) {
            $page_buttons->__(li('…'));
          } elseif (($page+$e) < $total_pages && $i > ($pages+$e) && $i < $total_pages) {
            $page_buttons->__(li('…'));
          }
        }
      }
    }
    return l('ul')->_i('pagination')->__($page_buttons);
  }
  
  protected function load_model($model) {
    if (!is_string($model))
      throw error::expecting_string();
    
    require_once MODELS_PATH.strtolower($model).'.php';
  }
  
  protected function commander() {
    return !$this->use_commander ? '' :
    l('div')->_c('span-24')->__(
        h1(($this->use_header ? $this->title : '&nbsp;'))->_c('span-16'),
        l('nav')->_i('commander')->_c('span-8 last text-right')->__($this->commands())
    );
  }
  
  protected function commands() {
    if ($this->logged_in()) {
      return l('ul')->__(
          li(l_link('admin/publish','publish post')->_c('publish-button')->_('target','_blank')->_('title','publish post')),
          li(l_link('admin/logout','logout')->_c('logout-button')->_('title','logout'))
      );
    } else {
      return '&nbsp;';
    }
  }
  
  protected function post_row(Post $post) {
    $path = '';
    $edit_title = '';
    $edit_tags = '';
    $delete_post = '';
    if ($this->logged_in()) {
      $path = l('li')->_c('post-path')->__(
          strong('path:'),' ',
          htmlentities(rtrim($this->post_path('', $post->time_first_published),'/').'/'),
          l('span')->_c('post-directory')->__(htmlentities($post->directory))
      );
      $edit_title = a_link('javascript: void(0)', 'edit title')->_c('edit-title edit-button')->_('title','edit title');
      $edit_tags = a_link('javascript: void(0)', 'edit tags')->_c('edit-tags edit-button')->_('title','edit tags');
      $delete_post = l('div')->_c('delete-post')->__(
          a_link('javascript: void(0)', 'delete post')->_c('x-button')->_('title','delete post')
      );
    }
  
    $last_modified = '';
    if ($post->time_first_published != $post->time_last_modified)
      $last_modified = li(strong('last modified:'),' ', date(POST_DATE_FORMAT, $post->time_last_modified))->_c('post-last-modified');
    
    $tags_html = l('ul')->_c('tags-list');
    $tags = explode(' ', $post->tags);
    $num_tags = count($tags);
    for ($i=0; $i<$num_tags; $i++) {
      $li = l('li');
      if ($i == $num_tags-1)
        $li->_c('last');
      $li->__( l_link('tag-search/'.urlencode($tags[$i]), htmlentities($tags[$i])) );
      $tags_html->__($li);
    }
    
    return
    l('div')->_i('post-'.$post->id.'-row')->_c('span-24 post-row')->__(
        l('div')->_c('span-24 post-title')->__(
            h2(
                l_link('view/'.$post->id, htmlentities($post->title))->_('title','link to post')->_('target','_blank')
            ),
            $delete_post,
            $edit_title
        ),
        l('div')->_c('span-24 post-tags')->__(
            strong('tagged'),' ', $tags_html,' ', $edit_tags
        ),
        l('div')->_c('span-24 post-description')->__(
            p(htmlentities($post->description))
        ),
        l('div')->_c('span-24 post-meta')->__(
            l('ul')->__(
                $path,
                li(strong('published:'),' ', date(POST_DATE_FORMAT, $post->time_first_published)),
                $last_modified
            )
        )
    );
  }
  
  protected function post_path($directory, $time_first_published) {
    $t = $time_first_published;
    return PUBLISHED_POSTS_DIR.date('Y', $t).'/'.date('n', $t).'/'.date('j', $t).'/'.$directory.'/';
  }
  
  protected function logged_in() {
    return (isset($_SESSION[USERNAME], $_SESSION[PASSWORD]));
  }
}

?>