<p>
  The site source code is available on <?=a_link('http://github.com/','github')?> in 
  <?=a_link('http://github.com/afeique/afeique.com.git','this repository')?>.
</p>

<h2>git on windows</h2>
<p>
  On Windows 7, I currently use <?=a_link('http://code.google.com/p/msysgit/','msysGit')?>.
  To get started, download the installer and run. 
</p>

<p>
  For "Adjusting your PATH Environment", I checked the "Use Git Bash only" radio.
  This is because I run <?=a_link('http://strawberryperl.com/','Strawberry Perl')?>. 
  Since msysGit comes with Perl, Overriding the default Windows PATH will also override 
  the default Windows Perl installation . I also run Python, Ruby, and Tcl interpreters 
  on my Windows machine, but I can't attest to whether overriding the PATH with msysGit 
  will affect their functionality.
</p>

<p>
  All my commits are made with Unix-style line endings. Be sure to keep that in mind and 
  check the appropriate "line-ending conversion" radio for checkout once you get to that 
  screen.
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
  <li>"repeating" the logic of looping over content, first when retrieving from the model, then again when displaying it;</li>
</ul>
<p>
  I don't really consider any of these to be serious issues, but they are minor nuisances to me,
  particularly the first two bullets.
</p>
<p>
  In my workflow, I have a tendency to modify the view right after making changes in the logic. I've
  long felt it would be more convenient if the view were at least in the same file. At least I wouldn't 
  have to go searching for it amongst the other files in the project (sometimes numbering the hundreds), or 
  amongst 10+ open tabs in my IDE, most of which are views.
</p>

<h2>html as php objects</h2>
<p>
  At some point, I somehow decided the solution was to be able to describe and form HTML using PHP objects.
  I reasoned that the HTML could be embedded seamlessly within the logic, could be loaded into helper
  functions within the same class (representing sub-views), and best of all, could be inherited for use
  in child classes. It would enable a whole system of simple inheritance-based templating.
</p>
<p>
  HTML, like XML and JSON, already notates "objects." The objects themselves are HTML elements, each element
  with its own set of attributes such as id, class, style, etc. Consequently, it is very simple to write a 
  small library capable of generating PHP objects that represent HTML.
</p>
<p>
  I say "small" and "simple" but it's only small and simple if you forego one thing: validation. Besides being
  considerably easier to write and maintain, skipping any sort of validation also gives you more flexibility.
</p>
<p>
  You can find my implementation of oohtml in the /libs/container.php and /libs/tag.php files respectively.
  Class container is extended by tag; container just represents a set of "renderable" elements: anything that
  is either a scalar or contains a __toString() method. It has code for embedding n-many elements (using
  func_get_args) and then rendering all its contents to a string.
</p>
<p>
  The tag class in contrast is specifically for creating an HTML tag. It has to be passed a name and whether
  or not the tag is self-closing (e.g. <?=htmlentities('<br />')?>). Self-closing elements can be embedded
  with content (as the embed method from the parent container class is not overridden), but none of this
  content will be rendered.
</p>
<p>
  Lastly, the /html.php file contains helper functions and shortcuts for using oohtml more efficiently.
</p>

<h2>crafters, not controllers</h2>
<p>
  With oohtml, every controller is no longer just a controller - but an entire self-contained
  unit for generating pages. Every method isn't just the logic for a particular page, but
  also the view. So the term "controller" is no longer appropriate, and hence I call them "crafters."
</p>
<p>
  The parent of all crafters is the /libs/crafter.php file which defines the abstract crafter class.
  In my implementation, crafters keep all their page-methods protected or private, all page-methods
  have an _ (underscore) as their first character, and no arguments are ever passed to page-methods.
</p>
<ul>
  <li>"public function _index()" - not valid, won't be detected as a page-method, must be protected or private</li>
  <li>"protected function _index($arg=null)" - valid, but not recommended - in my implementation, no args are ever passed</li>
  <li>"protected static function _index()" - not valid, method cannot be static</li>
  <li>"protected function index()" - not valid, no preceding underscore</li>
</ul>
<p>
  Two abstract methods are: "public function _index()" and "public function _404()". These represent the index
  and 404 pages respectively.
</p>
<p>
  Since page-methods are protected, one must externally <em>request</em> a crafter to craft a page. This is done
  in my implementation via the "request($page)" method.
</p>
<p>
  Internally, the crafter also keeps track of all the valid page-methods it contains. This is done using reflection
  within the crafter's __construct() method.
</p>
<p>
  When a crafter is passed a request, it will check whether the request is for a valid page-method. If so, it'll 
  prepare itself to craft it. If not, it will instead prepare to craft a 404 page.
</p>
<p>
  In order to actually craft the requested page, one can either cast the crafter to a string (the crafter's
  __toString() method calls the craft() method) or manually invoke the craft() method. The former method has
  the disadvantage of being vague when Exceptions are thrown, so I recommend the latter method.
</p>
<p>
  Typically, all crafters follow the behaviour of crafting an index page by default if no request is made to it.
  However, this may be undesirable in certain scenarios and can be overridden.
</p>
<p>
  Within the site source, all child crafters are located within the /crafters directory.
</p>

<h2>template crafter</h2>
<p>
  There is one intermediary parent between the abstract crafter class and all other child crafters: the template_crafter.
  This crafter is responsible for defining the template for the entire website, as well as all helper methods for
  generating content and database (model) access.
</p>

<h2>models</h2>
<p>
  Currently, the site uses <?=a_link('http://www.phpactiverecord.org/','php.activerecord')?> as a database
  ORM. There is only one table: 'posts'. Here is the CREATE TABLE SQL:
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
</pre>

<p>
  InnoDB is used because I plan on separating the 'tags' field into another table
  and setting up FOREIGN KEY constraints. 
</p>

<h2>database access</h2>
<p>
  In my current setup, I have two users configured for access to the site database: a single public user who can
  only perform SELECT queries; and an admin user who can do shenanigans. No admin credentials are stored in any
  config files. Only the public access credentials are stored.
</p>
<p>
  By default, in /config.php, the "afeique.com" directory cloned from github is defined as the "BASE_PATH" constant.
  The public access credentials are stored one directory above BASE_PATH, which is a folder inaccessible by remote
  users on my current webhost.
</p>

<h2>admin access</h2>
<p>
  In order to login as an admin, one must know the actual SQL admin credentials. Once logged in, the
  credentials are stored in PHP $_SESSION variables. The template_crafter always checks whether 
  these variables are set and uses their contents to establish an admin connection automatically.
</p>

<h2>security concerns</h2>
<p>
  I am no security expert. I don't know what flaws are presented with the above scheme. If you have any thoughts
  on the matter, feel free to contact me.
</p>

<h2>helper classes</h2>
<p>
  There are only two helper classes: /libs/validate.php and /libs/error.php. The former is a helper for validating
  form inputs. It uses method-chaining to quickly apply a series of operations and validations on a particular input
  (either a string or an array) and can then generate an error string compiling all issues with the input.
</p>
<p>
  The latter is a helper for throwing Exceptions when an error occurs - primarily, when an incorrect argument type
  is passed to a method. Some errors are offloaded into classes that extend the error class in order that the error 
  class only contain the most general Exceptions thrown.
</p>