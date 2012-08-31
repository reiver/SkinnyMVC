<?php

//
// SkinnyMVC build script.
//
// Run this to build skinnymvc.php
//


    $code  = '<?php'."\n";
    $code .= "\n";
    $code .= file_get_contents('parts/LICENSE.txt');
    $code .= "\n";
    $code .= "\n";
    $code .= "\n";
    $code .= '// This version of skinnymvc.php was built on: '.date('c')."\n";
    $code .= "\n";
    $code .= "\n";
    $code .= "\n";
    $code .= '// P R O C E D U R E S //////////////////////////////////////////////////////////////////////////////////////////////////////'."\n";
    $code .= file_get_contents('parts/_help.php');
    $code .= "\n";
    $code .= '//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//'."\n";
    $code .= "\n";
    $code .= `php build-upgrade.php`;
    $code .= "\n";
    $code .= '//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//'."\n";
    $code .= "\n";
    $code .= `php build-install.php`;
    $code .= "\n";
    $code .= '//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//'."\n";
    $code .= "\n";
    $code .= file_get_contents('parts/_uninstall.php');
    $code .= "\n";
    $code .= '//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//'."\n";
    $code .= "\n";
    $code .= file_get_contents('parts/_create_module.php');
    $code .= "\n";
    $code .= '//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//'."\n";
    $code .= "\n";
    $code .= file_get_contents('parts/_generate_sql_and_model.php');
    $code .= '////////////////////////////////////////////////////////////////////////////////////////////////////// P R O C E D U R E S //'."\n";
    $code .= "\n";
    $code .= "\n";
    $code .= "\n";
    $code .= file_get_contents('parts/MVC.php');
    $code .= "\n";
    $code .= "\n";
    $code .= "\n";
    $code .= file_get_contents('parts/main.php');

    print($code);
