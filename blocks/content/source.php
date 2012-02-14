<p>
  The site source is available via a <?=b_link('http://github.com/afeique/afeique.com.git','github repository')?>. 
  Git-specific discussion, such as how to setup Git, how to clone a repository, and so on, are beyond the scope of 
  this page. Here, discussion will be focused upon the source itself.
</p>

<h1>requirements</h1>
<ul>
  <li>PHP 5.3+</li>
  <li>MySQL 5.1+</li>
  <li>Either Apache 2 or server software with <?=code('mod_rewrite')?> equivalent</li>
</ul>

<h1>introduction to the source</h1>

<h2>statement of intent</h2>
<p>
  This site's code was written by me for me, with the sole intention of having fun programming. Being 
  used by other people was never on the agenda. Nevertheless, below is a general overview of the
  framework I wrote, provided in part as documentation and in part as reading for anyone interested.
</p>

<h2>thoughts on mvc</h2>
<p>
  Working with the MVC pattern for several years led to an interest in implementing an alternative pattern
  for development that somehow combined logic and views into one package. Doing so reduces or eliminates:
<ul>
  <li>having to navigate to separate views when making modifications;</li>
  <li>needing "sub-views" for chunks of content that are reused across multiple pages;</li>
  <li>"repeating" the logic of looping over content, first when retrieving from the model, then 
  again when displaying it.</li>
</ul>
<p>
  The first two bullets are important, as they force the developer to navigate numerous tabs and files.
  Doing so seems to have a considerable impact on workflow, though the observation is completely subjective. 
  This stance primarily applies to Windows-based development, as using 
  <?=b_link('http://www.gnu.org/software/emacs/','emacs')?> allows for a much more streamlined interface for
  switching between multiple files.
</p>

<h2>html as php objects</h2>
<p>
  HTML, like XML and JSON, already notates "objects." The objects are HTML elements, each element
  with its own set of attributes such as id, class, style, etc. Consequently, it is very simple to write a 
  small library capable of generating PHP objects that represent HTML.
</p>
<p>
  PHP objects representing HTML can be embedded seamlessly within the logic,  can be loaded into helper functions 
  within the same class (representing sub-views), and can be inherited for use in child classes. Using PHP objects 
  representing HTML enables a new degree of code-reuse for views, and a whole system of inheritance-based templating.
</p>
<p>
  The current site implementation of oohtml are in the <?=code('/libs/container.php')?> and <?=code('/libs/element.php')?> 
  files respectively. Class <?=code('container')?> is extended by <?=code('element')?>. Class <?=code('container')?>
  represents a set of "renderable" elements: anything that is either a scalar or contains a <?=code('__toString()')?> 
  method. It has code for embedding n-many elements using 
  <?=b_link('http://php.net/manual/en/function.func-get-args.php',code('func_get_args'))?> and then rendering all its 
  embedded content into a single run-on string (eliminating whitespace reduces output size without hindering debugging
  thanks to browsers such as <?=b_link('https://www.google.com/chrome','Google Chrome')?>).
</p>
<p>
  Class <?=code('element')?> is specifically for creating an HTML element. It has to be passed a name and whether or
  not the element is self-closing (e.g. <?=code('<br />')?>). Self-closing elements can be embedded with 
  content (as the embed method from the parent container class is not overridden), but none of this content 
  will be rendered.
</p>
<p>
  Lastly, the <?=code('/oohtml.php')?> file contains helper functions and shortcuts for using oohtml more efficiently.
</p>


<h2>oohtml syntax</h2>
<p>
  The recommended method of instantiating an oohtml object is to use the shortcut helper function 
  <?=code('l($element_name')?> defined in <?=code('/oohtml.php')?>.
</p>
<p>
  Embedding content and setting HTML attributes in oohtml is achieved through method chaining. The embed method is
  <?=code('__($content1, $content2, ..., $contentN)')?> and the set attribute method is <?=code('_($attribute, $value)')?>.
  As mentioned before, each piece of content embedded must be a renderable object; "renderable" meaning that it is either
  a scalar and therefore castable by PHP to a string or it contains a <?=code('__toString')?> method. If there is an
  attempt to embed a non-renderable piece of content, an Exception will be thrown.
</p>
<p>
  There are numerous shortcuts for setting the most widely used attributes. For example, to set the id attribute of an
  element, <?=code('_i($id)')?> can be used. For an exhaustive list of shortcuts, it is recommended to peruse the
  <?=code('/libs/element.php')?> file.
</p>
<p>
  Because of this, there are many ways to produce the same content. For example,
</p>

<pre class="code">
<?=htmlentities('$p = l("p")->__("this content inside a ", htmlentities("<p>"))," element with class m.")->_c("m");
$link = l("a")->__("this is a link that opens a new window on google")->_("href","google.com)->_("target","_blank");')?>
</pre>

<p>
  can be reorganized into
</p>

<pre class="code">
<?=htmlentities('$html = l("p")->_c("m")->__("this content inside a ", htmlentities("<p>"))," element with class m.");
$link = l("a")->_("href","google.com)->_("target","_blank")->__("this is a link that opens a new window on google");')?>
</pre>

<p>
  Moreover, using helper methods from <?=code('/oohtml.php')?>, one could instead write
</p>

<pre class="code">
<?=htmlentities('$html = p("this content inside a ", htmlentities("<p>")), " element with class m.")->_c("m");
$link = b_link("google.com","this is a link that opens a new window on google");')?>
</pre>

<p>
  So what is the best way? For sake of consistency with the HTML that oohtml seeks to represent, it is recommended
  to do things in the same order. That is, follow the second example and set element attributes <em>before</em>
  embedding content. In cases where there are helper methods available, if there is an intent to set element
  attributes, use <?=code('l($element_name)')?> to create the element, immediately chain attributes to it, and
  then embed the desired content.
</p>
<p>
  Additionally, one should have picked up on the fact that embedding content within an element does not automatically
  escape it with <?=code('htmlentities')?>. This is on purpose, as in numerous instances, blocks of html from external
  files are embedded within elements alongside other oohtml objects. An example is this very page: it is written using 
  straight HTML with some PHP, but rendered from within an oohtml object.
</p>

<h2>crafters, not controllers</h2>
<p>
  With oohtml, every controller is no longer just a controller but an entire self-contained unit for generating pages. 
  Every method isn't just the logic for a particular page, but also the view. So the term "controller" is no longer 
  appropriate and instead they are referred to as "crafters."
</p>
<p>
  The parent of all crafters is the <?=code('abstract class crafter')?> in <?=code('/libs/crafter.php')?>.
  In the current site implementation, crafters keep all their page-methods protected or private, all page-methods
  have an _ (underscore) as their first character, and no arguments are ever passed to page-methods.
</p>
<ul>
  <li><?=code('public function _index()')?>&mdash;not valid, won't be detected as a page-method, must be protected or private</li>
  <li><?=code('protected function _index($arg=null)')?>&mdash;valid, but not recommended&mdash;in current implementation, no args are ever passed</li>
  <li><?=code('protected static function _index()')?>&mdash;not valid, method cannot be static</li>
  <li><?=code('protected function index()')?>&mdash;not valid, no preceding underscore</li>
</ul>

<p>
  <?=code('abstract class crafter')?> has three abstract methods:
</p>

<ul>
  <li><?=code('public abstract function _index()')?></li>
  <li><?=code('public abstract function _404()')?></li>
  <li><?=code('public abstract function craft()')?></li>
</ul>
<p>
  The first two methods, <?=code('_index()')?> and <?=code('_404()')?>, define pages. Those pages are mandatory
  for all child crafters, either to define themselves or to inherit. This is because two methods within 
  <?=code('abstract class crafter')?> (<?=code('__construct()')?> and <?=code('craft()')?>&mdash;which are expected
  to be inherited by all children to some degree) specifically point to <?=code('_index()')?> and <?=code('_404()')?>.
</p>
<p>
  The third abstract method, <?=code('craft()')?>, allows children to define their own method of 
  crafting pages. Since each child crafter class will be responsible for its own set of related pages, it follows 
  that each set of pages might have a different methodology for crafting pages, either for styling, functionality, 
  or security. For example, the <?=code('admin_crafter')?> checks to ensure the user is logged in as an admin before 
  rendering any admin pages; else it renders a login page.
</p>

<h2>the template crafter</h2>
<p>
  In the current implementation of this site, there is one intermediary parent between the abstract <?=code('crafter')?> 
  class and all other child crafters: the <?=code('template_crafter')?> class. This crafter is responsible for defining 
  the template for the entire website, as well as numerous helper methods for generating content and connecting to the 
  database.
</p>

<h2>requesting pages from crafters</h2>
<p>
  Since page-methods are protected, one must externally <em>request</em> a crafter to craft a page. This is done
  in my implementation via the <?=code('request($page)')?> method.
</p>
<p>
  Internally, the crafter also keeps track of all valid page-methods it contains. This is done using reflection
  within the crafter's <?=code('__construct()')?> method.
</p>
<p>
  When a crafter is passed a request, it will check whether the request is for a valid page-method. If so, it'll 
  prepare itself to craft it. If not, it will instead prepare to craft a <?=code('404')?> page.
</p>
<p>
  In order to actually craft the requested page, one can either cast the crafter to a string (the crafter's
  <?=code('__toString()')?> method calls the <?=code('craft()')?> method) or manually invoke the <?=code('craft()')?> 
  method. The former method has the disadvantage of being vague when Exceptions are thrown, so using the latter
  is recommended.
</p>
<p>
  Typically, all crafters follow the behaviour of crafting an <?=code('index')?> page by default if no request is made 
  to it. However, this may be undesirable in certain scenarios and can be overridden by defining a customized
  <?=code('__construct()')?> method.
</p>
<p>
  Within the site source, all child crafters are located within the <?=code('/crafters')?> directory.
</p>

<h2>query string parsing</h2>
<p>
  The default <?=code('/.htaccess')?> file bundled defines a set of rewrite conditions which redirect all dynamic 
  requests to <?=code('index.php/$1')?>. Within <?=code('/index.php')?>, these requests are parsed from 
  <?=code('$_SERVER["QUERY_STRING"]')?> in order to determine what crafter to instantiate, what page-method to request, 
  and most importantly, whether or not the appropriate crafter even exists. If the crafter doesn't exist, 
  a <?=code('404')?> is thrown based on what is defined in <?=code('/crafters/root_crafter.php')?>.
</p>
<p>
  That aside, the key point for this section is that all query-string information beyond the relative path
  to the crafter (including the page-method request) is put into the <?=code('$GLOBALS[EXTRA]')?> array 
  (<?=code('EXTRA')?> is a constant defined in <?=code('/config.php')?>). Page-methods can then glean this
  extra information. 
</p>
<p>
  Consequently, unlike in <?=b_link('http://codeigniter.com/','CodeIgniter')?> where this information is passed 
  automatically to page-methods as arguments, the page-method must make an effort to get the information.
</p>

<h2>nothing's perfect</h2>
<p>
  In the same vein of the critiques levied against MVC at large, one can find similar small nitpicks with
  oohtml and the manner in which it is utilized in the current implementation.
</p>
<p>
  For example, <?=b_link('http://www.eclipse.org/projects/project.php?id=tools.pdt','Eclipse PDT')?> has HTML 
  autocomplete features that speed up writing HTML drastically. However, oohtml syntax has poor autocompletion 
  in Eclipse, meaning it is actually more cumbersome to write oohtml than actual HTML, despite oohtml being 
  more compact.
</p>
<p>
  Moreover, because the view is now integrated into the logic, crafter page-method code is naturally larger
  than its counterpart controller code. This means that page-method code becomes less navigable despite
  not having to flip files every five seconds.
</p>

<h2>potential workarounds</h2>
<p>
  Using helper functions dedicated to generating oohtml rather than embedding oohtml in the middle of page-methods 
  works well. This greatly minimizes the area taken up by page-methods while retaining overall code navigability.
  The code navigability part is true primarily if you use some sort of search to navigate code quickly.
</p>
<p>
  This also has the advantage of reducing code duplication by consistently favoring the creation of reusable methods.
</p>

<h2>'b' for 'blocks'</h2>
<p>
  In <?=code('/oohtml.php')?> there is a helper function called <?=code('b($block)')?> which specifically grabs a chunk 
  of static content in <?=code('/blocks')?>, fetches it through output buffering (so PHP content is still processed), 
  and returns the resulting output.
</p>

<h2>models</h2>
<p>
  Currently, the site uses <?=b_link('http://www.phpactiverecord.org/','php.activerecord')?> as a database
  ORM. There are three tables <?=code('posts')?>, <?=code('tags')?>, and a relational table called 
  <?=code('post_tag_relations')?>. Every table utilizes the InnoDB engine. This is because the relational table has 
  <?=code('FOREIGN KEY')?> constraints to <?=code('posts')?> and <?=code('tags')?> to streamline the relational
  mapping. Below is the the <?=code('CREATE TABLE')?> SQL for each table, along with the <?=code('ALTER TABLE')?>
  SQL for <?=code('post_tag_relations')?> to establish the <?=code('FOREIGN KEY')?> constraints:
</p>

<pre class="code">
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL,
  `description` varchar(250) NOT NULL,
  `directory` varchar(250) NOT NULL,
  `time_first_published` int(10) unsigned NOT NULL,
  `time_last_modified` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`),
  KEY `time_first_published` (`time_first_published`),
  KEY `time_last_modified` (`time_last_modified`),
  KEY `description` (`directory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(250) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `post_tag_relations` (
  `post_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`post_id`,`tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `post_tag_relations`
  ADD CONSTRAINT `post_tag_relations_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `post_tag_relations_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

</pre>

<h2>database access</h2>
<p>
  The current setup utilizes two users configured for access to the site database: a single public user who can
  only perform <?=code('SELECT')?> queries; and an admin user who can perform all operations. No admin credentials are 
  stored in any config files. Only the public access credentials are stored.
</p>

<h2>public access credentials</h2>
<p>
  By default, in <?=code('/config.php')?>, the "afeique.com" directory cloned from github is defined as the 
  <?=code('BASE_PATH')?> constant. The public access credentials are stored one directory above <?=code('BASE_PATH')?>, 
  which is a directory inaccessible by remote users in this setup.
</p>
<p>
  The public access credentials are stored in <?=code('/../mysql_credentials.php')?>. It defines the following
  variables:
</p>
<ul>
  <li><?=code('$mysql_public_user')?></li>
  <li><?=code('$mysql_public_pass')?></li>
  <li><?=code('$mysql_host')?></li>
  <li><?=code('$mysql_db')?></li>
</ul>
<p>
  Connect code can be found in <?=code('/crafters/template_crafter.php')?> in the <?=code('db_connect()')?> method.
  This method is called automatically within the <?=code('template_crafter::craft()')?> method. 
</p>

<h2>admin access</h2>
<p>
  In order to login as an admin, one must know the actual SQL admin credentials. Once logged in, the
  credentials are stored in PHP <?=code('$_SESSION')?> variables. The template_crafter always checks whether 
  these variables are set and uses their contents to establish an admin connection automatically.
</p>

<h2>admin panel</h2>
<p>
  The <?=code('/crafters/admin_crafter.php')?> file has a modified <?=code('craft()')?> method which, as was
  mentioned prior, first checks to see if a user is logged in as an admin. If the user is not an admin, the
  login page is rendered.
</p>

<h2>security concerns</h2>
<p>
  For any thoughts or comments regarding security vulnerabilities and flaws, please refer to the <?=l_link('contact')?>
  page.
</p>

<h2>debug mode</h2>
<p>
  Setting the <?=code('DEBUG')?> constat defined in <?=code('/config.php')?> to a nonzero value will set PHP 
  <?=code('error_reporting')?> to <?=code('E_ALL')?>; will make admin AJAX calls return more detailed debug 
  information via JSON (currently rendered by the admin JavaScript as alerts); and will make 
  <?=code('template_crafter')?> use development CSS and JavaScript files instead of their compressed counterparts
  (the subject of JavaScript and CSS compression is covered in greater detail further below).
</p>

<h2>helper classes</h2>
<p>
  There are only two helper classes: <?=code('/libs/validate.php')?> and <?=code('/libs/error.php')?>. 
  The former is a helper for validating form inputs. It uses method-chaining to quickly apply a series 
  of operations and validations on a particular input (either a string or an array) and can then generate 
  an error string compiling all the issues.
</p>
<p>
  The latter is a helper for throwing Exceptions when an error occurs, primarily cases when an argument with the 
  wrong type is passed to a function. Some classes extend the error class in order that the error class only contain 
  the most widely applicable Exceptions, for exmaple the <?=code('validate')?> helper defines a <?=code('validate_error')?>
  class.
</p>

<h2>javascript and css compression</h2>
<p>
  Everything is truly contingent on what you set <?=code('DEBUG')?> to in <?=code('/config.php')?>. Setting
  <?=code('DEBUG')?> to something nonzero will make the system use development versions of scripts and load
  scripts via <?=code('<script src="..."></script>')?> tags in the head. Setting <?=code('DEBUG')?>
  to <?=code('0')?> will make the system load "meshed" JavaScript via dynamic JavaScript-based deferment
  (i.e. using JavaScript to load JavaScript once the document is ready).
</p>
<p>
  There is a tool in the admin panel for compressing JavaScript and CSS. Currently, this tool will take all
  JavaScript files defined in <?=code('config.php')?>, mash them together in the <em>order specified</em>, and 
  then pack them using <?=b_link('http://dean.edwards.name/packer/','Dean Edwards\' JavaScript packer')?>. The 
  resulting output will then be saved to a single file specified in <?=code('/config.php')?>.
</p>
<p>
  This single file is ideal for dynamic deferment. Performing dynamic deferment without the use of a special library 
  such as <?=b_link('http://labjs.com/','LABjs')?> leads to potential race conditions wherein dependencies are not 
  loaded before their dependents. However, because the tool mentioned above meshes the scripts together in the correct
  order, race conditions are eliminated when the single meshed file is loaded via dynamic deferment.
</p>
<p>
  The CSS portion is even more straightforward. The same tool mentioned above minifies the CSS. If <?=code('DEBUG')?> 
  is nonzero, the non-minified development CSS files are used. If <?=code('DEBUG')?> is <?=code('0')?>, the minified 
  CSS files generated by the admin tool are used.
</p>
<p>
  Additionally, JavaScript can be forced to be render inline via the <?=code('INLINE_JS')?> constant in 
  <?=code('/config.php')?>.
</p>

<h2>htaccess</h2>
<p>
  Besides configuring <?=code('mod_rewrite')?>, the <?=code('.htaccess')?> that comes bundled in the git repository 
  will enable <?=code('mod_gzip')?> output compression if it's available. It will also set <?=code('Expires')?> 
  headers to access plus a week.
</p>

<h2>static assets</h2>
<p>
  Static assets such as JavaScript, CSS, and images are placed into the individual directory <?=code('/static')?>. 
  On this server, that directory is pointed to by the subdomain <?=code('static.afeique.com')?>, a cookieless domain.
  Additionally, the server is configured to redirect <?=code('afeique.com')?> to <?=code('www.afeique.com')?> in order 
  that <?=code('static.afeique.com')?> remain be a cookie-less domain. Otherwise, cookies set on the 
  root domain  <?=code('afeique.com')?> would still be  sent to the subdomain <?=code('static.afeique.com')?> and in 
  some cases it would no longer be a cookieless domain.
</p>