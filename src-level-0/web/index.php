<?php
/******************************
 * filename: index.php
 */

    require_once('../config/settings.php');
    require_once('../lib/skinnymvc/controller/SkinnyController.php');

    $c = new SkinnyController;
    $c->main();

   