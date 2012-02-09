<p>
  The site source code is available on <?=b_link('http://github.com/','github')?> in 
  <?=b_link('http://github.com/afeique/afeique.com.git','this repository')?>. Git-specific discussion,
  such as how to setup Git, how to clone a repository, and so on, are beyond the scope of this
  page. Here we will focus on discussing the source itself.
</p>

<h1>introduction to the source</h1>

<h2>statement of intent</h2>
<p>
  This site's code was written by me for me, with the sole intention of having fun programming. Being 
  used by other people was never on the agenda. Nevertheless, below is a general overview of the
  framework I wrote, provided in part as documentation and in part as reading for anyone interested.
</p>

<h2>thoughts on mvc</h2>
<p>
  Through the course of working with MVC for the past several years, I have a few times been curious
  about whether the mixing of view and logic is really an issue. In my experience, the real gripe
  is the mixing of HTML and logic.
</p>
<p>
  In my opinion, using separate view files introduces the burden of:
</p>
<ul>
  <li>having to navigate to those separate files when making modifications;</li>
  <li>needing "sub-views" for chunks of content that are reused across multiple page.</li>
  <li>"repeating" the logic of looping over content, first when retrieving from the model, then 
  again when displaying it;</li>
</ul>
<p>
  I don't really consider any of these to be serious issues, but they are minor nuisances to me,
  particularly the first two bullets.
</p>
<p>
  In my workflow, I have a tendency to modify the view right after making changes in the logic. I've
  long felt it would be more convenient if the view were at least in the same file. I wouldn't 
  have to go searching for it amongst the other files in the project (sometimes numbering the hundreds), or 
  amongst 10+ open tabs in my IDE, most of which are views.
</p>

<h2>html as php objects</h2>
<p>
  At some point, I decided the solution was to be able to form HTML using PHP objects.
  I reasoned that the PHP objects representing HTML could be embedded seamlessly within the logic, 
  could be loaded into helper functions within the same class (representing sub-views), and best of all, 
  could be inherited for use in child classes. It would enable a whole system of simple inheritance-based 
  templating.
</p>
<p>
  HTML, like XML and JSON, already notates "objects." The objects are HTML elements, each element
  with its own set of attributes such as id, class, style, etc. Consequently, it is very simple to write a 
  small library capable of generating PHP objects that represent HTML.
</p>
<p>
  I say "small" and "simple" but it's only small and simple if you forego one thing: validation. Besides being
  considerably easier to write and maintain, skipping any sort of validation also gives you more flexibility.
</p>
<p>
  You can find my implementation of oohtml in the <?=code('/libs/container.php')?> and <?=code('/libs/tag.php')?> 
  files respectively. Class <?=code('container')?> is extended by <?=code('tag')?>. Class <?=code('container')?>
  represents a set of "renderable" elements: 
  anything that is either a scalar or contains a <?=code('__toString()')?> method. It has code for embedding n-many 
  elements using <?=b_link('http://php.net/manual/en/function.func-get-args.php', code('func_get_args'))?> and then 
  rendering all its contents to a single run-on string. 
</p>
<p>
  The tag class in contrast is specifically for creating an HTML tag. It has to be passed a name and whether
  or not the tag is self-closing (e.g. <?=code(htmlentities('<br />'))?>). Self-closing elements can be embedded
  with content (as the embed method from the parent container class is not overridden), but none of this
  content will be rendered.
</p>
<p>
  Lastly, the <?=code('/html.php')?> file contains helper functions and shortcuts for using oohtml more efficiently.
</p>

<h2>crafters, not controllers</h2>
<p>
  With oohtml, every controller is no longer just a controller - but an entire self-contained
  unit for generating pages. Every method isn't just the logic for a particular page, but
  also the view. So the term "controller" is no longer appropriate, and hence I call them "crafters."
</p>
<p>
  The parent of all crafters is the <?=code('/libs/crafter.php')?> file which defines the abstract crafter class.
  In my implementation, crafters keep all their page-methods protected or private, all page-methods
  have an _ (underscore) as their first character, and no arguments are ever passed to page-methods.
</p>
<ul>
  <li><?=code('public function _index()')?> - not valid, won't be detected as a page-method, must be protected or private</li>
  <li><?=code('protected function _index($arg=null)')?> - valid, but not recommended - in my implementation, no args are ever passed</li>
  <li><?=code('protected static function _index()')?> - not valid, method cannot be static</li>
  <li><?=code('protected function index()')?> - not valid, no preceding underscore</li>
</ul>
<p>
  Two abstract methods are: <?=code('public function _index()')?> and <?=code('public function _404()')?>. These 
  represent the index and 404 pages respectively.
</p>
<p>
  The method <?=code('public function craft()')?> is also left as an abstract method, in order that child crafters
  may define their own method for crafting pages. For example, the <?=code('admin_crafter')?> checks to ensure the
  user is logged in as an admin before rendering any admin pages; else it renders the login page.
</p>
<p>
  Since page-methods are protected, one must externally <em>request</em> a crafter to craft a page. This is done
  in my implementation via the <?=code('request($page)')?> method.
</p>
<p>
  Internally, the crafter also keeps track of all the valid page-methods it contains. This is done using reflection
  within the crafter's <?=code('__construct()')?> method.
</p>
<p>
  When a crafter is passed a request, it will check whether the request is for a valid page-method. If so, it'll 
  prepare itself to craft it. If not, it will instead prepare to craft a 404 page.
</p>
<p>
  In order to actually craft the requested page, one can either cast the crafter to a string (the crafter's
  <?=code('__toString()')?> method calls the <?=code('craft()')?> method) or manually invoke the <?=code('craft()')?> 
  method. The former method has the disadvantage of being vague when Exceptions are thrown, so I recommend the 
  latter method.
</p>
<p>
  Typically, all crafters follow the behaviour of crafting an index page by default if no request is made to it.
  However, this may be undesirable in certain scenarios and can be overridden by defining a customized
  <?=code('__construct()')?> method.
</p>
<p>
  Within the site source, all child crafters are located within the <?=code('/crafters')?> directory.
</p>

<h2>query string parsing</h2>
<p>
  The default <?=code('/.htaccess')?> file bundled in the repo defines a set of rewrite conditions which
  redirects all dynamic requests to <?=code('index.php/$1')?>. Within <?=code('/index.php')?>, these requests
  are parsed from <?=code('$_SERVER["QUERY_STRING"]')?> in order to determine what crafter is being called,
  what page-method to request, and most importantly, whether or not the appropriate crafter even exists.
  You can probably guess that if the crafter doesn't exist, a nice 404 is thrown based on what is defined
  in <?=code('/crafters/root_crafter.php')?>.
</p>
<p>
  That aside, the key point for this section is that all query-string information beyond the relative path
  to the crafter (including the page-method request) is put into the <?=code('$GLOBALS[EXTRA]')?> array 
  (<?=code('EXTRA')?> is a constant defined in <?=code('/config.php')?>). Page-methods can then glean this
  extra information. 
</p>

<p>
  Consequently, unlike in <?=b_link('http://codeigniter.com/','CodeIgniter')?> where this
  information is passed automatically to page-methods as arguments, the page-method must itself make an effort 
  to get the information.
</p>

<h2>nothing's perfect</h2>
<p>
  In the same vein of the complaints I levied against MVC at large, one can find similar small nitpicks with
  the oohtml this site comes with.
</p>
<p>
  For one thing, a lot of IDEs have HTML autocomplete features that speed up writing HTML drastically.
  However, the oohtml syntax has poor autocompletion in Eclipse, meaning it is actually more cumbersome to
  write oohtml than actual HTML, despite oohtml being more compact.
</p>
<p>
  Moreover, because the view is now integrated into the logic, crafter page-method code is generally larger
  than its counterpart view-controller (VC) code. This means that page-method code becomes less navigable despite
  not having to flip files every five seconds.
</p>

<h2>potential workarounds</h2>
<p>
  Using helper functions dedicated to generating oohtml rather than embedding oohtml in the middle of page-methods 
  works well. This greatly minimizes the area taken up by page-methods while retaining overall code navigability.
  The code navigability part is true primarily if you use "find" to navigate code quickly.
</p>
<p>
  This also has the advantage of reducing code duplication by consistently favoring the creation of reusable methods
  and code.
</p>

<h2>'b' for 'blocks'</h2>
<p>
  One thing is for sure: you don't want to catch yourself writing out entire sections of static content in oohtml.
  So save yourself the time. In <?=code('/html.php')?> there is a helper function called <?=code('b($block)')?> which
  specifically grabs a chunk of static content in <?=code('/blocks')?>, fetches it through output buffering (so php
  content is still processed), and returns the resulting output.
</p>

<h2>template crafter</h2>
<p>
  There is one intermediary parent between the abstract crafter class and all other child crafters: the template_crafter.
  This crafter is responsible for defining the template for the entire website, as well as all helper methods for
  generating content and database (model) access.
</p>

<h2>models</h2>
<p>
  Currently, the site uses <?=b_link('http://www.phpactiverecord.org/','php.activerecord')?> as a database
  ORM. There is only one table: <?=code('posts')?>. Here is the <?=code('CREATE TABLE')?> SQL:
</p>

<pre class="code">
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL,
  `tags` varchar(250) NOT NULL,
  `description` varchar(250) NOT NULL,
  `directory` varchar(250) NOT NULL,
  `time_first_published` int(10) unsigned NOT NULL,
  `time_last_modified` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`),
  KEY `time_first_published` (`time_first_published`),
  KEY `time_last_modified` (`time_last_modified`),
  KEY `description` (`directory`),
  KEY `tags` (`tags`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
</pre>

<p>
  InnoDB is used because I plan on separating the <?=code('tags')?> field into another table
  and setting up <?=code('FOREIGN KEY')?> constraints. 
</p>

<h2>database access</h2>
<p>
  In my current setup, I have two users configured for access to the site database: a single public user who can
  only perform <?=code('SELECT')?> queries; and an admin user who can do shenanigans. No admin credentials are 
  stored in any config files. Only the public access credentials are stored.
</p>

<h2>public access credentials</h2>
<p>
  By default, in <?=code('/config.php')?>, the "afeique.com" directory cloned from github is defined as the 
  <?=code('BASE_PATH')?> constant. The public access credentials are stored one directory above <?=code('BASE_PATH')?>, 
  which is a folder inaccessible by remote users on my current webhost.
</p>
<p>
  The public access credentials is the PHP file <?=code('/../mysql_credentials.php')?>. It defines the following
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
  I am no security expert and thus cannot vouch for the security of the current scheme. If you have
  any thoughts on the matter, be they comments or suggestions for improvement, do contact me.
</p>

<h2>useful constants</h2>
<p>
  Perhaps the single-most useful constant is the <?=code('DEBUG')?> constant defined in <?=code('/config.php')?>.
  Setting this to <?=code('1')?> will set PHP <?=code('error_reporting')?> to <?=code('E_ALL')?> and will make
  admin AJAX calls return more detailed debug information via JSON. These responses are currently rendered by
  the administrative JavaScript as alerts. More useful still, setting <?=code('DEBUG')?> to <?=code('1')?> will
  also make the template crafter use development CSS and JavaScript files instead of their minified counterparts.
</p>
<p>
  <?=code('DEBUG')?> aside, if you're using this framework for your own personal cahoots, you will probably want
  to modify the <?=code('BASE_PATH')?> and <?=code('BASE_URL')?> constants. These are currently defined according
  to my development and production setups and also form the key ingredient to all the other <?=code('*_PATH')?>
  and <?=code('*_URL')?> constants defined. <?=code('TIMEZONE')?> might be something you want to change as well.
</p>

<h2>helper classes</h2>
<p>
  There are only two helper classes: <?=code('/libs/validate.php')?> and <?=code('/libs/error.php')?>. 
  The former is a helper for validating form inputs. It uses method-chaining to quickly apply a series 
  of operations and validations on a particular input (either a string or an array) and can then generate 
  an error string compiling all the issues.
</p>
<p>
  The latter is a helper for throwing Exceptions when an error occurs; namely when an incorrect argument type
  is passed to a method. Some classes extend the error class in order that the error class only contain the most 
  widely applicable Exceptions.
</p>

<h2>satire on code style</h2>
<p>
  <em>This section is purposely a satirical farce.</em>
</p>
<p>
  Let me tell you, I am waging a war against capitalism. And by that, I mean capital letters. Like with all things,
  I don't actually mind camel-caps or camel-hats. When it comes to personal preference though, there's nothing like 
  a quality underscore.
</p>
<p>
  Like with many things I do, this derives from nothing more than a pure aesthetic dislike of camelish situations.
  For example, having a class called <?=code('DBConnect')?>. That's just one too many capital letters running into each 
  other. I'm somehow equally averse to <?=code('DB_Connect')?>. A good <?=code('db_connect')?> is the most pleasing to 
  my eye. There is no way you are getting me to type out <?=code('DatabaseConnect')?>. Wait. Except that one time.
</p>
<p>
  I also greatly dislike "mixing" in the manner PHP does. Some functions adhere to the underscore mafia, while
  other functions are part of the camel conglomerate. Sure you can say it's the best of both worlds, but I think
  it's more like drinking orange juice while brushing your teeth. Try it some time. I dares ya.
</p>
<p>
  When I say "averse" and "war" please don't misunderstand me - I cannot emphasize this enough, it's simply a minor
  preference, not something I'm OCD about. If I was paid to write code in camel-caps to adhere to certain guidelines,
  I would do it at the drop of a camel hat. If I was writing a library that required conformancy to certain coding 
  standards, I wouldn't bat a long camel eyelash. If I'm using an external library (e.g. php.activerecord) that uses 
  CamelCaps, I wouldn't go off and refactor the entire library. But if I'm writing for myself, you can bet your 
  camel-hides I'm going to use whatever darn well tootin' pleases me.
</p>
<p>
  <?=code('// END TRANSMISSION')?>
</p>