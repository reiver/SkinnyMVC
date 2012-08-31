<?php

//
// SkinnyMVC build-upgrade script.
//
// Run this to build the upgrade portion of skinnymvc.php
//



// P R O C E D U R E S //////////////////////////////////////////////////////////////////////////////////////////////////////

    function generate_upgrade_procedure($dirname)
    {
        //
        // Generate code.
        //
            $code  = '        ';
            $code .= 'function _upgrade()';
            $code .= "\n";

            $code .= '        ';
            $code .= '{';
            $code .= "\n";

            $code .= sub_generate_directories($dirname, $dirname.'/');

            $code .= '        ';
            $code .= '}';
            $code .= "\n";

        //
        // Return.
        //
            return $code;
    }

//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

    function sub_generate_directories($dirname, $remove_prefix)
    {
        //
        //
        //
            $code = '';

            $dir = dir($dirname);
            if (  !isset($dir) || FALSE === $dir || !is_object($dir)  ) {
    /////////// RETURN
                return FALSE;
            }

            while (  FALSE !== ($x = $dir->read())  ) {

                if (  '.' == $x || '..' == $x || '.svn' == $x  ) {
            /////// CONTINUE
                    continue;
                }

                $path = $dirname . '/' . $x;

                $code .= sub_generate($path, $remove_prefix);

            } // while

        //
        // Return.
        //
            return $code;
    }

//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

    function sub_generate($path, $remove_prefix)
    {
        //
        // Generate code.
        //
            $code = '';

            $x = substr($path,strlen($remove_prefix));

            if (  is_dir($path)  ) {
                $code .= '                ';
                $code .= 'mkdir('. var_export($x,TRUE)  .');';
                $code .= "\n";

// ##### TODO: chmod

                $code .= sub_generate_directories($path, $remove_prefix);

            } elseif (  is_file($path)  ) {
                $code .= '                ';
                $code .= '@file_put_contents('. var_export($x,TRUE) .','. var_export(file_get_contents($path),TRUE) .');';
                $code .= "\n";
            }


        //
        // Return.
        //
            return $code;

    }
////////////////////////////////////////////////////////////////////////////////////////////////////// P R O C E D U R E S //



//
// M A I N
//

    $code = generate_upgrade_procedure('src-level-0');
    if (  FALSE === $code || !is_string($code) || '' == trim($code)  ) {
        // Error
/////// EXIT
        exit();
    }


    print($code);
