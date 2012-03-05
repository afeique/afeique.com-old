<p>
  The site source is available via a <?=b_link('http://github.com/afeique/afeique.com.git','github repository')?>.  Git-specific discussion, such as how to setup Git, how to clone a repository, and so on, are beyond the scope of this page. Here, the focus is to provide an overview of the site source. Additional documentation for the source can be written on request; to contact me, refer to the <?=l_link('about')?> page.</p>

<h1>requirements</h1>
<ul>
  <li>PHP 5.3+</li>
  <li>MySQL 5.1+</li>
  <li>Either Apache 2 or server software with <?=code('mod_rewrite')?> equivalent</li></ul>

<h1>source overview</h1>

<h2>html as php objects</h2>
<p>
  PHP objects representing HTML can be embedded seamlessly within the logic,  can be loaded into helper functions  within the same class (representing sub-views), and can be inherited for use in child classes. Using PHP objects  representing HTML enables a new degree of code-reuse for views, and a whole system of inheritance-based templating.</p>

<h2>oohtml</h2>
<p>
  The current implementation of oohtml rests within three files: <?=code('/libs/container.php')?>, <?=code('/libs/element.php')?>, and <?=code('/oohtml.php')?>. Class <?=code('container')?> represents a set of "renderable" elements: anything that is either a scalar or contains a <?=code('__toString()')?> method. It has a <?=code('__($arg1, $arg2, ..., $argN)')?> method for embedding N-many elements. Once content is embedded, <?=code('container')?> can render everything as a single run-on string.</p>
<p>
  Perhaps intuitively, an <?=code('element')?> extends <?=code('container')?> because an <?=code('element')?> can contain N-many other elements. In contrast to  and is specifically for creating HTML elements. When being instantiated, <?=code('element')?> to be passed a name and whether or not the HTML element being represented  is self-closing (e.g. <?=code('<br />')?>). Self-closing elements can be embedded with content as the <?=('__($arg1, $arg2, ..., $argN)')?> method from the parent container class is not overridden, but none of this content will be rendered.</p>
<p>
  Lastly, the <?=code('/oohtml.php')?> file contains helper functions and shortcuts for using oohtml more efficiently.</p>

<h2>oohtml syntax</h2>
<p>
  The recommended method for instantiating an oohtml object is to use the shortcut helper function <?=code('l($element_name')?> defined in <?=code('/oohtml.php')?>.</p>
<p>
  Embedding content and setting HTML attributes in oohtml is achieved through method chaining. The embed method is <?=code('__($content1, $content2, ..., $contentN)')?> and the set attribute method is <?=code('_($attribute, $value)')?>. As mentioned before, each piece of content embedded must be a renderable object; "renderable" meaning that it is either a scalar and therefore castable by PHP to a string or it contains a <?=code('__toString')?> method. If there is an attempt to embed a non-renderable piece of content, an Exception will be thrown.</p>
<p>
  There are numerous shortcuts for setting the most widely used attributes. For example, to set the id attribute of an element, <?=code('_i($id)')?> can be used. For an exhaustive list of shortcuts, it is recommended to peruse the <?=code('/libs/element.php')?> file.</p>
<p>
  Because of this, there are many ways to produce the same content. For example,</p>

<pre class="code">
<?=htmlentities('$p = l("p")->__("this content inside a ", htmlentities("<p>"))," element with class m.")->_c("m");
$link = l("a")->__("this is a link that opens a new window on google")->_("href","google.com)->_("target","_blank");')?>
</pre>
<p>
  can be reorganized into</p>
<pre class="code">
<?=htmlentities('$html = l("p")->_c("m")->__("this content inside a ", htmlentities("<p>"))," element with class m.");
$link = l("a")->_("href","google.com)->_("target","_blank")->__("this is a link that opens a new window on google");')?>
</pre>
<p>
  Moreover, using helper methods from <?=code('/oohtml.php')?>, one could instead write</p>
<pre class="code">
<?=htmlentities('$html = p("this content inside a ", htmlentities("<p>")), " element with class m.")->_c("m");
$link = b_link("google.com","this is a link that opens a new window on google");')?>
</pre>
<p>
  So what is the best way? In cases where large chunks of content are being embedded, it is recommended to specify element attributes <em>before</em> embedding content. This also remains consistent with the HTML the oohtml represents. In cases where only a small chunk of content is being embedded, it seems more prudent to chain attributes off of a helper function.</p>
<p>
  Additionally, one should have picked up on the fact that embedding content within an element does not automatically escape it with <?=code('htmlentities')?>. This is on purpose, as in numerous instances, blocks of html from external files are embedded within elements alongside other oohtml objects. An example is this very page: it is written using straight HTML with some PHP, but rendered from within an oohtml object.</p>

<h2>crafters, not controllers</h2>
<p>
  With oohtml, every controller is no longer just a controller but an entire self-contained unit for generating pages.  Every method isn't just the logic for a particular page, but also the view. So the term "controller" is no longer appropriate and instead they are referred to as "crafters."</p>
<p>
  The parent of all crafters is the <?=code('abstract class crafter')?> in <?=code('/libs/crafter.php')?>. In the current site implementation, crafters keep all their page-methods protected or private, all page-methods have an _ (underscore) as their first character, and no arguments are ever passed to page-methods.</p>
<ul>
  <li><?=code('public function _index()')?>&mdash;not valid, won't be detected as a page-method, must be protected or private</li>
  <li><?=code('protected function _index($arg=null)')?>&mdash;valid, but not recommended: in the current implementation, no args are ever passed</li>
  <li><?=code('protected static function _index()')?>&mdash;not valid, method cannot be static</li>
  <li><?=code('protected function index()')?>&mdash;not valid, no preceding underscore</li>
  <li><?=code('protected function _index()')?>&mdash;valid</li>
  <li><?=code('private function index()')?>&mdash;valid</li></ul>
<p>
  <?=code('abstract class crafter')?> has three abstract methods:</p>

<ul>
  <li><?=code('public abstract function _index()')?></li>
  <li><?=code('public abstract function _404()')?></li>
  <li><?=code('public abstract function craft()')?></li></ul>
<p>
  The first two methods, <?=code('_index()')?> and <?=code('_404()')?>, define pages. Those pages are mandatory for all child crafters, either to define themselves or to inherit. This is because two methods within <?=code('abstract class crafter')?> (<?=code('__construct()')?> and <?=code('craft()')?>&mdash;which are expected to be inherited by all children to some degree) specifically point to <?=code('_index()')?> and <?=code('_404()')?>.</p>
<p>
  The third abstract method, <?=code('craft()')?>, allows children to define their own method of crafting pages. Since each child crafter class will be responsible for its own set of related pages, it follows  that each set of pages might have a different methodology for crafting pages, either for styling, functionality,  or security. For example, the <?=code('admin_crafter')?> checks to ensure the user is logged in as an admin before  rendering any admin pages; else it renders a login page.</p>

<h2>the template crafter</h2>
<p>
  In the current implementation of this site, there is one intermediary parent between the abstract <?=code('crafter')?>  class and all other child crafters: the <?=code('template_crafter')?> class. This crafter is responsible for defining  the template for the entire website, as well as numerous helper methods for generating content and connecting to the  database.</p>
<h2>
  requesting pages from crafters</h2>
<p>
  Since page-methods are protected, one must externally <em>request</em> a crafter to craft a page. This is done in my implementation via the <?=code('request($page)')?> method.</p>
<p>
  Internally, the crafter also keeps track of all valid page-methods it contains. This is done using reflection within the crafter's <?=code('__construct()')?> method.</p>
<p>
  When a crafter is passed a request, it will check whether the request is for a valid page-method. If so, it'll prepare itself to craft it. If not, it will instead prepare to craft a <?=code('404')?> page.</p>
<p>
  In order to actually craft the requested page, one can either cast the crafter to a string (the crafter's <?=code('__toString()')?> method calls the <?=code('craft()')?> method) or manually invoke the <?=code('craft()')?>  method. The former method has the disadvantage of being vague when Exceptions are thrown, so using the latter is recommended.</p>
<p>
  Typically, all crafters follow the behaviour of crafting an <?=code('index')?> page by default if no request is made  to it. However, this may be undesirable in certain scenarios and can be overridden by defining a customized <?=code('__construct()')?> method.</p>
<p>
  Within the site source, all child crafters are located within the <?=code('/crafters')?> directory.</p>
<h2>
  query string parsing</h2>
<p>
  The default <?=code('/.htaccess')?> file bundled defines a set of rewrite conditions which redirect all dynamic  requests to <?=code('index.php/$1')?>. Within <?=code('/index.php')?>, these requests are parsed from  <?=code('$_SERVER["QUERY_STRING"]')?> in order to determine what crafter to instantiate, what page-method to request,  and most importantly, whether or not the appropriate crafter even exists. If the crafter doesn't exist,  a <?=code('404')?> is thrown based on what is defined in <?=code('/crafters/root_crafter.php')?>.</p>
<p>
  That aside, the key point for this section is that all query-string information beyond the relative path to the crafter (including the page-method request) is put into the <?=code('$GLOBALS[EXTRA]')?> array  (<?=code('EXTRA')?> is a constant defined in <?=code('/config.php')?>). Page-methods can then glean this extra information. </p>
<p>
  Consequently, unlike in <?=b_link('http://codeigniter.com/','CodeIgniter')?> where this information is passed  automatically to page-methods as arguments, the page-method must make an effort to get the information.</p>

<h2>nothing's perfect</h2>
<p>
  In the same vein of the critiques levied against MVC at large, one can find similar small nitpicks with oohtml and the manner in which it is utilized in the current implementation.</p>
<p>
  For example, <?=b_link('http://www.eclipse.org/projects/project.php?id=tools.pdt','Eclipse PDT')?> has HTML  autocomplete features that speed up writing HTML drastically. However, oohtml syntax has poor autocompletion  in Eclipse, meaning it is actually more cumbersome to write oohtml than actual HTML, despite oohtml being  more compact.</p>
<p>
  Moreover, because the view is now integrated into the logic, crafter page-method code is naturally larger than its counterpart controller code. This means that page-method code becomes less navigable despite not having to flip files every five seconds.</p>

<h2>potential workarounds</h2>
<p>
  Using helper functions dedicated to generating oohtml rather than embedding oohtml in the middle of page-methods  works well. This greatly minimizes the area taken up by page-methods while retaining overall code navigability. The code navigability part is true primarily if you use some sort of search to navigate code quickly.</p>
<p>
  This also has the advantage of reducing code duplication by consistently favoring the creation of reusable methods.</p>

<h2>'b' for 'blocks'</h2>
<p>
  In <?=code('/oohtml.php')?> there is a helper function called <?=code('b($block)')?>. This function grabs a chunk of static content in <?=code('/blocks')?> via output buffering (so PHP content is still processed),  and returns the resulting output.</p>

<h2>database access</h2>
<p>
  The current setup utilizes two users configured for access to the site database: a single public user who can only perform <?=code('SELECT')?> queries; and an admin user who can perform all operations. No admin credentials are  stored in any config files. Only the public access credentials are stored.</p>

<h2>public access credentials</h2>
<p>
  The public access credentials are stored in <?=code('/mysql_credentials.php')?>. It defines the following variables:</p>
<ul>
  <li><?=code('$mysql_public_user')?></li>
  <li><?=code('$mysql_public_pass')?></li>
  <li><?=code('$mysql_host')?></li>
  <li><?=code('$mysql_db')?></li></ul>
<p>
  Connect code can be found in <?=code('/crafters/template_crafter.php')?> in the <?=code('db_connect()')?> method. This method is called automatically within <?=code('template_crafter::__construct()')?>. </p>

<h2>admin access</h2>
<p>
  In order to login as an admin, one must know the actual SQL admin credentials. Once logged in, the credentials are stored in PHP <?=code('$_SESSION')?> variables. The template_crafter always checks whether  these variables are set and uses their contents to establish an admin connection automatically.</p>

<h2>admin panel</h2>
<p>
  The <?=code('/crafters/admin_crafter.php')?> file has a modified <?=code('craft()')?> method which, as was mentioned prior, first checks to see if a user is logged in as an admin. If the user is not an admin, the login page is rendered.</p>

<h2>security concerns</h2>
<p>
  To contact me with any thoughts or comments regarding security vulnerabilities and flaws, please refer to the <?=l_link('about')?> page.</p>

<h2>debug mode</h2>
<p>
  Setting the <?=code('DEBUG')?> constat defined in <?=code('/config.php')?> to a nonzero value will set PHP  <?=code('error_reporting')?> to <?=code('E_ALL')?>; will make admin AJAX calls return more detailed debug  information via JSON (currently rendered by the admin JavaScript as alerts); and will make  <?=code('template_crafter')?> use development CSS and JavaScript files instead of their compressed counterparts (the subject of JavaScript and CSS compression is covered in greater detail further below).</p>

<h2>helper classes</h2>
<p>
  There are only two helper classes: <?=code('/libs/validate.php')?> and <?=code('/libs/error.php')?>.  The former is a helper for validating form inputs. It uses method-chaining to quickly apply a series  of operations and validations on a particular input (either a string or an array) and can then generate an error string compiling all the issues.</p>
<p>
  The latter is a helper for throwing Exceptions when an error occurs, primarily cases when an argument with the  wrong type is passed to a function. Some classes extend the error class in order that the error class only contain the most widely applicable Exceptions, for exmaple the <?=code('validate')?> helper defines a <?=code('validate_error')?> class.</p>

<h2>javascript and css compression</h2>
<p>
  Everything is truly contingent on what you set <?=code('DEBUG')?> to in <?=code('/config.php')?>. Setting <?=code('DEBUG')?> to something nonzero will make the system use development versions of scripts and load scripts via <?=code('<script src="..."></script>')?> tags in the head. Setting <?=code('DEBUG')?> to <?=code('0')?> will make the system load "meshed" JavaScript via dynamic JavaScript-based deferment (i.e. using JavaScript to load JavaScript once the document is ready).</p>
<p>
  There is a tool in the admin panel for compressing JavaScript and CSS. Currently, this tool will take all JavaScript files defined in <?=code('/config.php')?>, mash them together in the <em>order specified</em>, and  then pack them using <?=b_link('http://dean.edwards.name/packer/','Dean Edwards\' JavaScript packer')?>. The  resulting output will then be saved to a single file specified in <?=code('/config.php')?>.</p>
<p>
  This single file is ideal for dynamic deferment. Performing dynamic deferment without the use of a special library  such as <?=b_link('http://labjs.com/','LABjs')?> leads to potential race conditions wherein dependencies are not loaded before their dependents. However, because the tool mentioned above meshes the scripts together in the correct order, race conditions are eliminated when the single meshed file is loaded via dynamic deferment.</p>
<p>
  The CSS portion is even more straightforward. The same tool mentioned above minifies the CSS. If <?=code('DEBUG')?>  is nonzero, the non-minified development CSS files are used. If <?=code('DEBUG')?> is <?=code('0')?>, the minified  CSS files generated by the admin tool are used.</p>
<p>
  Additionally, JavaScript can be forced to be render inline via the <?=code('INLINE_JS')?> constant in  <?=code('/config.php')?>.</p>

<h2>htaccess</h2>
<p>
  Besides configuring <?=code('mod_rewrite')?>, the <?=code('.htaccess')?> that comes bundled in the git repository  will enable <?=code('mod_gzip')?> output compression if it's available. It will also set <?=code('Expires')?> headers to access plus a week.</p>

<h2>static assets</h2>
<p>
  Static assets such as JavaScript, CSS, and images are placed into the individual directory <?=code('/static')?>.  On this server, that directory is pointed to by the subdomain <?=code('static.afeique.com')?>, a cookieless domain. Additionally, the server is configured to redirect <?=code('afeique.com')?> to <?=code('www.afeique.com')?> in order  that <?=code('static.afeique.com')?> remain a cookie-less domain. Otherwise, cookies set on the  root domain  <?=code('afeique.com')?> would still be  sent to the subdomain <?=code('static.afeique.com')?> and in  some cases it would no longer be a cookieless domain.</p>

<h2>google analytics</h2>
<p>
  The system automatically attempts to use google analytics for tracking site metrics. Within <?=code('/index.php')?> there is a small conditional that checks for the existence of <?=code('/analytics.php')?>. The file <?=code('/analytics.php')?>  defines the constant containing the Google Analytics tracking ID and nothing more. If the file isn't found, the constant is automatically set to 0.</p>
<p>
  If the constant is set to a nonzero value, the <?=code('template_crafter::page_template()')?> method will embed the Google Analytics JavaScript, including the tracking ID in the constant, in the page <?=code('<head>')?>.</p>
  
<h2>meta</h2>
<p>
  To set a meta redirect to another page relative to the <?=code('BASE_URL')?>, set <?=code('$this->meta_redirect')?> from within a crafter to the URI that would be appended to the <?=code('BASE_URL')?>. The redirect time will be the value of the <?=code('META_REDIRECT_TIME')?> constant defined in <?=code('/config.php')?>.</p>
<p>
  Similarly, to set the meta description of a page or a set of pages, simply set the value of <?=code('$this->meta_description')?> from within a crafter.</p>
<p>
  Handling of these meta variables is done within the <?=code('template_crafter::page_template()')?> method.</p>