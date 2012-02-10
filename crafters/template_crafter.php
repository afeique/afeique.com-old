<?php

class template_crafter extends crafter {
  // page title, also sets page heading (below) when only the title is set
  protected $title;
  
  // the page heading
  protected $heading;
  
  // additional stylesheets to use
  protected $styles;
  
  /**
   * arrays containing relative paths of all javascript files (relative to JS_URL)
   * scripts are loaded using <script src="..."></script>
   * 
   * originally used page deferment as per google pagespeed's recommendations, but
   * that caused race conditions with jquery that led to pages sometimes rendering
   * jquery ui elements incorrectly
   */
  protected $scripts;
  
  protected $content;
  
  // whether or not to use the page template
  protected $use_template;
  
  // whether or not to use the commander
  protected $use_commander;
  
  // whether or not to use the commander heading
  protected $use_heading;
  
  // whether or not a post can override page heading
  // also used by the commander to detect whether a page _has_ overridden the heading
  protected $post_can_override_heading;
  
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
    $this->heading = '';
    
    $this->content = o(
        p('This page currently has no content. Try again some other time?')
    );
    
    $this->use_template = 1;
    $this->use_commander = 1;
    $this->use_heading = 1;
    
    $this->post_can_override_heading = 1;
    
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
    $this->title = 'browse posts';
    
    $posts = Post::find('all', array('order' => 'id desc'));
    $posts_html = o();
    if (!empty($posts)) {
      foreach ($posts as $post) {
        $posts_html->__($this->post_row($post));
      }
    } else {
      $posts_html->__(
          l('h2')->_c('text-center')->__(
              em('no posts to display')
          )
      );
    }
  
    $middot = ' &middot; ';
    $this->content = o(
        p('Posts are displayed in descending order of date last modified.'),
        $posts_html
    );
  }
  
  protected function _404() {
    header('Status: 404 Not Found'); header('HTTP/1.0 404 Not Found');
    $this->title = '404 Not Found';
    $this->content = p('The requested page does not seem to exist. Check the URL or try again some other time.');
  }
  
  public function craft() {
    $this->{$this->request}();
    
    if (empty($this->heading))
      $this->heading = $this->title;
    
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
    
    array_unshift($this->styles, (DEBUG ? 'main.css' : 'main.min.css'));
    if ($this->logged_in()) {
      array_unshift($this->styles, 'jquery-ui-lightness/jquery-ui-1.8.17.min.css');
      array_unshift($this->scripts,'jquery-1.7.1.min.js','jquery-ui-1.8.17.min.js',
          (DEBUG ? 'buttons.js' : 'buttons.min.js'), 
          (DEBUG ? 'admin.js' : 'admin.min.js')
      );
    }
    
    foreach ($this->styles as $i => $stylesheet) {
      $this->styles[$i] = css_link($stylesheet);
    }
    foreach ($this->scripts as $i => $script) {
      $this->scripts[$i] = script_src($script);
    }
    
    /*
    foreach ($this->scripts as $i => $script) {
      $this->scripts[$i] = '"'.$script.'"';
    }
    */
    
    $this->meta_redirect = trim($this->meta_redirect,'/');
    
    return o(
        html5_doctype(),
        html(
            head(
                b('main/brains'),
                implode('', $this->styles),
                ($this->no_robots ? ll('meta')->_n('robots')->_('content','noindex, noarchive, nofollow') : ''),
                (!empty($this->meta_redirect) ? ll('meta')->_('http-equiv','refresh')->_('content', META_REFRESH_TIME.'; '.BASE_URL.$this->meta_redirect) : ''),
                title($this->title.' @ afeique.com'),
                implode('', $this->scripts)
                /*
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
                */
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
    $db = @new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
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
  
  protected function load_model($model) {
    if (!is_string($model))
      throw error::expecting_string();
    
    require_once MODELS_PATH.strtolower($model).'.php';
  }
  
  protected function commander() {
    // if commander is called without a heading set, use the page title for the heading
    if (empty($this->heading))
      $this->heading = $this->title;
    
    // if a post can no longer override the heading,
    // the header has already been overridden
    $post_title = '';
    $heading_id = '';
    $heading_commanders = '';
    if (!$this->post_can_override_heading) {
      $post_title = ' post-title';
      $this->post_commanders($edit_title, $edit_tags, $delete_post);
      $heading_commanders = o($edit_title, $delete_post);
      
      // parse post id from heading
      $heading = "{$this->heading}";
      preg_match('/href="([^"]+)"/', $heading, $match);
      $href = $match[1];
      preg_match('/(\d+)$/', $href, $match);
      $post_id = (int)$match[1];
      $heading_id = 'post-'.$post_id.'-title';
    }
    
    return !$this->use_commander ? '' :
    l('div')->_c('span-24'.$post_title)->__(
        l('div')->_i($heading_id)->_c('span-16'.$post_title)->__(
            h1(($this->use_heading ? $this->heading : '&nbsp;'))->_c($post_title),
            $heading_commanders
        ),
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
  
  protected function post_commanders(&$edit_title, &$edit_tags, &$delete_post) {
    $edit_title = '';
    $edit_tags = '';
    $delete_post = '';
    if ($this->logged_in()) {
      $edit_title = a_link('javascript: void(0)', 'edit title')->_c('edit-title edit-button')->_('title','edit title');
      $edit_tags = a_link('javascript: void(0)', 'edit tags')->_c('edit-tags edit-button')->_('title','edit tags');
      /*
       $delete_post = l('div')->_c('delete-post')->__(
           a_link('javascript: void(0)', 'delete post')->_c('x-button')->_('title','delete post')
       );
      */
    }
  }
  
  protected function post_template(Post $post, $heading, $content) {
    $this->post_commanders($edit_title, $edit_tags, $delete_post);
    
    $path = '';
    if ($this->logged_in()) {
      $path = l('li')->_c('post-path')->__(
          strong('path:'),' ',
          htmlentities(rtrim($this->post_path('', $post->time_first_published),'/').'/'),
          l('span')->_i('post-'.$post->id.'-directory')->_c('post-directory')->__(htmlentities($post->directory))
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
      //$li->__( l_link('tag-search/'.urlencode($tags[$i]), htmlentities($tags[$i])) );
      $li->__(htmlentities($tags[$i]));
      $tags_html->__($li);
    }
  
    return
    l('div')->_i('post-'.$post->id.'-row')->_c('span-24 post-row')->__(
        l('div')->_i('post-'.$post->id.'-title')->_c('span-24 post-title')->__(
            $heading,
            $delete_post,
            $edit_title
        ),
        l('div')->_i('post-'.$post->id.'-tags')->_c('span-24 post-tags')->__(
            strong('tagged'),' ', $tags_html,' ', $edit_tags
        ),
        $content,
        l('div')->_c('span-24 post-meta')->__(
            l('ul')->__(
                $path,
                li(strong('published:'),' ', date(POST_DATE_FORMAT, $post->time_first_published)),
                $last_modified
            )
        )
    );
  }
  
  protected function post_row(Post $post) {
    $heading = 
    l('h2')->_c('post-title')->__(
        l_link('view/'.$post->id, htmlentities($post->title))->_('title','link to post')->_('target','_blank')
    );
    $content = 
    l('div')->_i('post-'.$post->id.'-description')->_c('span-24 post-description')->__(
        p(htmlentities($post->description))
    );
    
    return $this->post_template($post, $heading, $content);
  }
  
  protected function view_posts(array $posts) {
    $posts_html = o();
    foreach ($posts as $post) {
      $content = $this->get_post_content($post);
      
      if (!$this->post_can_override_heading) {
        $heading = 
        l('h1')->_c('post-title')->__(
            l_link('view/'.$post->id, htmlentities($post->title))->_('title','link to post')->_('target','_blank')
        );
      } else {
        $heading = '';
        $this->heading = l_link('view/'.$post->id, htmlentities($post->title))->_('title','link to post')->_('target','_blank');
        $this->post_can_override_heading = 0;
      }
      
      $content =
      l('div')->_c('span-24 post-content')->__(
          $content
      );
    
      $posts_html->__($this->post_template($post, $heading, $content));
    }
    
    return $posts_html;
  }
  
  protected function get_post_content(Post $post) {
    ob_start();
    require $this->post_path($post->directory, $post->time_first_published).'content.php';
    $content = ob_get_clean();
    
    $this->use_template = isset($no_template) ? 0 : 1;
    
    if (isset($styles)) {
      if (!is_array($styles))
        throw error::expecting_array();
      $this->styles = array_merge($this->styles, $styles);
    }
    
    if (isset($scripts)) {
      if (!is_array($scripts))
        throw error::expecting_array();
      $this->scripts = array_merge($this->scripts, $scripts);
    }
    
    return $content;
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