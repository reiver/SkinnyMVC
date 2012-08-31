<?php

//
// Description:
//
//     This unit test checks to see that running...
//
//         php skinnymvc.php generateSQL
//
//     ... does things properly.
//

//
// M A I N
//


    //
    //
    //
        $test_model = array();
        $test_model['test_table'] =
            array
            ( 'id' => array('type'=>'int', 'null'=>false, 'special'=>'auto_increment')
            , '_PRIMARY_KEY' => 'id'
            );

        $sql = get_generated_sql($test_model);

        $expected_sql = 'CREATE TABLE IF NOT EXISTS test_table (' ."\n"
                      . '    id INT NOT NULL auto_increment,'     ."\n"
                      . '    PRIMARY KEY (id)'                    ."\n"
                      . ') ENGINE=InnoDB;'                        ."\n"
                      ;

         compare_sql($expected_sql, $sql);

// #### TODO
print('#### TODO: Check that for Postgres too'."\n");

// #### TODO
print('#### TODO' ."\n");




    //
    // Exit.
    //
        exit();


// P R O C E D U R E S //////////////////////////////////////////////////////////////////////////////////////////////////////
    function get_generated_sql($model)
    {
        global $argv;

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
        // Create "config/schema.php" file.
        //
            $config_schema_filename = $dirname .'/'. 'config/schema.php';
            $config_schema_contents = '<?php'."\n".'$model = '. var_export($model,TRUE) .';' ."\n";
            file_put_contents($config_schema_filename, $config_schema_contents);

        //
        // Run SkinnyMVC's generateSQL.
        //
            `cd $dirname ; php skinnymvc.php generateSQL`;

        //
        // Make sure that the SQL file is there.
        //
            $sql_filename = $dirname . '/lib/skinnymvc/model/sql/database.sql';
            if (  !file_exists($sql_filename)  ) {
                print('FAIL! - SQL file -- '. var_export($sql_filename,TRUE) .' is NOT there!' ."\n");
                `rm -fR $dirname`;
    /////////// RETURN
                return FALSE;
            }

            if (  !is_file($sql_filename)  ) {
                print('FAIL! - Expected SQL file -- '. var_export($sql_filename,TRUE) .' is NOT a file!' ."\n");
                `rm -fR $dirname`;
    /////////// RETURN
                return FALSE;
            }

        //
        // Get the generated SQL.
        //
            $sql = file_get_contents($sql_filename);

        //
        // Remove TEMP directory.
        //
            `rm -fR $dirname`;

        //
        // Return.
        //
            return $sql;
    }
//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//
    function compare_sql($expected_sql, $sql)
    {
            if (  trim($sql) != trim($expected_sql)  ) {
                print('FAIL! - SQL expected...'."\n");
                print($expected_sql);
                print("\n".'SQL received...'."\n");
                print($sql);
                print("\n");
            } else {
                print('pass'."\n");
            }
    }
////////////////////////////////////////////////////////////////////////////////////////////////////// P R O C E D U R E S //

