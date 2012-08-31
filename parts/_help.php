    function _help()
    {
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

        print($help);
    }
