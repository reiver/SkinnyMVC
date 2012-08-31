<?php

//
// Description:
//
//     This unit test checks to see that running...
//
//         php skinnymvc.php generateModel
//
//     ... does things properly.
//

// R E Q U I R E S //////////////////////////////////////////////////////////////////////////////////////////////////////////
    require_once( dirname(__FILE__).'/simpletest/autorun.php' );
    require_once( dirname(__FILE__).'/SkinnyMvcTest.php' );
////////////////////////////////////////////////////////////////////////////////////////////////////////// R E Q U I R E S //


//
// #### TODO
//
//     * Need to add unit test for the new cache methods!
//     * Need to add code for unit test for fetchers (of UNIQUE) keys.
//


// C L A S S E S ////////////////////////////////////////////////////////////////////////////////////////////////////////////
class TestGenerateModel extends UnitTestCase
{
    // P R O C E D U R E S //////////////////////////////////////////////////////////////////////////////////////////////////
        static function calculateExpectedModelData($model)
        {
            $expected_model_data = array();

            //
            //
            //
                foreach (  $model AS $table_name => $table_data  ) {

                    $expected_model_data[$table_name]                 = array();
                    $expected_model_data[$table_name]['fields']       = array();
                    $expected_model_data[$table_name]['foreign-keys'] = array();

                    $expected_model_data[$table_name]['base']         = array();

                    $table_fields       = array();
                    $table_foreign_keys = array();

                    foreach (  $table_data AS $k => $v  ) {

                        if (  in_array($k, array('_PRIMARY_KEY', '_UNIQUES', '_INDEXES', '_FULL_TEXT'))  ) {
                    /////// CONTINUE
                            continue;
                        }

                        if (  '_FOREIGN_KEYS' == $k  ) {
                            foreach (  $v AS $foreign_key_field_name => $foreign_key_data  ) {
                                $table_foreign_keys[] = $foreign_key_field_name;
                            } // foreach
                    /////// CONTINUE
                            continue;
                        }

                        $table_fields[] = $k;

                    } // foreach

                    $expected_model_data[$table_name]['fields']       = $table_fields;
                    $expected_model_data[$table_name]['foreign-keys'] = $table_foreign_keys;

                } // foreach

            //
            //
            //
                foreach (  $expected_model_data AS $table_name => $table_data  ) {

                    $expected_model_data[$table_name]['base']['getters']    = array();
                    $expected_model_data[$table_name]['base']['setters']    = array();
                    $expected_model_data[$table_name]['base']['validators'] = array();

                    $expected_model_data[$table_name]['base']['getters'][]    = 'get';
                    $expected_model_data[$table_name]['base']['setters'][]    = 'set';
                    $expected_model_data[$table_name]['base']['validators'][] = 'isValid';


                    foreach (  $table_data['fields'] AS $field  ) {

                        $camel_case_field = $field;
                        $camel_case_field = str_replace('_',' ', $camel_case_field);
                        $camel_case_field = ucwords($camel_case_field);
                        $camel_case_field = str_replace(' ', '', $camel_case_field);

                        $expected_model_data[$table_name]['base']['getters'][]    = 'get'     . $camel_case_field;
                        $expected_model_data[$table_name]['base']['setters'][]    = 'set'     . $camel_case_field;
                        $expected_model_data[$table_name]['base']['validators'][] = 'isValid' . $camel_case_field;

                    } // foreach



                    $expected_model_data[$table_name]['base']['retrievers'] = array();

                    foreach (  $table_data['foreign-keys'] AS $field  ) {

                        $camel_case_field = $field;
                        $camel_case_field = str_replace('_',' ', $camel_case_field);
                        $camel_case_field = ucwords($camel_case_field);
                        $camel_case_field = str_replace(' ', '', $camel_case_field);

                        $retriever_name = 'retrieve' . $camel_case_field;
                        if (  'Id' == substr($retriever_name, -2) ) {
                            $retriever_name = substr($retriever_name, 0, -2);
                        }

                        $expected_model_data[$table_name]['base']['retrievers'][] = $retriever_name;

                    } // foreach

                } // foreach

            //
            // Return.
            //
                return $expected_model_data;

        }

    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

        static function getGeneratedModelData($skinnyMvcTest, $model)
        {
            //
            //
            //
                $dirname = $skinnyMvcTest->getPath();

            //
            // Make sure that the ORM files are there.
            //
                $generated_model_data = array();

                foreach ($model AS $table_name => $table_data) {

                    $generated_model_data[$table_name] = array();

                    //
                    // Calculate CamelCase version of table name.
                    //
                        $camel_case_table_name = $table_name;
                        $camel_case_table_name = str_replace('_',' ', $camel_case_table_name);
                        $camel_case_table_name = ucwords($camel_case_table_name);
                        $camel_case_table_name = str_replace(' ', '', $camel_case_table_name);

                    //
                    // Calculate path to ORM and Base-ORM files.
                    //
                        $orm_filename      = $dirname . '/lib/skinnymvc/model/'          . $camel_case_table_name . '.php';
                        $base_orm_filename = $dirname . '/lib/skinnymvc/model/base/Base' . $camel_case_table_name . '.php';

                    //
                    // Make sure that ORM and Base-ORM files are there.
                    //
                        if (  !file_exists($orm_filename)  ) {
                            print('FAIL! - ORM file -- '. var_export($orm_filename,TRUE) .' is NOT there!' ."\n");
        /////////////////// RETURN
                            return FALSE;
                        }

                        if (  !file_exists($base_orm_filename)  ) {
                            print('FAIL! - Base ORM file -- '. var_export($base_orm_filename,TRUE) .' is NOT there!' ."\n");
        /////////////////// RETURN
                            return FALSE;
                        }

                    //
                    // Get data about the generated ORM or Base-ORM files.
                    //
                        $generated_model_data[$table_name]['base']               = array();
                        $generated_model_data[$table_name]['base']['src']        = file_get_contents($base_orm_filename);

                        $generated_model_data[$table_name]['base']['getters']    = self::extractGettersFromSrc    ($generated_model_data[$table_name]['base']['src']);
                        $generated_model_data[$table_name]['base']['setters']    = self::extractSettersFromSrc    ($generated_model_data[$table_name]['base']['src']);
                        $generated_model_data[$table_name]['base']['validators'] = self::extractValidatorsFromSrc ($generated_model_data[$table_name]['base']['src']);
                        $generated_model_data[$table_name]['base']['retrievers'] = self::extractRetrieversFromSrc ($generated_model_data[$table_name]['base']['src']);

                        unset($generated_model_data[$table_name]['base']['src']);

                } // foreach

            //
            // Return.
            //
                return $generated_model_data;
        }

    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

        static function extractGettersFromSrc($src)
        {
            $getters = array();

            //
            //
            //
                $lines = explode("\n", $src);

                foreach (  $lines AS $line  ) {

                    if (  FALSE === strpos($line, 'public function get')  ) {
                /////// CONTINUE
                        continue;
                    }

                    $begin_pos = strpos($line, 'public function ') + strlen('public function ');
                    $end_pos   = strpos($line, '(');

                    $getters[] = substr($line, $begin_pos, $end_pos - $begin_pos);

                } // foreach

            //
            // Return.
            //
               return $getters;
        }
    //---------------------------------------------------------------------------------------------------------------------//
        static function extractSettersFromSrc($src)
        {
            $setters = array();

            //
            //
            //
                $lines = explode("\n", $src);

                foreach (  $lines AS $line  ) {

                    if (  FALSE === strpos($line, 'public function set')  ) {
                /////// CONTINUE
                        continue;
                    }

                    $begin_pos = strpos($line, 'public function ') + strlen('public function ');
                    $end_pos   = strpos($line, '(');

                    $setters[] = substr($line, $begin_pos, $end_pos - $begin_pos);

                } // foreach

            //
            // Return.
            //
                return $setters;
        }
    //---------------------------------------------------------------------------------------------------------------------//
        static function extractValidatorsFromSrc($src)
        {
            $validators = array();

            //
            //
            //
                $lines = explode("\n", $src);

                foreach (  $lines AS $line  ) {

                    if (  FALSE === strpos($line, 'public function isValid')  ) {
                /////// CONTINUE
                        continue;
                    }

                    $begin_pos = strpos($line, 'public function ') + strlen('public function ');
                    $end_pos   = strpos($line, '(');

                    $validators[] = substr($line, $begin_pos, $end_pos - $begin_pos);

                } // foreach

            //
            // Return.
            //
                return $validators;
        }
    //---------------------------------------------------------------------------------------------------------------------//
        static function extractRetrieversFromSrc($src)
        {
            $retrievers = array();

            //
            //
            //
                $lines = explode("\n", $src);

                foreach (  $lines AS $line  ) {

                    if (  FALSE === strpos($line, 'public function retrieve')  ) {
                /////// CONTINUE
                        continue;
                    }

                    $begin_pos = strpos($line, 'public function ') + strlen('public function ');
                    $end_pos   = strpos($line, '(');

                    $retrievers[] = substr($line, $begin_pos, $end_pos - $begin_pos);

                } // foreach

            //
            // Return.
            //
                return $retrievers;
        }


    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

        static function theTestModel()
        {
            //
            // Construct the test model.
            //
                $test_model = array();
                $test_model['test_table_one'] =
                    array
                    ( 'id' => array('type'=>'BIGINT', 'null'=>false, 'special'=>'auto_increment')
                    , '_PRIMARY_KEY' => 'id'
                    );
                $test_model['test_table_two'] =
                    array
                    ( 'id' => array('type'=>'BIGINT', 'null'=>false, 'special'=>'auto_increment')

                    , 'when_created'  => array('type'=>'DATETIME', 'null', false  )
                    , 'when_updated'  => array('type'=>'DATETIME', 'null', false  )
                    , 'when_deleted'  => array('type'=>'DATETIME', 'null', true   )

                    , 'test_table_id' => array('type'=>'BIGINT', 'null'=>false,  )


                    , '_PRIMARY_KEY' => 'id'
                    );
                $test_model['test_table_three'] =
                    array
                    ( 'id' => array('type'=>'BIGINT', 'null'=>false, 'special'=>'auto_increment')

                    , 'when_created'  => array('type'=>'DATETIME', 'null', false  )
                    , 'when_updated'  => array('type'=>'DATETIME', 'null', false  )
                    , 'when_deleted'  => array('type'=>'DATETIME', 'null', true   )

                    , 'test_table_id' => array('type'=>'BIGINT', 'null'=>false,  )


                    , '_PRIMARY_KEY' => 'id'

                    , '_FOREIGN_KEYS' => array( 'test_table_id' => array('table'=>'test_table_id' , 'field'=>'id')
                                      )
                    );

            //
            // Return.
            //
                return $test_model;
        }
    ////////////////////////////////////////////////////////////////////////////////////////////////// P R O C E D U R E S //



    // F I E L D S //////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////// F I E L D S //



    // C O N S T R U C T O R S //////////////////////////////////////////////////////////////////////////////////////////////
        function __construct()
        {
        }
    ////////////////////////////////////////////////////////////////////////////////////////////// C O N S T R U C T O R S //



    // D E S T R U C T O R S ////////////////////////////////////////////////////////////////////////////////////////////////
        function __destruct()
        {
        }
    //////////////////////////////////////////////////////////////////////////////////////////////// D E S T R U C T O R S //



    // M E T H O D S ////////////////////////////////////////////////////////////////////////////////////////////////////////
        public function testGettersAreCreated()
        {
            //
            // Construct test model (in a PHP array).
            //
                $model = self::theTestModel();

            //
            // Calculate expected model data (in a PHP array).
            //
                $expected_model_data = self::calculateExpectedModelData($model);

                $this->assertTrue(  isset($expected_model_data)     );
                $this->assertTrue(  is_array($expected_model_data)  );

            //
            // Start SkinnyMVC test environment.
            //
                $skinnyMvcTest = new SkinnyMvcTest();

                $skinnyMvcTest->begin();

            //
            // php skinnymvc.php install
            //
                $skinnyMvcTest->skinnyMvcInstall();

            //
            // Set the "config/schema.php" file.
            //
                $config_schema_filename = $skinnyMvcTest->getPath() .'/'. 'config/schema.php';
                $config_schema_contents = '<?php'."\n".'$model = '. var_export($model,TRUE) .';' ."\n";
                file_put_contents($config_schema_filename, $config_schema_contents);

            //
            // php skinnymvc.php generateModel
            //
                $skinnyMvcTest->skinnyMvcGenerateModel();

            //
            // Get generated model data.
            //
                $generated_model_data = self::getGeneratedModelData($skinnyMvcTest, $model);

//print('========================= GENERATED MODEL DATA' ."\n");
//var_export($generated_model_data);print("\n\n\n");
//print('========================= EXPECTED MODEL DATA' ."\n");
//var_export($expected_model_data);print("\n\n\n");

             //
             // Make sure we have the same tables in generated table data and expected table data.
             //
                 $diff1 = array_diff_key($generated_model_data, $expected_model_data);
                 $this->assertTrue(  isset($diff1)     , 'Failure in testGettersAreCreated().  Failed with isset($diff1)'   .' $diff1='.var_export($diff1,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));
                 $this->assertTrue(  is_array($diff1)  , 'Failure in testGettersAreCreated().  Failed with is_array($diff1)'.' $diff1='.var_export($diff1,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));
                 $this->assertTrue(  empty($diff1)     , 'Failure in testGettersAreCreated().  Failed with empty($diff1)'   .' $diff1='.var_export($diff1,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));

                 $diff2 = array_diff_key($expected_model_data, $generated_model_data);
                 $this->assertTrue(  isset($diff2)     , 'Failure in testGettersAreCreated().  Failed with isset($diff2)'   .' $diff2='.var_export($diff2,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));
                 $this->assertTrue(  is_array($diff2)  , 'Failure in testGettersAreCreated().  Failed with is_array($diff2)'.' $diff2='.var_export($diff2,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));
                 $this->assertTrue(  empty($diff2)     , 'Failure in testGettersAreCreated().  Failed with empty($diff2)'   .' $diff2='.var_export($diff2,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));

             //
             // Make sure we have them!
             //
                 foreach (  array_keys($expected_model_data) AS $table_name  ) {

                     $key_of_interest = 'getters';

                     $this->assertTrue(     isset($expected_model_data[$table_name])                            , 'Failure of:    isset($expected_model_data['.var_export($table_name,TRUE).'])');
                     $this->assertTrue(  is_array($expected_model_data[$table_name])                            , 'Failure of: is_array($expected_model_data['.var_export($table_name,TRUE).'])');
                     $this->assertTrue(     isset($expected_model_data[$table_name]['base'])                    , 'Failure of:    isset($expected_model_data['.var_export($table_name,TRUE).'][\'base\'])');
                     $this->assertTrue(  is_array($expected_model_data[$table_name]['base'])                    , 'Failure of: is_array($expected_model_data['.var_export($table_name,TRUE).'][\'base\'])');
                     $this->assertTrue(     isset($expected_model_data[$table_name]['base'][$key_of_interest])  , 'Failure of:    isset($expected_model_data['.var_export($table_name,TRUE).'][\'base\']['.var_export($key_of_interest,TRUE).'])');
                     $this->assertTrue(  is_array($expected_model_data[$table_name]['base'][$key_of_interest])  , 'Failure of: is_array($expected_model_data['.var_export($table_name,TRUE).'][\'base\']['.var_export($key_of_interest,TRUE).'])');

                     $this->assertTrue(     isset($generated_model_data[$table_name])                            , 'Failure of:    isset($generated_model_data['.var_export($table_name,TRUE).'])');
                     $this->assertTrue(  is_array($generated_model_data[$table_name])                            , 'Failure of: is_array($generated_model_data['.var_export($table_name,TRUE).'])');
                     $this->assertTrue(     isset($generated_model_data[$table_name]['base'])                    , 'Failure of:    isset($generated_model_data['.var_export($table_name,TRUE).'][\'base\'])');
                     $this->assertTrue(  is_array($generated_model_data[$table_name]['base'])                    , 'Failure of: is_array($generated_model_data['.var_export($table_name,TRUE).'][\'base\'])');
                     $this->assertTrue(     isset($generated_model_data[$table_name]['base'][$key_of_interest])  , 'Failure of:    isset($generated_model_data['.var_export($table_name,TRUE).'][\'base\']['.var_export($key_of_interest,TRUE).'])');
                     $this->assertTrue(  is_array($generated_model_data[$table_name]['base'][$key_of_interest])  , 'Failure of: is_array($generated_model_data['.var_export($table_name,TRUE).'][\'base\']['.var_export($key_of_interest,TRUE).'])');

                     $expected  = $expected_model_data  [$table_name]['base'][$key_of_interest];
                     $generated = $generated_model_data [$table_name]['base'][$key_of_interest];


//print("\n\n\n".'=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-'."\n");
//print('$expected=');var_export($expected);
//print('$generated=');var_export($generated);
//print("\n");

                     $left_diff  = array_diff($expected, $generated);
                     $right_diff = array_diff($generated, $expected);

                     $this->assertTrue(  isset($left_diff)      , 'FAILURE of isset($left_diff) for '.  $key_of_interest. ' on table '.var_export($table_name,TRUE).'.');
                     $this->assertTrue(  isset($right_diff)     , 'FAILURE of isset($right_diff) for '. $key_of_interest. ' on table '.var_export($table_name,TRUE).'.');

                     $this->assertTrue(  is_array($left_diff)   , 'FAILURE of is_array($left_diff) for '.  $key_of_interest .' on table '.var_export($table_name,TRUE).'.');
                     $this->assertTrue(  is_array($right_diff)  , 'FAILURE of is_array($right_diff) for '. $key_of_interest .' on table '.var_export($table_name,TRUE).'.');

                     $this->assertTrue(  empty($left_diff)      , 'FAILURE of empty($left_diff) for '.  $key_of_interest .' on table '.var_export($table_name,TRUE).'.');
                     $this->assertTrue(  empty($right_diff)     , 'FAILURE of empty($right_diff) for '. $key_of_interest .' on table '.var_export($table_name,TRUE).'.');

                 } // foreach

            //
            // End SkinnyMVC test environment.
            //
                $skinnyMvcTest->end();
        }
    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//
        public function testSettersAreCreated()
        {
            //
            // Construct test model (in a PHP array).
            //
                $model = self::theTestModel();

            //
            // Calculate expected model data (in a PHP array).
            //
                $expected_model_data = self::calculateExpectedModelData($model);

                $this->assertTrue(  isset($expected_model_data)     );
                $this->assertTrue(  is_array($expected_model_data)  );

            //
            // Start SkinnyMVC test environment.
            //
                $skinnyMvcTest = new SkinnyMvcTest();

                $skinnyMvcTest->begin();

            //
            // php skinnymvc.php install
            //
                $skinnyMvcTest->skinnyMvcInstall();

            //
            // Set the "config/schema.php" file.
            //
                $config_schema_filename = $skinnyMvcTest->getPath() .'/'. 'config/schema.php';
                $config_schema_contents = '<?php'."\n".'$model = '. var_export($model,TRUE) .';' ."\n";
                file_put_contents($config_schema_filename, $config_schema_contents);

            //
            // php skinnymvc.php generateModel
            //
                $skinnyMvcTest->skinnyMvcGenerateModel();

            //
            // Get generated model data.
            //
                $generated_model_data = self::getGeneratedModelData($skinnyMvcTest, $model);

//print('========================= GENERATED MODEL DATA' ."\n");
//var_export($generated_model_data);print("\n\n\n");
//print('========================= EXPECTED MODEL DATA' ."\n");
//var_export($expected_model_data);print("\n\n\n");

             //
             // Make sure we have the same tables in generated table data and expected table data.
             //
                 $diff1 = array_diff_key($generated_model_data, $expected_model_data);
                 $this->assertTrue(  isset($diff1)     , 'Failure in testSettersAreCreated().  Failed with isset($diff1)'   .' $diff1='.var_export($diff1,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));
                 $this->assertTrue(  is_array($diff1)  , 'Failure in testSettersAreCreated().  Failed with is_array($diff1)'.' $diff1='.var_export($diff1,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));
                 $this->assertTrue(  empty($diff1)     , 'Failure in testSettersAreCreated().  Failed with empty($diff1)'   .' $diff1='.var_export($diff1,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));

                 $diff2 = array_diff_key($expected_model_data, $generated_model_data);
                 $this->assertTrue(  isset($diff2)     , 'Failure in testSettersAreCreated().  Failed with isset($diff2)'   .' $diff2='.var_export($diff2,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));
                 $this->assertTrue(  is_array($diff2)  , 'Failure in testSettersAreCreated().  Failed with is_array($diff2)'.' $diff2='.var_export($diff2,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));
                 $this->assertTrue(  empty($diff2)     , 'Failure in testSettersAreCreated().  Failed with empty($diff2)'   .' $diff2='.var_export($diff2,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));

             //
             // Make sure we have them!
             //
                 foreach (  array_keys($expected_model_data) AS $table_name  ) {

                     $key_of_interest = 'setters';

                     $this->assertTrue(     isset($expected_model_data[$table_name])                            , 'Failure of:    isset($expected_model_data['.var_export($table_name,TRUE).'])');
                     $this->assertTrue(  is_array($expected_model_data[$table_name])                            , 'Failure of: is_array($expected_model_data['.var_export($table_name,TRUE).'])');
                     $this->assertTrue(     isset($expected_model_data[$table_name]['base'])                    , 'Failure of:    isset($expected_model_data['.var_export($table_name,TRUE).'][\'base\'])');
                     $this->assertTrue(  is_array($expected_model_data[$table_name]['base'])                    , 'Failure of: is_array($expected_model_data['.var_export($table_name,TRUE).'][\'base\'])');
                     $this->assertTrue(     isset($expected_model_data[$table_name]['base'][$key_of_interest])  , 'Failure of:    isset($expected_model_data['.var_export($table_name,TRUE).'][\'base\']['.var_export($key_of_interest,TRUE).'])');
                     $this->assertTrue(  is_array($expected_model_data[$table_name]['base'][$key_of_interest])  , 'Failure of: is_array($expected_model_data['.var_export($table_name,TRUE).'][\'base\']['.var_export($key_of_interest,TRUE).'])');

                     $this->assertTrue(     isset($generated_model_data[$table_name])                            , 'Failure of:    isset($generated_model_data['.var_export($table_name,TRUE).'])');
                     $this->assertTrue(  is_array($generated_model_data[$table_name])                            , 'Failure of: is_array($generated_model_data['.var_export($table_name,TRUE).'])');
                     $this->assertTrue(     isset($generated_model_data[$table_name]['base'])                    , 'Failure of:    isset($generated_model_data['.var_export($table_name,TRUE).'][\'base\'])');
                     $this->assertTrue(  is_array($generated_model_data[$table_name]['base'])                    , 'Failure of: is_array($generated_model_data['.var_export($table_name,TRUE).'][\'base\'])');
                     $this->assertTrue(     isset($generated_model_data[$table_name]['base'][$key_of_interest])  , 'Failure of:    isset($generated_model_data['.var_export($table_name,TRUE).'][\'base\']['.var_export($key_of_interest,TRUE).'])');
                     $this->assertTrue(  is_array($generated_model_data[$table_name]['base'][$key_of_interest])  , 'Failure of: is_array($generated_model_data['.var_export($table_name,TRUE).'][\'base\']['.var_export($key_of_interest,TRUE).'])');

                     $expected  = $expected_model_data  [$table_name]['base'][$key_of_interest];
                     $generated = $generated_model_data [$table_name]['base'][$key_of_interest];


//print("\n\n\n".'=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-'."\n");
//print($table_name);var_export($table_name);
//print("\n");
//print('$expected=');var_export($expected);
//print('$generated=');var_export($generated);
//print("\n");

                     $left_diff  = array_diff($expected, $generated);
                     $right_diff = array_diff($generated, $expected);

                     $this->assertTrue(  isset($left_diff)      , 'FAILURE of isset($left_diff) for '.  $key_of_interest. ' on table '.var_export($table_name,TRUE).'.');
                     $this->assertTrue(  isset($right_diff)     , 'FAILURE of isset($right_diff) for '. $key_of_interest. ' on table '.var_export($table_name,TRUE).'.');

                     $this->assertTrue(  is_array($left_diff)   , 'FAILURE of is_array($left_diff) for '.  $key_of_interest .' on table '.var_export($table_name,TRUE).'.');
                     $this->assertTrue(  is_array($right_diff)  , 'FAILURE of is_array($right_diff) for '. $key_of_interest .' on table '.var_export($table_name,TRUE).'.');

                     $this->assertTrue(  empty($left_diff)      , 'FAILURE of empty($left_diff) for '.  $key_of_interest .' on table '.var_export($table_name,TRUE).'.');
                     $this->assertTrue(  empty($right_diff)     , 'FAILURE of empty($right_diff) for '. $key_of_interest .' on table '.var_export($table_name,TRUE).'.');

                 } // foreach

            //
            // End SkinnyMVC test environment.
            //
                $skinnyMvcTest->end();
        }
    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//
        public function testValidatorsAreCreated()
        {
            //
            // Construct test model (in a PHP array).
            //
                $model = self::theTestModel();

            //
            // Calculate expected model data (in a PHP array).
            //
                $expected_model_data = self::calculateExpectedModelData($model);

                $this->assertTrue(  isset($expected_model_data)     );
                $this->assertTrue(  is_array($expected_model_data)  );

            //
            // Start SkinnyMVC test environment.
            //
                $skinnyMvcTest = new SkinnyMvcTest();

                $skinnyMvcTest->begin();

            //
            // php skinnymvc.php install
            //
                $skinnyMvcTest->skinnyMvcInstall();

            //
            // Set the "config/schema.php" file.
            //
                $config_schema_filename = $skinnyMvcTest->getPath() .'/'. 'config/schema.php';
                $config_schema_contents = '<?php'."\n".'$model = '. var_export($model,TRUE) .';' ."\n";
                file_put_contents($config_schema_filename, $config_schema_contents);

            //
            // php skinnymvc.php generateModel
            //
                $skinnyMvcTest->skinnyMvcGenerateModel();

            //
            // Get generated model data.
            //
                $generated_model_data = self::getGeneratedModelData($skinnyMvcTest, $model);

//print('========================= GENERATED MODEL DATA' ."\n");
//print($table_name);var_export($table_name);
//print("\n");
//var_export($generated_model_data);print("\n\n\n");
//print('========================= EXPECTED MODEL DATA' ."\n");
//var_export($expected_model_data);print("\n\n\n");

             //
             // Make sure we have the same tables in generated table data and expected table data.
             //
                 $diff1 = array_diff_key($generated_model_data, $expected_model_data);
                 $this->assertTrue(  isset($diff1)     , 'Failure in testValidatorsAreCreated().  Failed with isset($diff1)'   .' $diff1='.var_export($diff1,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));
                 $this->assertTrue(  is_array($diff1)  , 'Failure in testValidatorsAreCreated().  Failed with is_array($diff1)'.' $diff1='.var_export($diff1,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));
                 $this->assertTrue(  empty($diff1)     , 'Failure in testValidatorsAreCreated().  Failed with empty($diff1)'   .' $diff1='.var_export($diff1,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));

                 $diff2 = array_diff_key($expected_model_data, $generated_model_data);
                 $this->assertTrue(  isset($diff2)     , 'Failure in testValidatorsAreCreated().  Failed with isset($diff2)'   .' $diff2='.var_export($diff2,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));
                 $this->assertTrue(  is_array($diff2)  , 'Failure in testValidatorsAreCreated().  Failed with is_array($diff2)'.' $diff2='.var_export($diff2,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));
                 $this->assertTrue(  empty($diff2)     , 'Failure in testValidatorsAreCreated().  Failed with empty($diff2)'   .' $diff2='.var_export($diff2,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));

             //
             // Make sure we have them!
             //
                 foreach (  array_keys($expected_model_data) AS $table_name  ) {

                     $key_of_interest = 'validators';

                     $this->assertTrue(     isset($expected_model_data[$table_name])                            , 'Failure of:    isset($expected_model_data['.var_export($table_name,TRUE).'])');
                     $this->assertTrue(  is_array($expected_model_data[$table_name])                            , 'Failure of: is_array($expected_model_data['.var_export($table_name,TRUE).'])');
                     $this->assertTrue(     isset($expected_model_data[$table_name]['base'])                    , 'Failure of:    isset($expected_model_data['.var_export($table_name,TRUE).'][\'base\'])');
                     $this->assertTrue(  is_array($expected_model_data[$table_name]['base'])                    , 'Failure of: is_array($expected_model_data['.var_export($table_name,TRUE).'][\'base\'])');
                     $this->assertTrue(     isset($expected_model_data[$table_name]['base'][$key_of_interest])  , 'Failure of:    isset($expected_model_data['.var_export($table_name,TRUE).'][\'base\']['.var_export($key_of_interest,TRUE).'])');
                     $this->assertTrue(  is_array($expected_model_data[$table_name]['base'][$key_of_interest])  , 'Failure of: is_array($expected_model_data['.var_export($table_name,TRUE).'][\'base\']['.var_export($key_of_interest,TRUE).'])');

                     $this->assertTrue(     isset($generated_model_data[$table_name])                            , 'Failure of:    isset($generated_model_data['.var_export($table_name,TRUE).'])');
                     $this->assertTrue(  is_array($generated_model_data[$table_name])                            , 'Failure of: is_array($generated_model_data['.var_export($table_name,TRUE).'])');
                     $this->assertTrue(     isset($generated_model_data[$table_name]['base'])                    , 'Failure of:    isset($generated_model_data['.var_export($table_name,TRUE).'][\'base\'])');
                     $this->assertTrue(  is_array($generated_model_data[$table_name]['base'])                    , 'Failure of: is_array($generated_model_data['.var_export($table_name,TRUE).'][\'base\'])');
                     $this->assertTrue(     isset($generated_model_data[$table_name]['base'][$key_of_interest])  , 'Failure of:    isset($generated_model_data['.var_export($table_name,TRUE).'][\'base\']['.var_export($key_of_interest,TRUE).'])');
                     $this->assertTrue(  is_array($generated_model_data[$table_name]['base'][$key_of_interest])  , 'Failure of: is_array($generated_model_data['.var_export($table_name,TRUE).'][\'base\']['.var_export($key_of_interest,TRUE).'])');

                     $expected  = $expected_model_data  [$table_name]['base'][$key_of_interest];
                     $generated = $generated_model_data [$table_name]['base'][$key_of_interest];


//print("\n\n\n".'=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-'."\n");
//print($table_name);var_export($table_name);
//print("\n");
//print('$expected=');var_export($expected);
//print('$generated=');var_export($generated);
//print("\n");

                     $left_diff  = array_diff($expected, $generated);
                     $right_diff = array_diff($generated, $expected);

                     $this->assertTrue(  isset($left_diff)      , 'FAILURE of isset($left_diff) for '.  $key_of_interest. ' on table '.var_export($table_name,TRUE).'.');
                     $this->assertTrue(  isset($right_diff)     , 'FAILURE of isset($right_diff) for '. $key_of_interest. ' on table '.var_export($table_name,TRUE).'.');

                     $this->assertTrue(  is_array($left_diff)   , 'FAILURE of is_array($left_diff) for '.  $key_of_interest .' on table '.var_export($table_name,TRUE).'.');
                     $this->assertTrue(  is_array($right_diff)  , 'FAILURE of is_array($right_diff) for '. $key_of_interest .' on table '.var_export($table_name,TRUE).'.');

                     $this->assertTrue(  empty($left_diff)      , 'FAILURE of empty($left_diff) for '.  $key_of_interest .' on table '.var_export($table_name,TRUE).'.');
                     $this->assertTrue(  empty($right_diff)     , 'FAILURE of empty($right_diff) for '. $key_of_interest .' on table '.var_export($table_name,TRUE).'.');

                 } // foreach

            //
            // End SkinnyMVC test environment.
            //
                $skinnyMvcTest->end();
        }
    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//
        public function testRetrieversAreCreated()
        {
            //
            // Construct test model (in a PHP array).
            //
                $model = self::theTestModel();

            //
            // Calculate expected model data (in a PHP array).
            //
                $expected_model_data = self::calculateExpectedModelData($model);

                $this->assertTrue(  isset($expected_model_data)     );
                $this->assertTrue(  is_array($expected_model_data)  );

            //
            // Start SkinnyMVC test environment.
            //
                $skinnyMvcTest = new SkinnyMvcTest();

                $skinnyMvcTest->begin();

            //
            // php skinnymvc.php install
            //
                $skinnyMvcTest->skinnyMvcInstall();

            //
            // Set the "config/schema.php" file.
            //
                $config_schema_filename = $skinnyMvcTest->getPath() .'/'. 'config/schema.php';
                $config_schema_contents = '<?php'."\n".'$model = '. var_export($model,TRUE) .';' ."\n";
                file_put_contents($config_schema_filename, $config_schema_contents);

            //
            // php skinnymvc.php generateModel
            //
                $skinnyMvcTest->skinnyMvcGenerateModel();

            //
            // Get generated model data.
            //
                $generated_model_data = self::getGeneratedModelData($skinnyMvcTest, $model);

//print('========================= GENERATED MODEL DATA' ."\n");
//print($table_name);var_export($table_name);
//print("\n");
//var_export($generated_model_data);print("\n\n\n");
//print('========================= EXPECTED MODEL DATA' ."\n");
//var_export($expected_model_data);print("\n\n\n");

             //
             // Make sure we have the same tables in generated table data and expected table data.
             //
                 $diff1 = array_diff_key($generated_model_data, $expected_model_data);
                 $this->assertTrue(  isset($diff1)     , 'Failure in testRetrieversAreCreated().  Failed with isset($diff1)'   .' $diff1='.var_export($diff1,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));
                 $this->assertTrue(  is_array($diff1)  , 'Failure in testRetrieversAreCreated().  Failed with is_array($diff1)'.' $diff1='.var_export($diff1,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));
                 $this->assertTrue(  empty($diff1)     , 'Failure in testRetrieversAreCreated().  Failed with empty($diff1)'   .' $diff1='.var_export($diff1,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));

                 $diff2 = array_diff_key($expected_model_data, $generated_model_data);
                 $this->assertTrue(  isset($diff2)     , 'Failure in testRetrieversAreCreated().  Failed with isset($diff2)'   .' $diff2='.var_export($diff2,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));
                 $this->assertTrue(  is_array($diff2)  , 'Failure in testRetrieversAreCreated().  Failed with is_array($diff2)'.' $diff2='.var_export($diff2,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));
                 $this->assertTrue(  empty($diff2)     , 'Failure in testRetrieversAreCreated().  Failed with empty($diff2)'   .' $diff2='.var_export($diff2,TRUE)."\n\n".'$generated_model_data='.var_export($generated_model_data,TRUE)."\n\n".'$expected_model_data='.var_export($expected_model_data,TRUE));

             //
             // Make sure we have them!
             //
                 foreach (  array_keys($expected_model_data) AS $table_name  ) {

                     $key_of_interest = 'retrievers';

                     $this->assertTrue(     isset($expected_model_data[$table_name])                            , 'Failure of:    isset($expected_model_data['.var_export($table_name,TRUE).'])');
                     $this->assertTrue(  is_array($expected_model_data[$table_name])                            , 'Failure of: is_array($expected_model_data['.var_export($table_name,TRUE).'])');
                     $this->assertTrue(     isset($expected_model_data[$table_name]['base'])                    , 'Failure of:    isset($expected_model_data['.var_export($table_name,TRUE).'][\'base\'])');
                     $this->assertTrue(  is_array($expected_model_data[$table_name]['base'])                    , 'Failure of: is_array($expected_model_data['.var_export($table_name,TRUE).'][\'base\'])');
                     $this->assertTrue(     isset($expected_model_data[$table_name]['base'][$key_of_interest])  , 'Failure of:    isset($expected_model_data['.var_export($table_name,TRUE).'][\'base\']['.var_export($key_of_interest,TRUE).'])');
                     $this->assertTrue(  is_array($expected_model_data[$table_name]['base'][$key_of_interest])  , 'Failure of: is_array($expected_model_data['.var_export($table_name,TRUE).'][\'base\']['.var_export($key_of_interest,TRUE).'])');

                     $this->assertTrue(     isset($generated_model_data[$table_name])                            , 'Failure of:    isset($generated_model_data['.var_export($table_name,TRUE).'])');
                     $this->assertTrue(  is_array($generated_model_data[$table_name])                            , 'Failure of: is_array($generated_model_data['.var_export($table_name,TRUE).'])');
                     $this->assertTrue(     isset($generated_model_data[$table_name]['base'])                    , 'Failure of:    isset($generated_model_data['.var_export($table_name,TRUE).'][\'base\'])');
                     $this->assertTrue(  is_array($generated_model_data[$table_name]['base'])                    , 'Failure of: is_array($generated_model_data['.var_export($table_name,TRUE).'][\'base\'])');
                     $this->assertTrue(     isset($generated_model_data[$table_name]['base'][$key_of_interest])  , 'Failure of:    isset($generated_model_data['.var_export($table_name,TRUE).'][\'base\']['.var_export($key_of_interest,TRUE).'])');
                     $this->assertTrue(  is_array($generated_model_data[$table_name]['base'][$key_of_interest])  , 'Failure of: is_array($generated_model_data['.var_export($table_name,TRUE).'][\'base\']['.var_export($key_of_interest,TRUE).'])');

                     $expected  = $expected_model_data  [$table_name]['base'][$key_of_interest];
                     $generated = $generated_model_data [$table_name]['base'][$key_of_interest];


//print("\n\n\n".'=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-'."\n");
//print($table_name);var_export($table_name);
//print("\n");
//print('$expected=');var_export($expected);
//print('$generated=');var_export($generated);
//print("\n");

                     $left_diff  = array_diff($expected, $generated);
                     $right_diff = array_diff($generated, $expected);

                     $this->assertTrue(  isset($left_diff)      , 'FAILURE of isset($left_diff) for '.  $key_of_interest. ' on table '.var_export($table_name,TRUE).'.');
                     $this->assertTrue(  isset($right_diff)     , 'FAILURE of isset($right_diff) for '. $key_of_interest. ' on table '.var_export($table_name,TRUE).'.');

                     $this->assertTrue(  is_array($left_diff)   , 'FAILURE of is_array($left_diff) for '.  $key_of_interest .' on table '.var_export($table_name,TRUE).'.');
                     $this->assertTrue(  is_array($right_diff)  , 'FAILURE of is_array($right_diff) for '. $key_of_interest .' on table '.var_export($table_name,TRUE).'.');

                     $this->assertTrue(  empty($left_diff)      , 'FAILURE of empty($left_diff) for '.  $key_of_interest .' on table '.var_export($table_name,TRUE).'.');
                     $this->assertTrue(  empty($right_diff)     , 'FAILURE of empty($right_diff) for '. $key_of_interest .' on table '.var_export($table_name,TRUE).'.');

                 } // foreach

            //
            // End SkinnyMVC test environment.
            //
                $skinnyMvcTest->end();
        }

    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

        public function testGettersReturnSetters()
        {
            //
            // Construct test model (in a PHP array).
            //
                $model = self::theTestModel();


            //
            // Start SkinnyMVC test environment.
            //
                $skinnyMvcTest = new SkinnyMvcTest();

                $skinnyMvcTest->begin();

            //
            // php skinnymvc.php install
            //
                $skinnyMvcTest->skinnyMvcInstall();

            //
            // Set the "config/schema.php" file.
            //
                $config_schema_filename = $skinnyMvcTest->getPath() .'/'. 'config/schema.php';
                $config_schema_contents = '<?php'."\n".'$model = '. var_export($model,TRUE) .';' ."\n";
                file_put_contents($config_schema_filename, $config_schema_contents);

            //
            // php skinnymvc.php generateModel
            //
                $skinnyMvcTest->skinnyMvcGenerateModel();

            //
            // Get generated model data.
            //
                $generated_model_data = self::getGeneratedModelData($skinnyMvcTest, $model);

print("\n\n\n".'=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-'."\n");
print('$generated_model_data=');var_export($generated_model_data);
print("\n");


            //
            // For each table...
            //
                foreach (  $generated_model_data AS $generated_table_name => $generated_table_data  ) {

                    $code = '<'.'?'.'php'."\n"
                          ."\n". 'require_once(  \'lib/skinnymvc/core/BaseModel.php\'  );'
                          ."\n". 'require_once(  \'lib/skinnymvc/core/Model.php\'      );'
                          ."\n". ''
                          ;

// #### TODO


                } // foreach
// #### TODO TODO TODO TODO TODO
//
//     $this->setX($x);
//     $this->assertTrue(  $x == $obj->getX()  );
//



            //
            // End SkinnyMVC test environment.
            //
                $skinnyMvcTest->end();
        }

    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

        public function testFetchersAreCreated()
        {
$this->assertTrue(FALSE, 'TODO testFetchersAreCreated()');
        }
    //////////////////////////////////////////////////////////////////////////////////////////////////////// M E T H O D S //

}
//////////////////////////////////////////////////////////////////////////////////////////////////////////// C L A S S E S //
