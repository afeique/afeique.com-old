<?php

class template_crafter extends crafter {
  // page title, also sets page heading (below) when only the title is set
  protected $title;
  
  // the page heading
  protected $heading;
  
  // id for the page heading
  protected $heading_id;
  
  // additional classes for the page heading
  protected $heading_class;
  
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
  
  // whether or not to display the heading
  protected $use_heading;
  
  // whether or not to display the heading commander
  protected $use_heading_commander;
  
  // enables anti-robot sentries
  protected $no_robots;
  
  // contains db errors, prim. connect errors
  protected $db_error;
  
  // used to redirect using a meta http-equiv="refresh" with time META_REFRESH_TIME
  protected $meta_redirect;
  
  // provides a meta description in the header
  protected $meta_description;
  
  // posts per page
  protected $ppp;
  
  // page epsilon - number of pages on either side of current page to display when paginating
  protected $pepsilon;
  
  public function __construct() {
    parent::__construct();
  
    $this->title = 'Fipple';
    $this->heading = '';
    
    $this->content = o(
        p('This page currently has no content. Try again some other time?')
    );
    
    $this->use_template = 1;
    $this->use_heading = 1;
    $this->use_heading_commander = 1;
    
    $this->no_robots = 0;
    
    $this->db_error = '';
    $this->db_access_level = 'public';
    
    $this->meta_redirect = '';
    $thsi->meta_description = '';
    
    $this->ppp = 1;
    $this->pepsilon = 2;
    
    $this->styles = array();
    $this->scripts = array();
    
    if ($this->logged_in())
      $this->db_connect($_SESSION['username'], $_SESSION['password']);
    else
      $this->db_connect();
  }
  
  protected function _index() {
    $this->title = 'emptiness';
    $this->content = '';
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
    
    /**
     * render styles
     */
    if ($this->logged_in())
      array_unshift($this->styles, ADMIN_CSS. (DEBUG ? '.css' : '.min.css'));
    array_unshift($this->styles, BASE_CSS.(DEBUG ? '.css' : '.min.css'));
    
    foreach ($this->styles as $i => $style) {
      $this->styles[$i] = css_link($style);
    }
    
    /**
     * render scripts
     */
    $scripts = $GLOBALS[BASE_JS];
    if (DEBUG) {
      if ($this->logged_in())
        $scripts[] = ADMIN_JS;
      foreach (array_reverse($scripts) as $script)
        array_unshift($this->scripts, $script);
    } else {
      if ($this->logged_in())
        array_unshift($this->scripts, ADMIN_MESHED_JS);
      else
        array_unshift($this->scripts, PUBLIC_MESHED_JS);
    }
    
    $scripts = '';
    foreach ($this->scripts as $i => $script) {
      if (INLINE_JS)
        $scripts .= file_get_contents(JS_PATH.$script);
      elseif (DEBUG)
        $this->scripts[$i] = script_src($script);
      else
        $this->scripts[$i] = '"'.$script.'"';
    }
    
    if (INLINE_JS)
      $scripts = l('script')->_t('text/javascript')->__($scripts);
    elseif (DEBUG)
      $scripts = implode('', $this->scripts);
    else
      $scripts = l('script')->_t('text/javascript')->__('function deferred_js(){var a=['.implode(',', $this->scripts).'];for(var b=0;b<a.length;b++){var c=document.createElement("script");c.src="'.JS_URL.'"+a[b];document.body.appendChild(c)}}if(window.addEventListener)window.addEventListener("load",deferred_js,false);else if(window.attachEvent)window.attachEvent("onload",deferred_js);else window.onload=deferred_js');
    
    if (ANALYTICS_TRACKING_ID) {
      $analytics = 'var _gaq=_gaq||[];_gaq.push(["_setAccount","'.ANALYTICS_TRACKING_ID.'"]);_gaq.push(["_trackPageview"]);(function(){var a=document.createElement("script");a.type="text/javascript";a.async=true;a.src=("https:"==document.location.protocol?"https://ssl":"http://www")+".google-analytics.com/ga.js";var b=document.getElementsByTagName("script")[0];b.parentNode.insertBefore(a,b)})()';
      $analytics = script($analytics);
    }
    
    /**
     * trim slashes from meta redirect
     */
    $this->meta_redirect = trim($this->meta_redirect,'/');
    
    /**
     * generate page
     */
    return o(
        html5_doctype(),
        html(
            head(
                css_link('blueprint/screen.min.css'),
                html_if('lt IE 8', css_link('blueprint/ie.min.css')),
                css_link('jquery-ui-lightness/jquery-ui-1.8.17.min.css'),
                implode('', $this->styles),
                html_if('lt IE 9', script_src('html5shiv.min.js')),
                l('script')->_t('text/javascript')->__('var BASE_URL="'.BASE_URL.'";var STATIC_URL="'.STATIC_URL.'";'),
                $this->no_robots ? ll('meta')->_n('robots')->_('content','noindex, noarchive, nofollow') : '',
                $this->meta_description ? ll('meta')->_n('description')->_('content', $this->meta_description) : '',
                $this->meta_redirect ? ll('meta')->_('http-equiv','refresh')->_('content', META_REFRESH_TIME.'; '.BASE_URL.$this->meta_redirect) : '',
                title($this->title.' @ afeique.com'),
                $analytics ? $analytics : '',
                $scripts
            ),

            body(
                l('div')->_i('main-container')->_c('container')->__(
                    b('template/header'),
                    l('div')->_i('main-content')->_c('span-24')->__(
                        $this->heading(),
                        $this->content
                    ),
                    b('template/footer')
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
  
  protected function heading() {
    if (!$this->use_heading && !$this->use_heading_commander)
      return '';
    
    // if no heading is set, use the page title for the heading
    if (empty($this->heading))
      $this->heading = h1($this->title);
    
    return 
    l('div')->_c('span-24')->__(
        l('div')->_i($this->heading_id)->_c('span-16 last '.$this->heading_class)->__(
            $this->use_heading ? $this->heading : '&nbsp;'
        ),
        l('nav')->_i('commander')->__($this->use_heading_commander ? $this->commands() : '&nbsp;')
    );
  }
  
  protected function commands() {
    if ($this->logged_in()) {
      return l('ul')->__(
          li(l_link('admin/publish','publish post')->_c('publish-button')->_('target','_blank')->_('title','publish post')),
          li(l_link('admin/compress-assets','compress assets')->_c('compress-button')->_('target','_blank')->_('title','compress assets')),
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
      $last_modified = l('li')->_c('post-last-modified')->__(
          strong('last modified:'),' ', date(POST_DATE_FORMAT, $post->time_last_modified)
      );
    
    $tags_html = l('ul')->_c('tags-list');
    $num_tags = count($post->tags);
    for ($i=0; $i<$num_tags; $i++) {
      $li = l('li');
      if ($i == $num_tags-1)
        $li->_c('last');
      //$li->__( l_link('tag-search/'.urlencode($tags[$i]), htmlentities($tags[$i])) );
      $li->__(htmlentities($post->tags[$i]->value));
      $tags_html->__($li);
    }
  
    return
    l('div')->_i('post-'.$post->id.'-row')->_c('span-24 post-row')->__(
        !empty($heading) ?
        l('div')->_i('post-'.$post->id.'-title')->_c('span-24 post-title')->__(
            $heading,
            $delete_post,
            $edit_title
        ) : '',
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
  
  /**
   * list_posts renders a list of posts with only their descriptions
   * for searching & browsing
   */
  protected function list_posts(array $posts) {
    $posts_html = o();
    if (empty($posts))
      return l('h2')->_c('text-center')->__(
          em('no posts to display')
      );
    
    foreach ($posts as $post) {
      $heading = 
      l('h2')->_c('post-title')->__(
          l_link('view/'.$post->id, htmlentities($post->title))->_('title','link to post')->_('target','_blank')
      );
      $content = 
      l('div')->_i('post-'.$post->id.'-description')->_c('span-24 post-description')->__(
          p(htmlentities($post->description))
      );
    
      $posts_html->__($this->post_template($post, $heading, $content));
    }
    
    return $posts_html;
  }
  
  /**
   * read_posts renders a list of posts with their actual content
   * for reading one or more posts simultaneously
   */
  protected function read_posts(array $posts) {
    $posts_html = o();
    if (empty($posts))
      return l('h2')->_c('text-center')->__(
          em('no posts to display')
      );
    
    $this->post_commanders($edit_title, $edit_tags, $delete_post);
    
    $first = 1;
    foreach ($posts as $post) {
      $content = $this->get_post_content($post);
      
      if ($first) {
        $heading = '';
        $this->heading = o(
            l('h1')->_c('post-title')->__(htmlentities($post->title)),
            $edit_title
        );
        $this->heading_id = 'post-'.$post->id.'-title';
        $this->heading_class = 'post-title';
        $first = 0;
      } else {
        $heading =
        l('div')->_i('post-'.$post->id.'-title')->_c('span-24 post-title')->__(
            l('h1')->_c('post-title')->__(htmlentities($post->title)),
            $edit_title
        );
      }
      
      $content =
      l('div')->_c('span-24 post-content')->__(
          $content
      );
    
      $posts_html->__($this->post_template($post, $heading, $content));
    }
    
    return $posts_html;
  }
  
  protected function page_bar($count, $uri) {
    $page = $this->get_page();
    $num_pages = ceil($count / $this->ppp);
    if ($num_pages < 1)
      $num_pages = 1;
  
  
    if ($num_pages == 1)
      return '';
  
    $uri = trim($uri,'/');
    $uri .= '/';
  
    $lepsilon = $page-$this->pepsilon;
    $upsilon = $page+$this->pepsilon;
    $html = l('nav')->_c('span-24 page-bar text-center');
    $list = ol();
  
    $i = 1;
    if ($lepsilon > 1)
      $i = $lepsilon;
  
    if ($page > 1)
      $list->__(l_link($uri,'&laquo;'));
    else
      $list->__(span('&laquo;'));
  
    for (; $i<=$num_pages && $i<=$upsilon; $i++) {
      switch ($i) {
        case ($i < 1 || $i == 0 || $i < $lepsilon || $i > $upsilon):
          $link = '';
          break;
        case ($i == $page):
          $link = span($i);
          break;
        default:
          $link = l_link($uri.$i, $i);
        break;
      }
  
      $list->__($link);
    }
  
    if ($page < $num_pages)
      $list->__(l_link($uri.$num_pages,'&raquo;'));
    else
      $list->__(l_link('&raquo;'));
  
    $html->__($list);
    return $html;
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
  
  protected function get_page($offset=0) {
    if (!isset($GLOBALS[EXTRA][$offset+1]))
      $page = 1;
    else {
      $page = (int)$GLOBALS[EXTRA][$offset+1];
      if ($page < 1)
        $page = 1;
    }
    
    return $page;
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