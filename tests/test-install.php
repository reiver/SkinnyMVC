<?php

//
// Description:
//
//     This unit test checks to see that running...
//
//         php skinnymvc.php install
//
//     ... adds the expected files and directories.
//

//
// M A I N
//

    //
    // Create TEMP directory to do test in.
    //
        $dirname = 'TEMP-'.$argv[0];
        `mkdir $dirname`;

    //
    // Create a new copy of skinnymvc.php
    //
        `cd .. ; php build.php > tests/$dirname/skinnymvc.php`;

    //
    // Run SkinnyMVC's install.
    //
        `cd $dirname ; php skinnymvc.php install`;

    //
    // Check that upgradable expected directories exist!  (And are directories.)
    //
        $expected_upgradable_dirs =
            array
            ( 'config'
            , 'lib'
            , 'lib/skinnymvc'
            , 'lib/skinnymvc/class'
            , 'lib/skinnymvc/controller'
            , 'lib/skinnymvc/controller/base'
            , 'lib/skinnymvc/core'
            , 'lib/skinnymvc/core/base'
            , 'lib/skinnymvc/dbcontroller'
            , 'lib/skinnymvc/dbcontroller/base'
            , 'lib/skinnymvc/model'
            , 'lib/skinnymvc/model/base'
            , 'lib/skinnymvc/model/sql'
            , 'modules'
            , 'plugins'
            , 'templates'
            , 'tmp'
            , 'web'
            , 'web/css'
            , 'web/images'
            , 'web/js'
            );

        check_expected_dirs($dirname, $expected_upgradable_dirs);

    //
    // Check all upgradable files exist.  (And are files.)
    //
        $expected_upgradable_files =
            array
            ( 'README'
            , 'lib/skinnymvc/controller/base/SkinnyBaseController.php'
            , 'lib/skinnymvc/core/BaseModel.php'
            , 'lib/skinnymvc/core/SkinnyException.php'
            , 'lib/skinnymvc/core/SkinnyUser.php'
            , 'lib/skinnymvc/core/base/SkinnyBaseActions.php'
            , 'lib/skinnymvc/dbcontroller/base/SkinnyBaseDbController.php'
            , 'plugins/README'
            , 'templates/README'
            , 'web/.htaccess'
            , 'web/dev.php'
            , 'web/index.php'
            );

        check_expected_files($dirname, $expected_upgradable_files);


    //
    // Check that the installable (but NOT upgradable) expected directories exist!  (And are directories.)
    //
        $expected_dirs =
            array
            ( 'modules/default'
            , 'modules/default/actions'
            , 'modules/default/templates'
            );

        check_expected_dirs($dirname, $expected_dirs);

    //
    // Check that the installable (but NOT upgradable) expected files exist!  (And are files.)
    //
        $expected_files =
            array
            ( 'config/schema.php'
            , 'config/settings.php'
            , 'lib/skinnymvc/controller/SkinnyController.php'
            , 'lib/skinnymvc/core/Model.php'
            , 'lib/skinnymvc/core/SkinnyActions.php'
            , 'lib/skinnymvc/dbcontroller/SkinnyDbController.php'
            , 'modules/default/actions/actions.php'
            , 'modules/default/templates/index.php'
            , 'modules/default/templates/README'
            , 'templates/404.php'
            , 'templates/500.php'
            , 'templates/layout.php'
            , 'web/css/main.css'
            );


        check_expected_files($dirname, $expected_files);

    //
    // Double check that the stuff in "../src-level-0" is there.
    //
// #### TODO
        print('#### TODO: Double check that the stuff in "../src-level-0" is there.' ."\n");

    //
    // Double check that the stuff in "../src-level-1" is there.
    //
// #### TODO
        print('#### TODO: Double check that the stuff in "../src-level-1" is there.' ."\n");

    //
    // Remove TEMP directory.
    //
        `rm -fR $dirname`;


    //
    // Exit.
    //
        exit();


// P R O C E D U R E S //////////////////////////////////////////////////////////////////////////////////////////////////////
    function check_expected_files($dirname, $expected_files)
    {
        //
        // Parameter.
        //
            if (  !isset($expected_files) || !is_array($expected_files)  ) {
                // Error.
                print('ERROR: check_expected_files() procedure got a bad parameter.  Namely: $check_expected_files = '.var_export($check_expected_files,TRUE) ."\n");
                return FALSE;
            }


        //
        // Check expected files.
        //
            foreach (  $expected_files AS $x  ) {
                $f = $dirname .'/'. $x;

                if (  !file_exists($f)  ) {
                    print('FAIL! - File '. var_export($x,TRUE) .' does NOT exist'."\n");
            /////// CONTINUE
                    continue;
                }
                if (  !is_file($f)  ) {
                    print('FAIL! - What was expected to be file '. var_export($x,TRUE) .' is NOT a file!'."\n");
            /////// CONTINUE
                    continue;
                }
                print('pass'."\n");
            }

    }

//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

    function check_expected_dirs($dirname, $expected_dirs)
    {
        foreach (  $expected_dirs AS $x  ) {
            $d = $dirname .'/'. $x;

            if (  !file_exists($d)  ) {
                print('FAIL! - Directory '. var_export($x,TRUE) .' does NOT exist'."\n");
        /////// CONTINUE
                continue;
            }
            if (  !is_dir($d)  ) {
                print('FAIL! - What was expected to be directory '. var_export($x,TRUE) .' is NOT a directory!'."\n");
        /////// CONTINUE
                continue;
            }
            print('pass'."\n");
        }

    }
////////////////////////////////////////////////////////////////////////////////////////////////////// P R O C E D U R E S //
