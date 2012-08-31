<?php
/******************************
 * filename: dev.php
 */

    require_once('../config/settings.php');
    require_once('../lib/skinnymvc/controller/SkinnyController.php');
    require_once('../lib/skinnymvc/core/SkinnyException.php');

    SkinnySettings::$CONFIG['debug'] = true;

    $__DEBUG = array('sql'=>array(), 'data'=>array());

    try {
      $c = new SkinnyController;
      $c->main();
    } catch (SkinnyException $e) {
      echo "<h4>Exception</h4>";
      echo "<div>$e->getMessage()</div>";
      echo "<div>$e->getTrace()</div>";
    }

    echo "<div><pre>".var_export($__DEBUG, true)."</pre></div>";
    