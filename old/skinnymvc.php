<?php
/************************************************************************************
 ***                                                                              ***
 *    SkinnyMVC for PHP                                                             *
 *                                                                                  *
 *    Author: Radoslav Gazo <rado@49research.com> :: http://radogazo.com            *
 *            Charles Iliya Krempeaux <charles@49research.com> http://changelog.ca  *
 *                                                                                  *
 *    Web:    http://skinnymvc.com                                                  *
 *            http://49research.com                                                 *
 *                                                                                  *
 *                                                                                  *
 *    SkinnyMVC License:                                                            *
 *                                                                                  *
 *    Copyright (c) 2009 49 Research, Inc.                                          *
 *                                                                                  *
 *    Permission is hereby granted, free of charge, to any person                   *
 *    obtaining a copy of this software and associated documentation                *
 *    files (the "Software"), to deal in the Software without                       *
 *    restriction, including without limitation the rights to use,                  *
 *    copy, modify, merge, publish, distribute, sublicense, and/or sell             *
 *    copies of the Software, and to permit persons to whom the                     *
 *    Software is furnished to do so, subject to the following                      *
 *    conditions:                                                                   *
 *                                                                                  *
 *    The above copyright notice and this permission notice shall be                *
 *    included in all copies or substantial portions of the Software.               *
 *                                                                                  *
 *    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,               *
 *    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES               *
 *    OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND                      *
 *    NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT                   *
 *    HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,                  *
 *    WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING                  *
 *    FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR                 *
 *    OTHER DEALINGS IN THE SOFTWARE.                                               *
 *                                                                                  *
 ***                                                                              ***
 ************************************************************************************/


$help = '
Usage:
  php skinnymvc.php task_name [argument]

Tasks:
  install       -  Installs new SkinnyMVC framework
  upgrade       -  Upgrades core SkinnyMVC files
  uninstall     -  Uninstalls the SkinnyMVC project (deletes all files in all SkinnyMVC directories)
  createModule  -  Creates new module (Ex.: "php skinnymvc.php createModule login")
  createMod     -  Alias for createModule
  generateSQL   -  Generates sql from schema.php and stores it in lib/skinnymvc/model/sql/database.sql
  generateModel -  Generates model classes from schema.php
  help          -  Displays this help

Other:
  * Make sure that your project\'s tmp directory is writable by the web server
  * Your custom error pages are located in templates

';

if ($argc < 2) {
  echo $help;
  exit;
}

$mvc = new MVC();

if (is_callable(array($mvc, $argv[1])) && $argv[1]!='main') {
  call_user_func_array(array($mvc, $argv[1]), array($argv)); //hack
} else {
  echo "Invalid argument!\n\n";
  echo $help;
}


Class MVC {

  private $nonkeys = array('_UNIQUE', '_UNIQUES', '_INDEX', '_INDEXES', '_PRIMARY_KEY', '_FOREIGN_KEY', '_FOREIGN_KEYS', '_FULLTEXT', '_DATABASE_KEY', '_TABLE_NAME');

  public function __construct() {
  }

  public function main() {

    if (!in_array($argv[1], $functions)) {
      echo "Unknown parameter: $argv[1]\n";
    } else {
      
    }
  }

  public function help() {
     global $help;
     echo $help;
  }

 /**
  * Installs MVC
  */
  public function install() {
    if (file_exists('modules') || file_exists('config') || file_exists('lib') || file_exists('web'))
    {
      echo "SkinnyMVC already installed.\n";
      exit;
    }
    // CREATE DIRECTORY STRUCTURE
    mkdir('modules'); //business logic
    mkdir('templates');
    mkdir('config');
    mkdir('lib');
    mkdir('lib/skinnymvc');
    mkdir('lib/skinnymvc/dbcontroller');      //controller classes
    mkdir('lib/skinnymvc/dbcontroller/base'); //base controller classes
    mkdir('lib/skinnymvc/controller');      //controller classes
    mkdir('lib/skinnymvc/controller/base'); //base controller classes
    mkdir('lib/skinnymvc/core'); //core (original) classes
    mkdir('lib/skinnymvc/class'); //custom classes
    mkdir('lib/skinnymvc/model'); //extended db classes
    mkdir('lib/skinnymvc/model/base'); //Base db classes
    mkdir('lib/skinnymvc/model/sql'); //db sql code
    mkdir('plugins');
    mkdir('web');
    mkdir('web/images');
    mkdir('web/css');
    mkdir('web/js');
    mkdir('tmp');

    $this->installSettings();
    $this->installIndex();
    $this->installController();
    $this->installSkinnyUser();
    $this->installLayout();
    $this->installSchema();
    $this->installBaseModel();
    $this->installException();
    $this->installSkinnyActions();
    $this->installDbController();
    $this->installErrorPages();
    $this->createMod(array('skinnymvc.php','createMod','default'));

    //README for plugins
    $s = '
       Put your plugins here.
       The file name of a plugin must be formatted like this:
            skinnyPlugin*.php

       For example:
           skinnyPluginCaptcha.php
    ';

    @file_put_contents('plugins/README', $s);

    //main README
    $s= '
Please add the following settings to httpd.conf with the proper values filled out.

<VirtualHost *:80>
    ServerName   [your domain name]
    ServerAdmin  [your email]
    DocumentRoot [path to your project]/web
    php_value include_path .:[path to your project]
    <Directory "[path to your project]/web">
     AllowOverride All
     Allow from All
    </Directory>
</VirtualHost>
    ';

    @file_put_contents('README', $s);
  }

/**
 * updates core skinnyMVC files;
 */
public function upgrade() {
    $this->installIndex();
    $this->installController();
    $this->installSkinnyUser();
    $this->installBaseModel();
    $this->installException();
    $this->installSkinnyActions();
    $this->installDbController();
}


 /**
  * Deletes the whole project
  */
  public function uninstall() {
    echo "This will delete ALL files in the project. Are you sure? [y/N]";
    flush();
    @ob_flush();
    $confirmation  =  trim( fgets( STDIN ) );
    if ( $confirmation !== 'y' ) {
      exit;
    }
    $this->deleteDirectory('modules');
    $this->deleteDirectory('templates');
    $this->deleteDirectory('config');
    $this->deleteDirectory('lib');
    $this->deleteDirectory('plugins');
    $this->deleteDirectory('web');
    $this->deleteDirectory('tmp');
  }

 /** 
  * Creates a new application in module folder
  */
  public function createModule($argv) {
    $this->createMod($argv);
  }

  public function createMod($argv) {

    if (!file_exists('modules')) {
      echo "Invalid SkinnyMVC installation.\n";
      exit;
    }

    if(empty($argv[2])) {
      echo "Usage: ".$argv[0]." createMod module\n";
      return;
    }

    if(file_exists('modules/'.$argv[2])) {
      echo "Module '$argv[2]' already exists!\n";
      return;
    }
    mkdir('modules/'.$argv[2]);
    mkdir('modules/'.$argv[2].'/actions');
    mkdir('modules/'.$argv[2].'/templates');

    $this->installActions($argv[2]);
    $this->installTemplate($argv[2]);
  }

  /**
   * Generates SQL code using schema.php
   */
   public function generateSQL() {
     if(!file_exists('config/schema.php')) {
       //Error
       echo "File schema.php does not exist!\n";
       exit;
     }

     if(!file_exists('config/settings.php')) {
       //Error
       echo "File settings.php does not exist!\n";
       exit;
     }

     $sql = '';

     include('config/schema.php');
     include('config/settings.php');

     if (!empty($model) && is_array($model) && count($model)>0) {
        $sql = $this->createSQLFromArray($model);
     } else {
       //Error
       echo "File schema.php is empty!\n";
       exit;
     }

     if (!empty($sql)) {
        if (count($sql)==1 && isset($sql['__database'])) {
          @file_put_contents('lib/skinnymvc/model/sql/database.sql', $sql['__database']);
          return;
        }

        foreach($sql as $dbName=>$dbSQL) {
            @file_put_contents('lib/skinnymvc/model/sql/'.$dbName.'.sql', $dbSQL);
        }
     }
   }


  /**
   * Generates Model code using schema.php
   */
   public function generateModel() {
     if(!file_exists('config/schema.php')) {
       //Error
       echo "File schema.php does not exist!\n";
       exit;
     }

     include('config/schema.php');
     include('config/settings.php');

     if (!empty($model) && is_array($model) && count($model)>0) {
        $this->createModelfromArray($model);
     }
   }

/*    *******************************************************************************    */
/*    ************************  P R I V A T E   M E T H O D S ***********************    */
/*    *******************************************************************************    */

 /**
  * Recursively deletes a directory
  * @param $dir
  * @return 
  */
  private function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir) || is_link($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
      if ($item == '.' || $item == '..') continue;
      if (!$this->deleteDirectory($dir . "/" . $item)) {
        chmod($dir . "/" . $item, 0777);
        if (!$this->deleteDirectory($dir . "/" . $item)) return false;
      }
    }
    return rmdir($dir);
  } 
/*    *******************************************************************************    */


 /**
  * Generates the project settings file
  */
  private function installSettings() {
    $s = '<?php
/******************************
 * filename:    settings.php
 * description: Project settings. 
 *              To edit, change the values on right side of the name-value pairs.
 */

class SkinnySettings { public static $CONFIG = array(


"project name"    => "SkinnyMVC Project",
"debug"           => false,
"preload model"   => true,  //true = all model classes will be loaded with each request;
                            //false = model classes will be loaded only if explicitly required (use require_once)

"session persistency" => true, //tmp in your project dir must be writeable by the server!
"session timeout" => 1800, //in seconds!

"unauthenticated default module" => "default", //set this to where you want unauthenticated users redirected.
"unauthenticated default action" => "index",

"dbdriver"        => "mysql",
"dbname"          => "db",
"dbhost"          => "127.0.0.1",
"dbuser"          => "user",
"dbpassword"      => "password",

// To use multiple databases, keep the code above with default values
// and add a new setting like this:
//   "dbs" => array(
//                   "database1"=> array(
//                                       "dbdriver"   => "mysql",
//                                       "dbname"     => "db",
//                                       "dbhost"     => "127.0.0.1",
//                                       "dbuser"     => "user",
//                                       "dbpassword" => "password",
//                                      ),
//                   "database2"=> array(
//                                       "dbdriver"   => "mysql",
//                                       "dbname"     => "db",
//                                       "dbhost"     => "127.0.0.1",
//                                       "dbuser"     => "user",
//                                       "dbpassword" => "password",
//                                      ),
//                ),
//
 

);}
    ';
    @file_put_contents('config/settings.php', $s);
  } //end private function installSettings
/*    *******************************************************************************    */


 /**
  * Generates blank schema file
  */
  private function installSchema() {
    $s="<?php
/**
 * Use the schema to generate database.sql file and the model files
 *
 * To create database.sql:
 *                          php skinnymvc.php generateSQL
 *
 *    database.sql will be stored in lib/skinnymvc/model/sql
 *
 * To create the model files:
 *                          php skinnymvc.php generateModel
 *
 *    model files will be stored in lib/skinnymvc/model
 *
 * Example schema code:
 * \$model = array('table1'=>array(
 *                                 'field1'=>array('type'=>'int', 'null'=>false, 'special'=>'auto_increment'),
 *                                 'field2'=>'datetime',
 *                                 'field3'=>'varchar(255)',
 *                                 '_INDEXES'=>array('field3'),
 *                                 '_PRIMARY_KEY'=>array('field1'),
 *                               ),
 *                'table2'=>array(
 *                                 'field1'=>array('type'=>'int', 'null'=>false, 'special'=>'auto_increment'), //null is false by default
 *                                 'field2'=>'decimal(10,4)',
 *                                 'field3'=>'varchar(255)',
 *                                 '_INDEXES'=>array( array('field3','field4') ),
 *                                 '_PRIMARY_KEY'=>array('field1'),
 *                               ),
 *                'table3'=>array(
 *                                 'field1'=>array('type'=>'int', 'null'=>false, 'special'=>'auto_increment'),
 *                                 'field2'=>array('type'=>'varchar(255)', 'null'=>false),
 *                                 'field3'=>'text',
 *                                 'field4'=>'int',
 *                                 'field5'=>'int',
 *                                 '_UNIQUES'=>array( 'field2', array('field4','field5') ),
 *                                 '_FULLTEXT'=>array('field3'),
 *                                 '_PRIMARY_KEY'=>array('field1'),
 *                                 '_FOREIGN_KEYS'=>array('field4'=>array('table'=>'table1','field'=>'field1'), 'field5'=>array('table'=>'table2','field'=>'field1')),
 *                                 '_DATABASE_KEY'=>'db_key',
 *                                 '_TABLE_NAME'=>'table_name',
 *                               ),
 *                  );
 *
 */
";
   @file_put_contents('config/schema.php', $s);
  } //end private function installSchema
/*    *******************************************************************************    */


 /**
  * Generates the project index file
  */
  private function installIndex() {
   $s = '<?php
/******************************
 * filename: index.php
 */

    require_once(\'../config/settings.php\');
    require_once(\'../lib/skinnymvc/controller/SkinnyController.php\');

    $c = new SkinnyController;
    $c->main();

   ';

   @file_put_contents('web/index.php', $s);

   $s = '<?php
/******************************
 * filename: dev.php
 */

    require_once(\'../config/settings.php\');
    require_once(\'../lib/skinnymvc/controller/SkinnyController.php\');
    require_once(\'../lib/skinnymvc/core/SkinnyException.php\');

    SkinnySettings::$CONFIG[\'debug\'] = true;

    $__DEBUG = array(\'sql\'=>array(), \'data\'=>array());

    try {
      $c = new SkinnyController;
      $c->main();
    } catch (SkinnyException $e) {
      echo "<h4>Exception</h4>";
      echo "<div>$e->getMessage()</div>";
      echo "<div>$e->getTrace()</div>";
    }

    echo "<div><pre>".var_export($__DEBUG, true)."</pre></div>";
    ';

    @file_put_contents('web/dev.php', $s);

    $s = '';
    @file_put_contents('web/css/main.css', $s);

    $s= 'Options +FollowSymLinks +ExecCGI

<IfModule mod_rewrite.c>
  RewriteEngine On

  RewriteRule ^(.*)/([^/\.]+)\.(gif|jpg|png)$ images/$2.$3 [L]
  RewriteRule ^(.*)/([^/\.]+)\.js$ js/$2.js [L]
  RewriteRule ^(.*)/([^/\.]+)\.css$ css/$2.css [L]

  RewriteRule ^(dev.php)/([^/\.]+)/?([^/\.]+)?/?$ dev.php?__module=$2&__action=$3&%{QUERY_STRING}  [L]
  RewriteRule ^(index.php/)?([^/\.]+)/?([^/\.]+)?/?$ index.php?__module=$2&__action=$3&%{QUERY_STRING}  [L]

</IfModule>
    ';
    @file_put_contents('web/.htaccess', $s);

  } // end private function installIndex
/*    *******************************************************************************    */


 /**
  * Generates the project controller
  */
  private function installController() {
   $s = '<?php
/******************************
 * filename:    SkinnyBaseController.php
 * description: The main application controller. Every request goes through here.
 */

class SkinnyBaseController {

    protected $app = null;
    protected $module = null;
    protected $action = null;
    protected $param = null;
    protected $skinnyUser = null;

    protected $allowModulesAsFiles = false;
    protected $allowActionsAsFiles = false;
    protected $fixMisspellings     = true;

    public function __construct()
    {
        // Nothing here.
    }

  /**
   * The main controller script, running with every request.
   */
    public function main()
    {
        //
        // Get the Module and Action from the CGI parameters.
        //
            if (isset($_GET[\'__action\']) && !empty($_GET[\'__action\'])) {
                $action = $_GET[\'__action\'];
            } else {
                $action = \'index\';
            }

            if (isset($_GET[\'__module\']) && !empty($_GET[\'__module\'])) {
                $module = $_GET[\'__module\'];
            } else {
                $module = \'default\';
                $action = \'index\';
            }


        //
        // Set up $param.
        //
            $paramGET = $_GET;
            unset($paramGET[\'__module\']);
            unset($paramGET[\'__action\']);

            $param = array(\'GET\'=>$paramGET, \'POST\'=>$_POST, \'FILES\'=>$_FILES);


        //
        // Set up variable that are used by the run() method.
        //
            $this->module = $module;
            $this->action = $action;
            $this->param  = $param;


        //
        // Handle the missing slashes if there are any.
        //

           // Slash after the module missing?
            $hasMissingSlash = \'\' == @$_GET[\'__action\']
                            && \'/\' == substr($_SERVER[\'REQUEST_URI\'],0,1) 
                            && 1 < strlen($_SERVER[\'REQUEST_URI\']) 
                            && FALSE == strpos($_SERVER[\'REQUEST_URI\'],\'/\',1)
                             ;
            if (  $hasMissingSlash  ) {

                if (  $this->allowModulesAsFiles   ) {

                    // Nothing here.

                } else if (  $this->fixMisspellings  ) {

                    if (  \'\' != $this->module && \'\' == @$_GET[\'__action\']  ) {
                        $href = \'/\' . $this->module . \'/\';
                        header(\'Location: \'.$href);
                        exit();
                    }

                } else {
                    //Error: Action does not exist
                    header("HTTP/1.1 404 Not Found");
                    echo file_get_contents("../templates/404.php");
                    exit;
                }
            }

           // Slash after the action missing?
           $hasMissingSlash = \'\' != @$_GET[\'__action\']
                            && \'/\' == substr($_SERVER[\'REQUEST_URI\'],0,1) 
                            && 1 < strlen($_SERVER[\'REQUEST_URI\']) 
                            && FALSE !== strpos($_SERVER[\'REQUEST_URI\'],\'/\',1)
                            && FALSE ==  strpos($_SERVER[\'REQUEST_URI\'],\'/\', strpos($_SERVER[\'REQUEST_URI\'],\'/\',1))
                             ;
            if (  $hasMissingSlash  ) {

                if (  $this->allowActionsAsFiles   ) {

                    // Nothing here.

                } else if (  $this->fixMisspellings  ) {

                    if (  \'\' != $this->module && \'\' != @$_GET[\'__action\']  ) {
                        $href = \'/\' . $this->module . \'/\'. $this->action .\'/\';
                        header(\'Location: \'.$href);
                        exit();
                    }

                } else {
                    //Error: Action does not exist
                    header("HTTP/1.1 404 Not Found");
                    echo file_get_contents("../templates/404.php");
                    exit;
                }
            

            }


        //Get the core classes
        $this->require_once_many("../lib/skinnymvc/core/*.php");

        // Get the db controller classes
        $this->require_once_many("../lib/skinnymvc/dbcontroller/base/*.php");
        $this->require_once_many("../lib/skinnymvc/dbcontroller/*.php");

        // Get the controller classes
        $this->require_once_many("../lib/skinnymvc/controller/base/*.php");
        $this->require_once_many("../lib/skinnymvc/controller/*.php");

        //Initialize session
        if (SkinnySettings::$CONFIG[\'session persistency\']) {
            $this->skinnyUser = SkinnyUser::getUser();
        }

        //Get all Model classes
        if (SkinnySettings::$CONFIG[\'preload model\']) {
            $this->require_once_many("../lib/skinnymvc/model/*.php");
            $this->require_once_many("../lib/skinnymvc/model/base/*.php");
        }

        //Get all plugins
        $this->require_once_many("../plugins/skinnyPlugin*.php");


        //
        // Call the run() method.
        //
            $this->run();

    }


    public function run()
    {
        $this->executeModuleAction($this->module, $this->action, $this->param);
    }


    private function executeModuleAction($module, $action, $param)
    {

        if (!file_exists("../modules/$module/actions/actions.php")) {
            //Error: Action does not exist
            header("HTTP/1.1 404 Not Found");
            echo file_get_contents("../templates/404.php");
            exit;
        }
        require_once("../modules/$module/actions/actions.php");

        $moduleClass = self::camelize($module) . \'Actions\';

        $actionMethod = \'execute\'.self::camelize($action);

        $moduleObj = new $moduleClass();

        $skinnyUser = $this->skinnyUser;

        if (!empty($skinnyUser)) {
            $moduleObj->setSkinnyUser($skinnyUser);
            if ($moduleObj->authenticatedOnly()) {
                if (!$skinnyUser->isAuthenticated()) {
                    //Not authenticated!
                    if (isset(SkinnySettings::$CONFIG[\'unauthenticated default module\'])) {
                        if (isset(SkinnySettings::$CONFIG[\'unauthenticated default action\'])) {
                            $moduleObj->redirect(SkinnySettings::$CONFIG[\'unauthenticated default module\'], SkinnySettings::$CONFIG[\'unauthenticated default action\']);
                        } else {
                            $moduleObj->redirect(SkinnySettings::$CONFIG[\'unauthenticated default module\'], "index");
                        }
                    } else {
                        $moduleObj->redirect("default", "index");
                    }
                }
            }
        }

        if (empty($moduleObj)) {
            //Error: Module does not exist
            header("HTTP/1.1 404 Not Found");
            echo file_get_contents("../templates/404.php");
            exit;
        }

        // The action should return an array of all values that will be needed in the template
        if ( $moduleObj->allowUndefinedActions()) {

            $data = array();
            if (  is_callable(array($moduleObj, $actionMethod))  ) {
                $data = call_user_func_array(array($moduleObj, $actionMethod), array($param));
            } else {
                if (!file_exists("../modules/$module/templates/$action.php")) {
                    //Error: Action does not exist
                    header("HTTP/1.1 404 Not Found");
                    echo file_get_contents("../templates/404.php");
                    exit;
                }
            }
            if (SkinnySettings::$CONFIG[\'debug\']) {
                global $__DEBUG;
                array_push($__DEBUG[\'data\'], $data);
            }
        } else if (is_callable(array($moduleObj, $actionMethod))) {
            $data = call_user_func_array(array($moduleObj, $actionMethod), array($param));
            if (SkinnySettings::$CONFIG[\'debug\']) {
                global $__DEBUG;
                array_push($__DEBUG[\'data\'], $data);
            }
        } else {
            //Error: Action $action does not exist
            header("HTTP/1.1 404 Not Found");
            echo file_get_contents("../templates/404.php");
            exit;
        }

        //Process the templates
        if (!file_exists("../modules/$module/templates/$action.php")) {
            //Error
            throw new SkinnyException("Template for module $module, action $action does not exist.");
            exit;
        }

        $actionTemplateSource = file_get_contents("../modules/$module/templates/$action.php");

        ob_start();
        $this->processTemplate($data, $skinnyUser, $actionTemplateSource);

        $skinny_content = ob_get_clean();

        //Run the layout;
        include("../templates/layout.php");

        flush();
        ob_flush();

        //clean up old sessions
        $rand = rand(0, 99);
        if ($rand == 1) {
            SkinnyUser::cleanup();
        }
    }

  /**
   * Turns "foo_bar" into "FooBar"
   * @param string $str
   * @return string Camelized $str
   */
   public static function camelize($str)
   {
     $str = str_replace("_", " ", $str);
     $str = ucwords($str);
     $str = str_replace(" ", "", $str);
     return $str;
   }

   private function processTemplate($skinnyData, $skinnyUser, $skinnyTemplateSourceData) {
      eval(\'?>\'.$skinnyTemplateSourceData."\n");
   }


   private function require_once_many($pattern)
   {
      foreach(glob($pattern) as $class_filename) {
         require_once($class_filename);
      }
   }

    protected function moduleExists($moduleName)
    {
        return file_exists(\'../modules/\'. $moduleName .\'/actions/actions.php\');
    }

} // class SkinnyBaseController

';

   @file_put_contents('lib/skinnymvc/controller/base/SkinnyBaseController.php', $s);





    $s = '<?php
/******************************
 * filename:    SkinnyController.php
 * description: The main application controller. Every request goes through here.
 */

require_once(\'base/SkinnyBaseController.php\');

class SkinnyController extends SkinnyBaseController 
{

    public function __construct()
    {
        // Nothing here.
    }

    public function run()
    {
        // Put code here to rewrite the routing rules, or whatever.
        //
        // To make this happen, set the following fields to change the routing (and then call parent::run() )...
        //
        //     $this->module
        //     $this->action
        //     $this->param
        //
        //
        // For example, to make it so URLs like...
        //
        //     http://example.com/book/1234
        //     http://example.com/book/51238
        //     http://example.com/book/7
        //
        // ... work as if they were the URLs...
        //
        //     http://example.com/knowledgebase/item?ID=1234
        //     http://example.com/knowledgebase/item?ID=51238
        //     http://example.com/knowledgebase/item?ID=7
        //
        // ... we use the following code...
        //
        //     if (  \'book\' == $module  ) {
        //
        //         $ID = $this->action;
        //
        //         $this->param[\'GET\'][\'ID\'] = $ID;
        //         $this->module = \'knowledgebase\';
        //         $this->action = \'item\';
        //     }
        //
        //
        // Or for a more complex example, to make it so URLs like...
        //
        //     http://example.com/joe
        //     http://example.com/john
        //     http://example.com/jen
        //
        // ... work as if they were the URLs...
        //
        //     http://example.com/user/defaul?username=joe
        //     http://example.com/user/defaul?username=john
        //     http://example.com/user/defaul?username=jen
        //
        // ... EXCEPT in cases where there is actually a module for that, like...
        //
        //     http://example.com/settings
        //     http://example.com/about
        //     http://example.com/contact
        //
        // ... we use code like...
        //
        //     if (  ! $this->moduleExists($this->module)  ) {
        //         $this->module = \'user\';
        //         $this->action = \'default\';
        //         $this->param[\'GET\'][\'username\'] = $this->module;
        //     }
        //



        // This MUST stay here!
        parent::run();
    }

} // class SkinnyController

    ';

    $filename = 'lib/skinnymvc/controller/SkinnyController.php';
    if (  !file_exists($filename)  ) {
        @file_put_contents($filename, $s);
    }




  } //end private function installController
/*    *******************************************************************************    */


/**
 *
 */
 private function installSkinnyUser() {
    $s = '<?php
/******************************
 * filename:    SkinnyUser.php
 * description: Holds session stuff
 *              This version requires working session persistency!
 */

class SkinnyUser {

   private $authenticated = false;

   private $timeout = 1800;

   private $last_accessed = 0;

   private $attributes = array();

   private function SkinnyUser() {
   }

  /**
   * Gets the existing User or creates a new one
   */
   public static function getUser() {
      if (!SkinnySettings::$CONFIG[\'session persistency\']) {
        throw new SkinnyException("Session persistency not enabled");
      }
      $sess = null;
      session_start();

      if (file_exists("../tmp/".session_id().".session")) {
         $sess = unserialize(@file_get_contents("../tmp/".session_id().".session"));
         $session_inactive = time() - $sess->last_accessed;
         if ($session_inactive > $sess->timeout) {
             $sess->last_accessed = time();
             $sess->setAuthenticated(false);
             $sess->save();
         } else {
             $sess->last_accessed = time();
             $sess->save();
         }
      } else {
         $sess = new SkinnyUser();
         $sess->timeout = SkinnySettings::$CONFIG[\'session timeout\'];
         $sess->last_accessed = time();
         $sess->save();
      }
      return $sess;
   }

  /**
   * @return boolean Is the user authenticated?
   */
   public function isAuthenticated() {
     return $this->authenticated;
   }

  /**
   * Sets the user to authenticated or unauthenticated
   * @param boolean $authenticated 
   */
   public function setAuthenticated($authenticated) {
     if (!is_bool($authenticated)) {
        throw new SkinnyException("Authentication value must be boolean");
     }

     $this->authenticated = $authenticated;
     $this->save();     
   }

  /**
   * Returns the value of a saved attribute
   * @param string $name Name of the attribute
   * @return mixed Value of the attribute or null if the attribute was not found
   */
   public function getAttribute($name) {
     if(isset($this->attributes[$name])) {
        return $this->attributes[$name];
     } else {
        return null;
     }
   }

  /**
   * Saves an attribut in the session
   * @param string $name Name of the attribute
   * @param mixed $value Value of the attribute
   */
   public function setAttribute($name, $value) {
      $this->attributes[$name] = $value;
      $this->save();
   }

  /**
   * Deletes an attibute that was stored in the session
   * @param string $name Name of the attribute
   */
   public function deleteAttribute($name) {
      if(isset($this->attributes[$name])) {
        unset($this->attributes[$name]);
      }
      $this->save();
   }

  /**
   * Gets the current session timeout value
   * @return int Timeout in seconds
   */ 
   public function getTimeout() {
      return $this->timeout;
   }

  /**
   * Make user data persistent
   */
   public function save() {
      $data = serialize($this);
      return file_put_contents("../tmp/".session_id().".session", $data);
   }

  /**
   * Destroys the session - removes user file
   */
   public function destroy() {
      return @unlink("../tmp/".session_id().".session");
   }

   //clean up the tmp dir
   public static function cleanup() {
      if ($handle = opendir("../tmp")) {
         while (false !== ($file = readdir($handle))) {
           $diff = time() - filemtime("../tmp/".$file);
           if ($diff>SkinnySettings::$CONFIG["session timeout"]){
              @unlink("../tmp/$file");
           }
         }
      }
   }
}
    ';

     @file_put_contents('lib/skinnymvc/core/SkinnyUser.php', $s);
 } //end private function installSkinnyUser


 /**
  * Creates SkinnyException class
  */
  private function installException() {
    $s = '<?php
/******************************
 * filename:    SkinnyException.php
 * description: main Exception class
 */

class SkinnyException extends Exception {
}
    ';
    @file_put_contents('lib/skinnymvc/core/SkinnyException.php', $s);
  } //end private function installException
/*    *******************************************************************************    */


 /**
  * Creates SkinnyActions class - the base class for actions
  */
  private function installSkinnyActions() {
    $s = '<?php
/******************************
 * filename:    SkinnyActions.php
 * description: main Actions class
 */

class SkinnyActions {

   protected $skinnyUser = null;

   protected $authenticatedOnly = false;

   protected $allowUndefinedActions = false;

  /**
   * Attaches the session-user to this action
   * @param SkinnyUser $skinnyUser
   */
   public function setSkinnyUser($skinnyUser) {
      $this->skinnyUser = $skinnyUser;
   }

  /**
   * Gets the current SkinnyUser - session
   * @return SkinnyUser
   */
   public function getSkinnyUser() {
      return $this->skinnyUser;
   }

  /**
   * Is this module restricted?
   * @return boolean
   */
   public function authenticatedOnly() {
      return $this->authenticatedOnly;
   }

  /**
   * Does module allow undefined actions?
   * @return boolean
   */
   public function allowUndefinedActions() {
      return $this->allowUndefinedActions;
   }

  /**
   * Redirects the browser to a new page (modue and action)
   * @param string $module
   * @param string $action
   * @param array $request
   */
   public function redirect($module=\'default\', $action=\'index\', $request=array(\'GET\'=>array(), \'POST\'=>array())) {
      $param = self::getRelativeRoot()."$module/$action/";
      $loop = 0;
      if(isset($request[\'GET\'])) {
         foreach ($request[\'GET\'] As $key=>$value) {
            if ($loop == 0) {
              $param .= "?";
            } else {
              $param .= "&";
            }
            $param .= "$key=$value";
             $loop++;
         }
      }
      if(isset($request[\'POST\'])) {
         foreach ($request[\'POST\'] As $key=>$value) {
             if ($loop == 0) {
              $param .= "?";
            } else {
              $param .= "&";
            }
            $param .= "$key=$value";
            $loop++;
         }
      }

      if (SkinnySettings::$CONFIG[\'debug\']) {
         header( "Location: /dev.php".$param );
      } else {
         header( "Location: ".$param );
      }
      exit;
   }

  /**
   * Makes a call to the specified module+action and returns back to the caller
   * @param string $module
   * @param string $action
   * @param array $request
   * @return array
   */
   public function call($module=\'default\', $action=\'index\', $request=array(\'GET\'=>array(), \'POST\'=>array())) {
      $moduleClass = SkinnyController::camelize($module) . \'Actions\';

      $actionMethod = \'execute\'.SkinnyController::camelize($action);

      $moduleObj = new $moduleClass();

      if ($moduleObj->authenticatedOnly()) {
        if (!$this->skinnyUser->isAuthenticated()) {
            //Not authenticated!
            return null;
        }
      }

      if (is_callable(array($moduleObj, $actionMethod))) {
        $data = call_user_func_array(array($moduleObj, $actionMethod), array($request));
        if (SkinnySettings::$CONFIG[\'debug\']) {
           global $__DEBUG;
           array_push($__DEBUG[\'data\'], $data);
        }
      }
      return $data;
   }

  /**
   * Gets the relative root directory of the project - useful, if installed in a subdir.
   * @return string
   */
   public static function getRelativeRoot() {
      $rel_path = str_replace($_SERVER[\'DOCUMENT_ROOT\'], \'\', $_SERVER[\'SCRIPT_FILENAME\']);
      if ($rel_path == "index.php"){
        $rel_path="/";
      } else if ($rel_path == "dev.php") {
        $rel_path="/dev.php/";
      } else {
        $rel_path = substr($rel_path, 0, strrpos($rel_path, "/")+1);
      }
      return $rel_path;
   }
}
    ';
    @file_put_contents('lib/skinnymvc/core/SkinnyActions.php', $s);
  } //end private function installActions
/*    *******************************************************************************    */


 /**
  * Generates actions.php for the module
  */
  private function installActions($module)
  {
    $moduleClassName = $this->camelize($module)."Actions";
    $s = '<?php
/******************************
 * filename:    '.$module.'.php
 * description:
 */

class '.$moduleClassName.' extends SkinnyActions {

   public function __construct()
   {
   }

  /**
   * The actions index method
   * @param array $request
   * @return array
   */
   public function executeIndex($request)
   {
      // return an array of name value pairs to send data to the template
      $data = array();
      return $data;
   }

}';

  @file_put_contents('modules/'.$module.'/actions/actions.php', $s);
  } //end private function installActions
/*    *******************************************************************************    */


 /**
  * Generates default template for a module
  */
  private function installTemplate($module)
  {
    if ($module=='default') {
      $output = ' echo "   You have successfuly installed SkinnyMVC."; ';
    } else {
      $output = " /* Put your code here */ ";
    }

    $s = '   <h1>Under Construction</h1>
<?php
'.$output.'
';
    @file_put_contents('modules/'.$module.'/templates/index.php', $s);

    //README for plugins
    $s = '
       This directory contains your module-action templates.

       A template must be named after the associated action. 
       For example, if the associated action is "list", then the file name of the template must be "list.php"
    ';

    @file_put_contents('modules/'.$module.'/templates/README', $s);

  } //end private function installTemplate
/*    *******************************************************************************    */


 /**
  * Generates the main template file
  */
  private function installLayout()
  {
    $s = '<html>
 <head>
  <title>SkinnyMVC Project</title>
  <meta http-equiv="Content-Type" content="text/html" />
  <meta name="keywords" content="SkinnyMVC" />
  <meta name="description" content="SkinnyMVC Project" />
  <meta name="robots" content="index,follow" />
  <link rel="stylesheet" type="text/css" media="screen" href="css/main.css" />
 </head>
 <body>
  <?php echo $skinny_content ?>

  <div id="SkinnyMVCAttribution" style="font-size:8pt;margin-bottom:8px;">Powered by <a href="http://skinnymvc.com">SkinnyMVC</a></div>
 </body>
</html>';

    @file_put_contents('templates/layout.php', $s);

    $s = '
This directory contains the main project template (layout.php). This template contains the HTML wrapper for all pages.

The line with <?php echo $skinny_content ?> outputs the processed model-action templates. 
    ';

    @file_put_contents('templates/README', $s);
  } // end private function installLayout
/*    *******************************************************************************    */


 /**
  * Generates the SkinnyDbController Class
  */
  private function installDbController() {

    $s = '<?php
/**
 * filename:    SkinnyBaseDbController.php
 * description: Database controller
 */

class SkinnyBaseDbController extends PDO {

   protected static $connections = array();

   protected function SkinnyBaseDbController($mode, $dsn, $username=null, $password=null, $driver_options=null ) {
     parent::__construct($dsn, $username, $password, $driver_options);
     self::$connections[$mode] = $this;
   }

  /**
   * Gets the existing DB Connection or creates a new one
   * @param string $dbKey
   * @return SkinnyDbController
   */
   public static function getConnection($dbKey = null, $mode=\'r+\') {

     if (!isset(self::$connections[$mode]) || empty(self::$connections[$mode])) {

       if (  array_key_exists("dbs", SkinnySettings::$CONFIG) && is_array(SkinnySettings::$CONFIG["dbs"]) && array_key_exists($dbKey, SkinnySettings::$CONFIG["dbs"])  ) {
         $db_config = SkinnySettings::$CONFIG["dbs"][$dbKey];
         $dbName = null;
       } else {
         $db_config = SkinnySettings::$CONFIG;
         $dbName = $dbKey;
       }

       if (empty($dbName)) {
         $dbName = $db_config["dbname"];
       }

       if ($db_config["dbhost"] == "127.0.0.1") {
         $dsn = $db_config["dbdriver"].":dbname=".$dbName;
       } else {
         $dsn = $db_config["dbdriver"].":dbname=".$dbName.";host=".$db_config["dbhost"];
       }

       $dsn = $db_config["dbdriver"].":dbname=".$dbName.";host=".$db_config["dbhost"];
       try {
         return new SkinnyDbController($mode, $dsn, $db_config["dbuser"], $db_config["dbpassword"]);
       } catch (PDOException $e) {
         throw new SkinnyException($e->getMessage(), $e->getCode());
       }
     } else {
       return self::$connections[$mode];
     }
   }

    public static function getReadConnection($dbKey = null)
    {
        // TODO after PHP 5.3 becomes more common: return static::getConnection($dbKey, "r");
        return self::getConnection($dbKey, "r");
    }

    public static function getWriteConnection($dbKey = null)
    {
        // TODO after PHP 5.3 becomes more common: return static::getConnection($dbKey, "w");
        return self::getConnection($dbKey, "w");
    }
}
    ';
    @file_put_contents('lib/skinnymvc/dbcontroller/base/SkinnyBaseDbController.php', $s);


    $s = '<?php
/**
 * filename:    SkinnyDbController.php
 * description: Database controller
 */

class SkinnyDbController extends SkinnyBaseDbController {

}

    ';

    $filename = 'lib/skinnymvc/dbcontroller/SkinnyDbController.php';
    if (  !file_exists($filename)  ) {
        @file_put_contents($filename, $s);
    }

  }//end 


 /**
  * Generates the base model class
  */
  private function installBaseModel() {
    $s = '<?php
/**
 * filename:    BaseModel.php
 * description: Base class for all Base model classes
 */

abstract class SkinnyBaseModel {
   
  /**
   * Gets SQL query results
   * @param string $tableName Name of the affected table
   * @param mixed $criteria
   * @return array
   */
    public static function selectArray($tableName, $criteria = array()) {

        if (  isset($criteria["sql"]) && is_string($criteria["sql"])  ) {

            $sql = $criteria["sql"];

        } else {

            //columns can be SQL or an array.
            if (empty($criteria[\'columns\'])) {
                $criteria[\'columns\'] = \'*\';
            } else {
                if (is_array($criteria[\'columns\'])) {
                    $criteria[\'columns\'] = implode (\',\', $criteria[\'columns\']);
                }
            }

            //joins are only SQL for now
            if (empty($criteria[\'joins\'])) {
                $criteria[\'joins\'] = \'\';
            }

            // group can be SQL or array
            if (empty($criteria[\'group\'])) {
                $criteria[\'group\'] = \'\';
            } else {
                if(is_array($criteria[\'group\'])) {
                    $criteria[\'group\'] = \'GROUP BY \'.implode(\',\',$criteria[\'group\']);
                }
            }

            //limit can be SQL or a STRING formatted like this: "LIMIT 10" or "LIMIT 5,10" or "10"
            if (empty($criteria[\'limit\'])) {
                $criteria[\'limit\'] = \'\';
            }else{
                if (is_numeric($criteria[\'limit\'])) {
                    $criteria[\'limit\'] = "LIMIT ".$criteria[\'limit\'];
                }
            }

            //offset can be SQL or a STRING formatted like this: "OFFSET 10" or "10"
            if (empty($criteria[\'offset\'])) {
                $criteria[\'offset\'] = \'\';
            }else{
                if (is_numeric($criteria[\'offset\'])) {
                    $criteria[\'offset\'] = "OFFSET ".$criteria[\'offset\'];
                }
            }

            //order can be SQL or array
            //   array(
            //         array(\'column\'=>\'column1\', \'direction\'=>\'desc\'),
            //         array(\'column\'=>\'column2\', \'direction\'=>\'desc\')
            //   );
            if (empty($criteria[\'order\'])) {
                $criteria[\'order\'] = \'\';
            } else {
                if(is_array($criteria[\'order\'])) {
                    $tmpOrder = "ORDER BY ";
                    foreach($criteria[\'order\'] As $order) {
                        if (is_array($order)) {
                            $tmpOrder .= $order[\'column\'].\' \'.$order[\'direction\'];
                        } else {
                            $tmpOrder .= $order;
                        }
                        $tmpOrder .= \', \';
                    }
                    $tmpOrder = substr($tmpOrder, 0, strlen($tmpOrder)-2);
                    $criteria[\'order\'] = $tmpOrder;
                }
            }

            //conditions could be SQL code or an array
            //if an array, it should look like this:
            //   array( 
            //          array(\'left\'=>\'column1\', \'condition\'=>\'<\',\'right\'=>\'10\'),
            //          array(\'left\'=>\'column1\', \'condition\'=>\'NOT NULL\'),
            //   );
            if (empty($criteria[\'conditions\'])) {
                $criteria[\'conditions\'] = \'\';
            } else {
                if (is_array($criteria[\'conditions\'])) {
                    $tmpConditions = \'WHERE\';
                    foreach($criteria[\'conditions\'] As $condition) {
                        if(is_array($condition)) {
                            if (empty($condition[\'left\'])) {
                                throw new SkinnyException(\'Missing left side of the condition.\');
                            }
                            if (empty($condition[\'condition\'])) {
                                throw new SkinnyException(\'Invalid condition.\');
                            }
                            if (!isset($condition[\'right\'])) {
                                if (!in_array(strtoupper($condition[\'condition\']), array(\'NOT NULL\', \'IS NULL\'))) {
                                    throw new SkinnyException(\'Missing right side of the condition.\');
                                } else {
                                    $tmpConditions .= $condition[\'left\'].\' \'.$condition[\'condition\'];
                                }
                            } else {
                                $tmpConditions .= $condition[\'left\'].\' \'.$condition[\'condition\'].\' \'.$condition[\'right\'];
                            }
                            $tmpConditions .= "\n AND ";
                        } else {
                            $tmpConditions .= $condition."\n AND ";
                        }
                    }
                    $tmpConditions .= " 1=1\n";
                    $criteria[\'conditions\'] = $tmpConditions;
                }
            }

            $sql = "SELECT ".$criteria[\'columns\']."\n"
                 ."FROM ".$tableName."\n";
            if (!empty($criteria[\'joins\'])) {
                $sql .= $criteria[\'joins\']."\n";
            }
            if (!empty($criteria[\'conditions\'])) {
                $sql .= $criteria[\'conditions\']."\n";
            }
            if (!empty($criteria[\'group\'])) {
                $sql .= $criteria[\'group\']."\n";
            }
            if (!empty($criteria[\'order\'])) {
                $sql .= $criteria[\'order\']."\n";
            }
            if (!empty($criteria[\'limit\'])) {
                $sql .= $criteria[\'limit\']."\n";
            }
            if (!empty($criteria[\'offset\'])) {
                $sql .= $criteria[\'offset\']."\n";
            }

        }


        if (SkinnySettings::$CONFIG[\'debug\']) {
            global $__DEBUG;
            array_push($__DEBUG[\'sql\'], $sql);
        }

        $con = SkinnyDbController::getReadConnection();
        $result = $con->query($sql);
        if (!empty($result)) {
            return $result->fetchAll();
        } else {
            return null;
        }
    }
}
    ';
    @file_put_contents('lib/skinnymvc/core/BaseModel.php', $s);


    $s = '<?php
/**
 * filename:    Model.php
 * description: Use this class for methods that you want to extend into all model classes.
 */

abstract class SkinnyModel extends SkinnyBaseModel {
}
    ';
    @file_put_contents('lib/skinnymvc/core/Model.php', $s);

  } //end private function installBaseModel
/*    *******************************************************************************    */

  /**
  * Creates SQL from an array
  */
  private function createSQLFromArray($model)
  {
     $return_sql = array();

     if (isset(SkinnySettings::$CONFIG['dbs']) && !empty(SkinnySettings::$CONFIG['dbs']) && is_array(SkinnySettings::$CONFIG['dbs'])) {
        $dbs = array();
        foreach(SkinnySettings::$CONFIG['dbs'] as $db_key=>$value) {
          array_push($dbs, $db_key);
          $return_sql[$db_key] = '';
        }
     }
     $return_sql['__database'] = ''; //default db

     foreach($model As $tableName=>$table) {
       if (isset($table['_TABLE_NAME']) && !empty($table['_TABLE_NAME'])) {
          $tableName = $table['_TABLE_NAME'];
       }

       //get the db driver (for ex. "mysql" or "pgsql")
       //we need this for stuff like "auto_increment" in $special
       if (isset(SkinnySettings::$CONFIG['dbs']) && !empty(SkinnySettings::$CONFIG['dbs']) && is_array(SkinnySettings::$CONFIG['dbs'])) {
         if (isset($table['_DATABASE_KEY']) && !empty($table['_DATABASE_KEY'])) {
           if (!in_array($table['_DATABASE_KEY'], $dbs)) {
              //Error
              echo "Invalid _DATABASE_KEY in table $tableName \n";
              exit;
           }
           $db_driver = SkinnySettings::$CONFIG['dbs'][$table['_DATABASE_KEY']]['dbdriver'];
         } else {
          $db_driver = SkinnySettings::$CONFIG['dbdriver'];
         }
       } else {
          if (isset($table['_DATABASE_KEY'])) {
             //Error
             echo "Invalid _DATABASE_KEY in table $tableName; dbs not specified in config; \n";
             exit;
          }
          $db_driver = SkinnySettings::$CONFIG['dbdriver'];
       }

       //For postgres
       $create_sequence = false;

       if ($db_driver == "pgsql") {
         $sql = "CREATE TABLE ".$tableName." (\n";
         $unique_key = "UNIQUE";
       } else {
         $sql = "CREATE TABLE IF NOT EXISTS ".$tableName." (\n";
         $unique_key = "UNIQUE KEY";
       }
       foreach($table As $fieldName=>$field)
       {
         if (in_array($fieldName, $this->nonkeys))
         {
            continue;
         }
         if (is_array($field)){
            if (!isset($field['type']) || empty($field['type'])) { 
               die("You must specify the data type for \'$fieldName\'\n");
            }
            $type = $field['type'];
            $null = (isset($field['null'])&&$field['null'])?"NULL":"NOT NULL";
            $special = isset($field['special'])?$field['special']:"";
            if($db_driver == 'pgsql' && $special == 'auto_increment') {
               $special = " DEFAULT nextval('".$tableName."_seq')";
               $create_sequence = true;
            }
         } else {
            if(empty($field)) {
              $type='int';
            } else {
              $type = $field;
            }
            $null = "NOT NULL";
            $special = "";
         }
         $sql .= "    ".$fieldName." ".strtoupper($type)." ".$null." ".$special.",\n";
       }//end foreach
       $sql = substr($sql, 0, strrpos($sql,','));

       if (isset($table['_PRIMARY_KEY']) && is_string($table['_PRIMARY_KEY'])) {
         $sql .= ",\n";
         $sql .= "    PRIMARY KEY (". $table['_PRIMARY_KEY'] .")";
       } else if (isset($table['_PRIMARY_KEY']) && !empty($table['_PRIMARY_KEY'])) {
         $sql .= ",\n";
         $sql .= "    PRIMARY KEY (".implode(',',$table['_PRIMARY_KEY']).")";
       } else {
         echo "Warning: No _PRIMARY_KEY specified for \'$tableName\'!. It is recommended to define a primary key for every table to get the maximum functionality from SkinnyMVC.\n\n";
       }
       if (!empty($table['_UNIQUE'])) {
           foreach (  $table['_UNIQUE'] AS $x  ) {

               if (  is_array($x)  ) {

                   $sql .= ",\n";
                   $sql .= "    $unique_key (".implode(',',$x).")";

               } else if (  is_string($x)  ) {

                   $sql .= ",\n";
                   $sql .= "    $unique_key (". $x .")";

               } else {

                   $sql .= ",\n";
                   $sql .= "    $unique_key (". (string)$x .")";
               }

           } // foreach
       }
       if (!empty($table['_UNIQUES'])) {
           foreach (  $table['_UNIQUES'] AS $x  ) {

               if (  is_array($x)  ) {

                   $sql .= ",\n";
                   $sql .= "    $unique_key (".implode(',',$x).")";

               } else if (  is_string($x)  ) {

                   $sql .= ",\n";
                   $sql .= "    $unique_key (". $x .")";

               } else {

                   $sql .= ",\n";
                   $sql .= "    $unique_key (". (string)$x .")";
               }

           } // foreach
       }

       unset ($indexes);
       if ($db_driver == "pgsql") {
         if (!empty($table['_INDEX'])) {
            $indexes = '';
            foreach($table['_INDEX'] As $key) {
               $indexes .= "CREATE INDEX ".$key."_idx ON $tableName($key);\n";
            } 
         } 
         if (!empty($table['_INDEXES'])) {
            if (!isset($indexes)) {
               $indexes = '';
            }
            foreach($table['_INDEXES'] As $key) {
               $indexes .= "CREATE INDEX ".$key."_idx ON $tableName($key);\n";
            }
         }
       } else {
         if (!empty($table['_INDEX'])) {
            $sql .= ",\n";
            $single_keys = array();
            $complex_keys = array();
            foreach($table['_INDEX'] As $key) {
                if (is_array($key)) {
                   array_push($complex_keys,"    KEY (".implode(',',$key).")");
                } else {
                   array_push($single_keys, $key);
                }
            }
            if (!empty($complex_keys)) {
               $sql .= implode (",\n", $complex_keys); 
            }
            if (!empty($single_keys)) {
               if (!empty($complex_keys)) {
                 $sql .= ",\n";
               }
               $sql .= "    KEY (".implode("),\n    KEY (", $single_keys).")";
            }
         }
         if (!empty($table['_INDEXES'])) {
            $sql .= ",\n";
            $single_keys = array();
            $complex_keys = array();
            foreach($table['_INDEXES'] As $key) {
                if (is_array($key)) {
                   array_push($complex_keys,"    KEY (".implode(',',$key).")");
                } else {
                   array_push($single_keys, $key);
                }
            }
            if (!empty($complex_keys)) {
               $sql .= implode (",\n", $complex_keys); 
            }
            if (!empty($single_keys)) {
               if (!empty($complex_keys)) {
                 $sql .= ",\n";
               }
               $sql .= "    KEY (".implode("),\n    KEY (", $single_keys).")";
            }
         }
       } // end if $db_driver == "pgsql"

       if (!empty($table['_FOREIGN_KEY'])) {
         $sql .= ",\n";
         $foreign_keys = array();
         foreach($table['_FOREIGN_KEY'] As $keyName=>$key) {
           array_push($foreign_keys, "    FOREIGN KEY (".$keyName.") REFERENCES ".$key["table"]."(".$key["field"].")");
         }
         $sql .= implode (",\n", $foreign_keys);
       }
       if (!empty($table['_FOREIGN_KEYS'])) {
         $sql .= ",\n";
         $foreign_keys = array();
         foreach($table['_FOREIGN_KEYS'] As $keyName=>$key) {
           array_push($foreign_keys, "    FOREIGN KEY (".$keyName.") REFERENCES ".$key["table"]."(".$key["field"].")");
         }
         $sql .= implode (",\n", $foreign_keys);
       }

       if ($db_driver == 'pgsql') {
         $sql .= "\n);\n\n";
       } else {
         $sql .= "\n) ENGINE=InnoDB;\n\n";
       }

       if ($create_sequence) {
         $sql = "CREATE SEQUENCE ".$tableName."_seq MINVALUE 1;\n\n".$sql;
       }

       if (isset($indexes) && !empty($indexes)) {
          $sql .= $indexes."\n\n";
       }

       $sql .= "\n";

       if (isset($table['_DATABASE_KEY']) && !empty($table['_DATABASE_KEY'])) {
          if (!in_array($table['_DATABASE_KEY'], $dbs)) {
             //Error
             echo "Invalid _DATABASE_KEY in table $tableName \n";
             exit;
          }
          $return_sql[$table['_DATABASE_KEY']] .= $sql;
       } else {
         $return_sql['__database'] .= $sql;
       }
     }

     return $return_sql;
  }//end private function createSQLFromArray
/*    *******************************************************************************    */


  /**
  * Creates Model files from an array
  */
  private function createModelfromArray($model) {
     //create a list of all db_keys
     if (isset(SkinnySettings::$CONFIG['dbs']) && !empty(SkinnySettings::$CONFIG['dbs']) && is_array(SkinnySettings::$CONFIG['dbs'])) {
        $dbs = array();
        foreach(SkinnySettings::$CONFIG['dbs'] as $db_key=>$value) {
          array_push($dbs, $db_key);
        }
     }

    foreach($model As $tableName=>$table) {
       if (isset($table['_TABLE_NAME']) && !empty($table['_TABLE_NAME'])) {
          $tableName = $table['_TABLE_NAME'];
       }
       unset($databaseKey);
       if (isset($table['_DATABASE_KEY']) && !empty($table['_DATABASE_KEY'])) {
          $databaseKey = $table['_DATABASE_KEY'];
       }

       //get the db driver (for ex. "mysql" or "pgsql")
       if (isset(SkinnySettings::$CONFIG['dbs']) && !empty(SkinnySettings::$CONFIG['dbs']) && is_array(SkinnySettings::$CONFIG['dbs'])) {
         if (isset($databaseKey)) {
           if (!in_array($databaseKey, $dbs)) {
              //Error
              echo "Invalid _DATABASE_KEY in table $tableName \n";
              exit;
           }
           $db_driver = SkinnySettings::$CONFIG['dbs'][$databaseKey]['dbdriver'];
         } else {
           $db_driver = SkinnySettings::$CONFIG['dbdriver'];
         }
       } else {
          if (isset($databaseKey)) {
             //Error
             echo "Invalid _DATABASE_KEY in table $tableName; dbs not defined \n";
             exit;
          }   
          $db_driver = SkinnySettings::$CONFIG['dbdriver'];
       }

       $tableFieldsArray = array_diff(array_keys($table), $this->nonkeys);
       $tableFields = 'array("'.implode('","', $tableFieldsArray).'")';

       $integerType = array('int', 'integer', 'smallint', 'tinyint', 'longint');


       $tableFieldsValues = 'array(';
       foreach($tableFieldsArray As $column) {
          $tableFieldsValues .= '"'.$column.'"=>';
          if ((isset($table[$column]['type']) && in_array($table[$column]['type'], $integerType)) || in_array($table[$column], $integerType)) {
             $tableFieldsValues .= "0,";
          } else {
             if (isset($table[$column]['null']) && false === $table[$column]['null']) {
                $tableFieldsValues .= '"",';
             } else {
               $tableFieldsValues .= "null,";
             }
          }
       }
       $tableFieldsValues .= ')';
       $tableNameCamelized = $this->camelize($tableName);
       if(isset($table['_PRIMARY_KEY'])) {
         if (  is_array($table['_PRIMARY_KEY'])  ) {
           $pk = 'array("'.implode('","',$table['_PRIMARY_KEY']).'")';
         } else if(  is_string($table['_PRIMARY_KEY'])  ) {
           $pk = 'array('. var_export($table['_PRIMARY_KEY'],TRUE) .')';
         } else {
           $pk = 'array('. var_export((string)$table['_PRIMARY_KEY'],TRUE) .')';
         }
       } else {
         $pk = 'null';
         echo "Warning: No _PRIMARY_KEY specified for '$tableName'!. It is recommended to define a primary key for every table to get the maximum functionality from SkinnyMVC.\n\n"; 
       }
       $class = '<?php

require_once("base/Base'.$tableNameCamelized.'.php");

class '.$tableNameCamelized.' extends Base'.$tableNameCamelized.'
{
}
       ';

       $baseClass = '<?php
/**
 * filename:    Base'.$tableNameCamelized.'.php
 * description: Represents table \''.$tableName.'\'
 *
 */

require_once("../lib/skinnymvc/core/Model.php");

abstract class Base'.$tableNameCamelized.' extends SkinnyModel {

   protected $new = true;
   protected static $fields = '.$tableFields.';
   protected static $tableName = \''.$tableName.'\';
   protected static $databaseKey = '.(isset($databaseKey)?'"'.$databaseKey.'"':"null").';
   protected static $className = \'Base'.$tableNameCamelized.'\';
   protected static $primaryKey = '.$pk.';
   protected $originalFieldValues = '.$tableFieldsValues.';
   protected $fieldValues = '.$tableFieldsValues.';
   protected $modifiedFields = array();

   public function __construct($fieldValues=null) {
      if(!empty($fieldValues)) {
         foreach($fieldValues As $field=>$value) {
            if (is_numeric($field)) continue;
            if (!in_array($field, self::$fields)) {
               throw SkinnyException(\'Invalid field name used in constructor.\');
            }
            $this->fieldValues[$field] = $value;
         }
         $this->originalFieldValues = $this->fieldValues;
      }
   }

   public function isNew() {
      return $this->new;
   }

   public function setNew($new) {
      $this->new = $new;
   }

   public function get() {
      return $this->fieldValues;
   }

    public function set($fieldValues=null) {
        if(!empty($fieldValues)) {
            foreach($fieldValues AS $field=>$value) {

                if (!in_array($field, self::$fields)) {
                    throw SkinnyException(\'Invalid field name used in set().\');
                }

                $this->fieldValues[$field] = $value;

                if (  !in_array($field, $this->modifiedFields)  ) {
                    $this->modifiedFields[] = $field;
                }
            }
        }      
    }

    public function reset()
    {
        $this->modifiedFields = array();

        $$this->fieldValues   = $this->originalFieldValues;
    }

';

    foreach ($tableFieldsArray as $column) {
        $columnCamelized = $this->camelize($column);
        $baseClass .=     '    /////////////////////////' ."\n"
                   .      '    // field: ' .$column       ."\n"
                   .      '    /////////////////////////' ."\n"
                   .      "\n"
                   ;
        $baseClass .=     "    public function get".$columnCamelized."()\n"
                   .      "    {\n"
                   .      "        return \$this->fieldValues['$column'];\n"
                   .      "    }  \n\n"
                   .      "    public function set".$columnCamelized."(\$value)\n"
                   .      "    {\n"
                   ;
        if ((isset($table[$column]['type']) && in_array($table[$column]['type'], $integerType)) || in_array($table[$column], $integerType))
        {
            $baseClass .= "        if (!is_numeric(\$value)) {throw new SkinnyException('".$column.": Value must be numeric!');}\n";
        }
        if (isset($table[$column]['null']) && false === $table[$column]['null'])
        {
            $baseClass .= "        if (is_null(\$value)) {throw new SkinnyException('".$column.": Value can not be null!');}\n";
        }

        $baseClass .= "\n";

        $baseClass .=     "        \$this->fieldValues['$column'] = \$value;\n"
                   .      '        if (  !in_array('. var_export($column,TRUE) .', $this->modifiedFields)  ) {' ."\n"
                   .      '            $this->modifiedFields[] = '. var_export($column,TRUE) .';'               ."\n"
                   .      '        }'                                                                           ."\n"
                   .      '    }'                                                                               ."\n"
                   .      "\n"
                   ;

        $baseClass .=     '    public function reset'. $columnCamelized .'()'                                                                            ."\n"
                   .      '    {'                                                                                                                        ."\n"
                   .      '        $i = array_search('. var_export($column,TRUE) .', $this->modifiedFields);'                                            ."\n"
                   .      '        if (  FALSE !== $i  ) {'                                                                                              ."\n"
                   .      '            unset($this->modifiedFields[$i]);'                                                                                ."\n"
                   .      '            $this->fieldValues['. var_export($column,TRUE) .'] = $this->originalFieldValues['. var_export($column,TRUE) .'];' ."\n"
                   .      '        }'                                                                                                                    ."\n"
                   .      '    }'                                                                                                                        ."\n"
                   .      "\n"
                   ;
        $baseClass .= "\n\n";

    } // foreach
   $baseClass .= '

   public function fetchFields() {
     return self::$fields;
   }

   public function fetchTableName() {
     return self::$tableName;
   }

   public function reload() {
     if (!isset(self::$primaryKey)) {
       throw new SkinnyException(\'This class does not have a primary key defined.\');
     }
     $_pk = self::$primaryKey;
     $pk = array();
     $condition = array();

     foreach($_pk As $field) {
        array_push($condition, $field.\'="\'.$this->fieldValues[$field].\'"\');
     }
     $condition = "WHERE ".implode(" AND ", $condition);

     $criteria = array();
     $criteria[\'conditions\'] = $condition;

     $criteria[\'limit\'] = \'LIMIT 1\';
     $criteria[\'offset\'] = \'OFFSET 0\';


     $result = self::selectArray(self::$tableName, $criteria);

     if (empty($result)) {
        //Something is wrong
        throw new SkinnyException("Could not reload object from database. Criteria:".var_export($criteria, true));
     }

     $row = $result[0];

     foreach($row As $field=>$value) {
       if (is_numeric($field)) continue;
       $this->fieldValues[$field] = $value;
     }

     $this->originalFieldValues = $this->fieldValues;

     $this->new = false;
   }

   public function __clone() {
      $this->new = true;
      $_pk = self::$primaryKey;
      //TODO: Create a new object instead
      foreach($_pk As $field) {
         $this->fieldValues[$field]         = 0;
         $this->originalFieldValues[$field] = 0;
      }
   }
  
   public function delete() {
     if ($this->new) return;
     $sql = "DELETE FROM '.$tableName.' WHERE ";

     $_pk = self::$primaryKey;
     $npk = count($_pk);
     for ($loop=0;$loop<$npk;$loop++) {
       $sql .= $_pk[$loop]."=".$this->fieldValues[$_pk[$loop]];
       if($loop<$npk-1) {
         $sql .= " AND ";
       }
     }

     $con = SkinnyDbController::getWriteConnection();
     $result = $con->exec($sql);
   }
 
    public function save() {
';
    foreach($tableFieldsArray As $column) {
        if ((isset($table[$column]['type']) && in_array($table[$column]['type'], $integerType)) || in_array($table[$column], $integerType)) {
            $baseClass .= '        if (!is_numeric($this->fieldValues["'.$column.'"])) {'."\n"
                       .  '            throw new SkinnyException(\''.$column.' must be numeric.\');'."\n"
                       .  '        }'."\n";
        } else {
            if (isset($table[$column]['null']) && false === $table[$column]['null']) {
                $baseClass .= '        if (is_null($this->fieldValues["'.$column.'"])) {'."\n"
                           .  '            throw new SkinnyException(\''.$column.' must not be null.\');'."\n"
                           .  '        }'."\n";
            }
        }
    }

   $baseClass .= '
        $numfv = count($this->fieldValues);
        $inserted = false;
        if($this->new) {
            $sql = "INSERT INTO '.$tableName.' VALUES (";
            $loop = 1;
            foreach($this->fieldValues As $field=>$value) {
               if (  !in_array($field, $this->modifiedFields)  ) {
                  $sql .= "DEFAULT";
               } else {
                  $sql .= "\'".addslashes($value)."\'";
               }
               if ($loop<$numfv) {
                  $sql .= ", ";
               }
               $loop++;
            } // foreach;
            $sql .= ")";

            $this->new = false;
            $inserted = true;
        }else{
            //UPDATE
            $sql = "UPDATE '.$tableName.' SET ";
            $loop = 1;
            foreach($this->fieldValues As $field=>$value) {
                if (  !in_array($field, $this->modifiedFields)  ) {
                    $sql .= $field."=DEFAULT";
                } else {
                  if (is_null($value)) {
                      $sql .= $field."=null";
                  } else {
                      $sql .= $field."=\'".addslashes($value)."\'";
                  }
                }
                if ($loop<$numfv) {
                    $sql .= ", ";
                }
                $loop++;
            } // foreach
            $sql .= " WHERE ";
            $_pk = self::$primaryKey;
            $npk = count($_pk);
            for ($loop=0;$loop<$npk;$loop++) {
                $sql .= $_pk[$loop]."=".$this->fieldValues[$_pk[$loop]];
                if($loop<$npk-1) {
                    $sql .= " AND ";
                }
            }
        }

        $con = SkinnyDbController::getWriteConnection();
      ';

   if ($db_driver == "pgsql") {
    // use query for inserts instead of exec for postgres
    //TODO: Make work with complex primary key! 
    $baseClass .= '
        $con->beginTransaction();
        $result = false;
        if ($inserted) {
           //Works with simple primary key only
           $stmt = $con->query($sql. " RETURNING ".self::$primaryKey[0]);

           if (!empty($stmt)) {
             $result = $stmt->fetchColumn();
             $this->fieldValues[self::$primaryKey[0]] = $result;
             $con->commit(); 
           } else {
             $con->rollBack();
           }
        } else {
           $result = $con->exec($sql);
           $con->commit();
        }
    ';

   } else {
    $baseClass .= '
        $result = $con->exec($sql);

        if ($inserted) {
            //TODO: Make work with multiple fields
            $this->fieldValues[self::$primaryKey[0]] = $con->lastInsertId();
        }
    ';
   } //end if

   // $result, if successful, contains the number of affected rows OR the insert_id (only in postgres).
   // In postgres, the insert_id should always be >0
   $baseClass .= '
        if (  $result  ) {
            $this->originalFieldValues = $this->fieldValues;
        }

        if (is_numeric($result) && $result>0) {
           $result = true;
        } else {
           $result = false;
        }

        return $result;
    }

   public static function getByPK($pk) {
     if (!isset(self::$primaryKey)) {
       throw new SkinnyException(\'This class does not have a primary key defined.\');
     }
     $_pk = self::$primaryKey;
     if (count($_pk)>1 && (!is_array($pk) ||  count($_pk) !=  count($pk))) {
       throw new SkinnyException(\'Invalid primary key.\');
     }
     if (!is_array($pk)) {
       $condition = "WHERE ".$_pk[0]."=\'".addslashes($pk)."\'";
     } else {
       $condition = array();
       foreach ($pk As $column=>$value) {
         if (!in_array($column, $_pk)) {
            throw new SkinnyException(\'Invalid primary key.\');
         }
         array_push($condition, $column."=\'".$value."\'");
       }
       $condition = "WHERE ".implode(" AND ", $condition);
     }
     return self::selectOne(array(\'conditions\'=>$condition));
   }

    //selects and returns ONE instance of the class
    public static function selectOne($criteria = array()) {
        if (  !isset($criteria["sql"])  ) {
            $criteria[\'limit\'] = \'LIMIT 1\';
            $criteria[\'offset\'] = \'OFFSET 0\';
        }
        $result = self::select($criteria);

        if(!empty($result)) {
            return $result[0];
        } else {
            return null;
        }
    }

  /**
   * Selects and creates multiple instances of the class
   * @param mixed $criteria
   * @return array of class instances
   */
    public static function select($criteria = array()) {

        if (  !isset($criteria["sql"])  ) {

            //columns can be SQL or an array.
            if (empty($criteria[\'columns\'])) {
                $criteria[\'columns\'] = \'*\';
            } else {
                if (is_array($criteria[\'columns\'])) {
                    foreach($criteria[\'columns\'] As $column) {
                        if (!in_array($column, self::$fields)) {
                            throw new SkinnyException(\'Invalid column: "\'.$column.\'".\');
                        }
                    }
                    $criteria[\'columns\'] = implode (\',\', $criteria[\'columns\']);
                }
            }

            // group can be SQL or array
            if (empty($criteria[\'group\'])) {
                $criteria[\'group\'] = \'\';
            } else {
                if(is_array($criteria[\'group\'])) {
                    foreach($criteria[\'group\'] As $column) {
                        if (!in_array($column, self::$fields)) {
                            throw new SkinnyException(\'Invalid column: "\'.$column.\'".\');
                        }
                    }
                    $criteria[\'group\'] = \'GROUP BY \'.implode(\',\',$criteria[\'group\']);
                }
            }
        }


        $result = self::selectArray(self::$tableName, $criteria);

        if (!empty($result)) {
            $objects = array();

            foreach($result as $row) {
                $obj = new '.$tableNameCamelized.'($row);
                $obj->setNew(false); 
                array_push($objects, $obj);
            }
            return $objects;
        }
        return null;
    }

}
       ';

       if (!file_exists('lib/skinnymvc/model/'.$tableNameCamelized.'.php')) {
         @file_put_contents('lib/skinnymvc/model/'.$tableNameCamelized.'.php', $class);
         echo "Created $tableNameCamelized.php\n";
       }
       @file_put_contents('lib/skinnymvc/model/base/Base'.$tableNameCamelized.'.php', $baseClass);
       echo "Created Base$tableNameCamelized.php\n";
    }
  }//end private function createSQLFromArray
/*    *******************************************************************************    */


private function installErrorPages() {

   $s = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html>
<head>
<title>SkinnyMVC: 404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL was not found.</p>
<hr>
<address><a href="http://skinnymvc.com">SkinnyMVC</address>
</body>
</html>
   ';
   
   @file_put_contents('templates/404.php', $s);

}//end private function installErrorPages
/*    *******************************************************************************    */


 /**
  * Camelizes a string
  */
  private function camelize($str)
  {
    $str = str_replace("_", " ", $str);
    $str = ucwords($str);
    $str = str_replace(" ", "", $str);
    return $str;
  }

} // end class MVC

?>
