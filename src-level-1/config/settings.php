<?php
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
    