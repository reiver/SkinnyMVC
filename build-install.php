<?php

//
// SkinnyMVC build-install script.
//
// Run this to build the install portion of skinnymvc.php
//



// P R O C E D U R E S //////////////////////////////////////////////////////////////////////////////////////////////////////

    function generate_install_procedure($other_dirname, $dirname)
    {
        //
        // Generate code.
        //
            $code  = '        ';
            $code .= 'function _install()';
            $code .= "\n";

            $code .= '        ';
            $code .= '{';
            $code .= "\n";

            $code .= sub_generate_directories($other_dirname, $dirname, $dirname.'/');

            $code .= '        ';
            $code .= '}';
            $code .= "\n";

        //
        // Return.
        //
            return $code;
    }

//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

    function sub_generate_directories($other_dirname, $dirname, $remove_prefix)
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
                $other_path = $other_dirname .'/'. $x;

                $code .= sub_generate($other_path, $path, $remove_prefix);

            } // while

        //
        // Return.
        //
            return $code;
    }

//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

    function sub_generate($other_path, $path, $remove_prefix)
    {
        //
        // Generate code.
        //
            $code = '';

            $x = substr($path,strlen($remove_prefix));

            if (  is_dir($path)  ) {

                if (  !file_exists($other_path)  ) {
                    $code .= '                ';
                    $code .= 'if (  !file_exists('. var_export($x,TRUE) .')  ) {';
                    $code .= "\n";

                    $code .= '                ';
                    $code .= '    mkdir('. var_export($x,TRUE)  .');';
                    $code .= "\n";

                    $code .= '                ';
                    $code .= '}';
                    $code .= "\n";
                }

// ##### TODO: chmod?

                $code .= sub_generate_directories($other_path, $path, $remove_prefix);

            } elseif (  is_file($path)  ) {

                if (  !file_exists($other_path)  ) {
                    $code .= '                ';
                    $code .= 'if (  !file_exists('. var_export($x,TRUE) .')  ) {';
                    $code .= "\n";

                    $code .= '                ';
                    $code .= '    @file_put_contents('. var_export($x,TRUE) .','. var_export(file_get_contents($path),TRUE) .');';
                    $code .= "\n";

                    $code .= '                ';
                    $code .= '}';
                    $code .= "\n";
                }
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

    $code = generate_install_procedure('src-level-0', 'src-level-1');
    if (  FALSE === $code || !is_string($code) || '' == trim($code)  ) {
        // Error
/////// EXIT
        exit();
    }


    print($code);
