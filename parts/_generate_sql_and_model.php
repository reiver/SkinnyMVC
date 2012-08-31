    if (  !function_exists('camelize')  ) {
        function camelize($str)
        {
            $str = str_replace("_", " ", $str);
            $str = ucwords($str);
            $str = str_replace(" ", "", $str);
            return $str;
        }
    }

//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

    function _get_nonkeys()
    {
        $nonkeys = array('_UNIQUE', '_UNIQUES', '_INDEX', '_INDEXES', '_PRIMARY_KEY', '_FOREIGN_KEY', '_FOREIGN_KEYS', '_FULLTEXT', '_DATABASE_KEY', '_TABLE_NAME');

        return $nonkeys;
    }

//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

    function _generate_sql($param=array())
    {
        //
        // Deal with parameters.
        //
            $config_schema_path = 'config/schema.php';
            if (  isset($param['schema-path']) && is_string($param['schema-path'])  ) {
                $config_schema_path = $param['schema-path'];
            }

            $sql_dir_path = 'lib/skinnymvc/model/sql';
            if (  isset($param['sql-path']) && is_string($param['sql-path'])  ) {
                $sql_dir_path = $param['sql-path'];
            }

        //
        // Check to see if files we ned exist.
        //
            if(!file_exists($config_schema_path)) {
                //Error
                echo "File schema.php does not exist!\n";
                exit;
            }

            if(!file_exists('config/settings.php')) {
                //Error
                echo "File settings.php does not exist!\n";
                exit;
            }


        //
        // include() the files we need.
        //
            include($config_schema_path);
            include('config/settings.php');


        //
        // Generate SQL.
        //
            $sql = '';

            if (!empty($model) && is_array($model) && count($model)>0) {
                $sql = _create_sql_from_array($model);
            } else {
                //Error
                echo "File schema.php is empty!\n";
                exit;
            }

            if (!empty($sql)) {
                if (count($sql)==1 && isset($sql['__database'])) {
                    @file_put_contents($sql_dir_path.'/database.sql', $sql['__database']);
                    return;
                }

                foreach($sql as $dbName=>$dbSQL) {
                    @file_put_contents($sql_dir_path.'/'.$dbName.'.sql', $dbSQL);
                }
            }

    }

//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

    function _generate_model($param=array())
    {
        //
        // Deal with parameters.
        //
            $config_schema_path = 'config/schema.php';
            if (  isset($param['schema-path']) && is_string($param['schema-path'])  ) {
                $config_schema_path = $param['schema-path'];
            }

            $model_dir_path = 'lib/skinnymvc/model';
            if (  isset($param['model-path']) && is_string($param['model-path'])  ) {
                $model_dir_path = $param['model-path'];
            }

        if(!file_exists($config_schema_path)) {
            //Error
            echo "File schema.php does not exist!\n";
    /////// EXIT
            exit;
        }

        include($config_schema_path);
        include('config/settings.php');

        if (!empty($model) && is_array($model) && count($model)>0) {
            _create_model_from_array($model, $model_dir_path);
        }

    }

//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

    function _create_sql_from_array($model)
    {
        //
        //
        //
            $return_sql = array();

            if (isset(SkinnySettings::$CONFIG['dbs']) && !empty(SkinnySettings::$CONFIG['dbs']) && is_array(SkinnySettings::$CONFIG['dbs'])) {
                $dbs = array();
                foreach(SkinnySettings::$CONFIG['dbs'] as $db_key=>$value) {
                   array_push($dbs, $db_key);
                   $return_sql[$db_key] = '';
                }
            }
            $return_sql['__database'] = ''; //default db

            foreach($model As $tableName=>$table) {
                if (isset($table['_TABLE_NAME']) && !empty($table['_TABLE_NAME'])) {
                    $tableName = $table['_TABLE_NAME'];
                }

                //get the db driver (for ex. "mysql" or "pgsql")
                //we need this for stuff like "auto_increment" in $special
                if (isset(SkinnySettings::$CONFIG['dbs']) && !empty(SkinnySettings::$CONFIG['dbs']) && is_array(SkinnySettings::$CONFIG['dbs'])) {
                    if (isset($table['_DATABASE_KEY']) && !empty($table['_DATABASE_KEY'])) {
                        if (!in_array($table['_DATABASE_KEY'], $dbs)) {
                            //Error
                            echo "Invalid _DATABASE_KEY in table $tableName \n";
                            exit;
                        }
                        $db_driver = SkinnySettings::$CONFIG['dbs'][$table['_DATABASE_KEY']]['dbdriver'];
                    } else {
                        $db_driver = SkinnySettings::$CONFIG['dbdriver'];
                    }
                } else {
                    if (isset($table['_DATABASE_KEY'])) {
                        //Error
                        echo "Invalid _DATABASE_KEY in table $tableName; dbs not specified in config; \n";
                        exit;
                    }
                    $db_driver = SkinnySettings::$CONFIG['dbdriver'];
                }

                //For postgres
                $create_sequence = false;

                if ($db_driver == "pgsql") {
                    $sql = "CREATE TABLE ".$tableName." (\n";
                    $unique_key = "UNIQUE";
                } else {
                    $sql = "CREATE TABLE IF NOT EXISTS ".$tableName." (\n";
                    $unique_key = "UNIQUE KEY";
                }
                foreach($table As $fieldName=>$field) {
                    if (in_array($fieldName, _get_nonkeys())) {
                        continue;
                    }
                    if (is_array($field)){
                        if (!isset($field['type']) || empty($field['type'])) { 
                            die("You must specify the data type for \'$fieldName\'\n");
                        }
                        $type = $field['type'];
                        $null = (isset($field['null'])&&$field['null'])?"NULL":"NOT NULL";
                        $special = isset($field['special'])?$field['special']:"";
                        if($db_driver == 'pgsql' && $special == 'auto_increment') {
                            $special = " DEFAULT nextval('".$tableName."_seq')";
                            $create_sequence = true;
                        }
                    } else {
                        if(empty($field)) {
                            $type='int';
                        } else {
                            $type = $field;
                        }
                        $null = "NOT NULL";
                        $special = "";
                    }
                    $sql .= "    ".$fieldName." ".strtoupper($type)." ".$null." ".$special.",\n";
                }//end foreach
                $sql = substr($sql, 0, strrpos($sql,','));

                if (isset($table['_PRIMARY_KEY']) && is_string($table['_PRIMARY_KEY'])) {
                    $sql .= ",\n";
                    $sql .= "    PRIMARY KEY (". $table['_PRIMARY_KEY'] .")";
                } else if (isset($table['_PRIMARY_KEY']) && !empty($table['_PRIMARY_KEY'])) {
                    $sql .= ",\n";
                    $sql .= "    PRIMARY KEY (".implode(',',$table['_PRIMARY_KEY']).")";
                } else {
                    echo "Warning: No _PRIMARY_KEY specified for \'$tableName\'!. It is recommended to define a primary key for every table to get the maximum functionality from SkinnyMVC.\n\n";
                }
                if (!empty($table['_UNIQUE'])) {
                    foreach (  $table['_UNIQUE'] AS $x  ) {

                        if (  is_array($x)  ) {

                            $sql .= ",\n";
                            $sql .= "    $unique_key (".implode(',',$x).")";

                        } else if (  is_string($x)  ) {

                            $sql .= ",\n";
                            $sql .= "    $unique_key (". $x .")";

                        } else {

                            $sql .= ",\n";
                            $sql .= "    $unique_key (". (string)$x .")";
                        }

                    } // foreach
                }
                if (!empty($table['_UNIQUES'])) {
                    foreach (  $table['_UNIQUES'] AS $x  ) {

                        if (  is_array($x)  ) {

                            $sql .= ",\n";
                            $sql .= "    $unique_key (".implode(',',$x).")";

                        } else if (  is_string($x)  ) {

                            $sql .= ",\n";
                            $sql .= "    $unique_key (". $x .")";

                        } else {

                            $sql .= ",\n";
                            $sql .= "    $unique_key (". (string)$x .")";
                        }

                    } // foreach
                }

                unset ($indexes);
                if ($db_driver == "pgsql") {
                    if (!empty($table['_INDEX'])) {
                        $indexes = '';
                        foreach($table['_INDEX'] As $key) {
                            $indexes .= "CREATE INDEX ".$tableName."_".$key."_idx ON $tableName($key);\n";
                        } 
                    } 
                    if (!empty($table['_INDEXES'])) {
                        if (!isset($indexes)) {
                            $indexes = '';
                        }
                        foreach($table['_INDEXES'] As $key) {
                            $indexes .= "CREATE INDEX ".$tableName."_".$key."_idx ON $tableName($key);\n";
                        }
                    }
                } else {
                    if (!empty($table['_INDEX'])) {
                        $sql .= ",\n";
                        $single_keys = array();
                        $complex_keys = array();
                        foreach($table['_INDEX'] As $key) {
                            if (is_array($key)) {
                                array_push($complex_keys,"    KEY (".implode(',',$key).")");
                            } else {
                                array_push($single_keys, $key);
                            }
                        }
                        if (!empty($complex_keys)) {
                            $sql .= implode (",\n", $complex_keys); 
                        }
                        if (!empty($single_keys)) {
                            if (!empty($complex_keys)) {
                                $sql .= ",\n";
                            }
                            $sql .= "    KEY (".implode("),\n    KEY (", $single_keys).")";
                        }
                    }


                    if (!empty($table['_INDEXES'])) {
                        $sql .= ",\n";
                        $single_keys = array();
                        $complex_keys = array();
                        foreach($table['_INDEXES'] As $key) {
                            if (is_array($key)) {
                                array_push($complex_keys,"    KEY (".implode(',',$key).")");
                            } else {
                                array_push($single_keys, $key);
                            }
                        }
                        if (!empty($complex_keys)) {
                            $sql .= implode (",\n", $complex_keys); 
                        }
                        if (!empty($single_keys)) {
                            if (!empty($complex_keys)) {
                                $sql .= ",\n";
                            }
                            $sql .= "    KEY (".implode("),\n    KEY (", $single_keys).")";
                        }
                    }
                } // end if $db_driver == "pgsql"

                if (!empty($table['_FOREIGN_KEY'])) {
                    $sql .= ",\n";
                    $foreign_keys = array();
                    foreach($table['_FOREIGN_KEY'] As $keyName=>$key) {
                        array_push($foreign_keys, "    FOREIGN KEY (".$keyName.") REFERENCES ".$key["table"]." (".$key["field"].")");
                    }
                    $sql .= implode (",\n", $foreign_keys);
                }
                if (!empty($table['_FOREIGN_KEYS'])) {
                    $sql .= ",\n";
                    $foreign_keys = array();
                    foreach($table['_FOREIGN_KEYS'] As $keyName=>$key) {
                        array_push($foreign_keys, "    FOREIGN KEY (".$keyName.") REFERENCES ".$key["table"]." (".$key["field"].")");
                    }
                    $sql .= implode (",\n", $foreign_keys);
                }

                if ($db_driver == 'pgsql') {
                    $sql .= "\n);\n\n";
                } else {
                    $sql .= "\n) ENGINE=InnoDB;\n\n";
                }

                if ($create_sequence) {
                    $sql = "CREATE SEQUENCE ".$tableName."_seq MINVALUE 1;\n\n".$sql;
                }

                if (isset($indexes) && !empty($indexes)) {
                    $sql .= $indexes."\n\n";
                }

                $sql .= "\n";

                if (isset($table['_DATABASE_KEY']) && !empty($table['_DATABASE_KEY'])) {
                    if (!in_array($table['_DATABASE_KEY'], $dbs)) {
                        //Error
                        echo "Invalid _DATABASE_KEY in table $tableName \n";
                        exit;
                    }
                    $return_sql[$table['_DATABASE_KEY']] .= $sql;
                } else {
                    $return_sql['__database'] .= $sql;
                }
            }

        //
        // Return.
        //
            return $return_sql;
    }


//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

    function _create_model_from_array($model, $model_dir_path)
    {
        //
        // Create a list of all db_keys
        //
            if (isset(SkinnySettings::$CONFIG['dbs']) && !empty(SkinnySettings::$CONFIG['dbs']) && is_array(SkinnySettings::$CONFIG['dbs'])) {
                $dbs = array();
                foreach(SkinnySettings::$CONFIG['dbs'] as $db_key=>$value) {
                    array_push($dbs, $db_key);
                }
            }


        //
        // For each table....
        //
            foreach($model As $tableName=>$table) {
                if (isset($table['_TABLE_NAME']) && !empty($table['_TABLE_NAME'])) {
                    $tableName = $table['_TABLE_NAME'];
                }
                unset($databaseKey);
                if (isset($table['_DATABASE_KEY']) && !empty($table['_DATABASE_KEY'])) {
                    $databaseKey = $table['_DATABASE_KEY'];
                }

                //get the db driver (for ex. "mysql" or "pgsql")
                if (isset(SkinnySettings::$CONFIG['dbs']) && !empty(SkinnySettings::$CONFIG['dbs']) && is_array(SkinnySettings::$CONFIG['dbs'])) {
                    if (isset($databaseKey)) {
                        if (!in_array($databaseKey, $dbs)) {
                            //Error
                            echo "Invalid _DATABASE_KEY in table $tableName \n";
                            exit;
                        }
                        $db_driver = SkinnySettings::$CONFIG['dbs'][$databaseKey]['dbdriver'];
                    } else {
                        $db_driver = SkinnySettings::$CONFIG['dbdriver'];
                    }
                } else {
                    if (isset($databaseKey)) {
                        //Error
                        echo "Invalid _DATABASE_KEY in table $tableName; dbs not defined \n";
                        exit;
                    }
                    $db_driver = SkinnySettings::$CONFIG['dbdriver'];
                }

                $tableFieldsArray = array_diff(array_keys($table), _get_nonkeys());
                $tableFields = 'array("'.implode('","', $tableFieldsArray).'")';

                $integerType = array('int', 'integer', 'smallint', 'tinyint', 'longint');


                $tableFieldsValues = 'array(';
                foreach($tableFieldsArray As $column) {
                    $tableFieldsValues .= '"'.$column.'"=>';
                    if ((isset($table[$column]['type']) && in_array($table[$column]['type'], $integerType)) || in_array($table[$column], $integerType)) {
                        $tableFieldsValues .= "0,";
                    } else {
                        if (isset($table[$column]['null']) && false === $table[$column]['null']) {
                            $tableFieldsValues .= '"",';
                        } else {
                            $tableFieldsValues .= "null,";
                        }
                    }
                }
                $tableFieldsValues .= ')';
                $tableNameCamelized = camelize($tableName);
                if(isset($table['_PRIMARY_KEY'])) {
                    if (  is_array($table['_PRIMARY_KEY'])  ) {
                        $pk = 'array("'.implode('","',$table['_PRIMARY_KEY']).'")';
                    } else if(  is_string($table['_PRIMARY_KEY'])  ) {
                        $pk = 'array('. var_export($table['_PRIMARY_KEY'],TRUE) .')';
                    } else {
                        $pk = 'array('. var_export((string)$table['_PRIMARY_KEY'],TRUE) .')';
                    }
                } else {
                    $pk = 'null';
                        echo "Warning: No _PRIMARY_KEY specified for '$tableName'!. It is recommended to define a primary key for every table to get the maximum functionality from SkinnyMVC.\n\n"; 
                }

                //
                // Data for foreign keys.
                //
                    $tableForeignKeys = array();
                    if (  isset($table['_FOREIGN_KEYS']) && is_array($table['_FOREIGN_KEYS']) && !empty($table['_FOREIGN_KEYS'])  ) {
                        $tableForeignKeys = array_keys($table['_FOREIGN_KEYS']);
                    }


                $class = '<?php

require_once(  dirname(__FILE__).\'/base/Base'.$tableNameCamelized.'.php\'  );

class '.$tableNameCamelized.' extends Base'.$tableNameCamelized.'
{
}
       ';

                $baseClass = '<?php
/**
 * filename:    Base'.$tableNameCamelized.'.php
 * description: Represents table \''.$tableName.'\'
 *
 */

require_once("../lib/skinnymvc/core/Model.php");

abstract class Base'.$tableNameCamelized.' extends SkinnyModel {

    // P R O C E D U R E S //////////////////////////////////////////////////////////////////////////////////////////////////
        public static function databaseKey() {
            return self::$databaseKey;
        }

    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

        public static function fetchByPK($pk) {
            if (!isset(self::$primaryKey)) {
                throw new SkinnyException(\'This class does not have a primary key defined.\');
            }
            $_pk = self::$primaryKey;
            if (count($_pk)>1 && (!is_array($pk) ||  count($_pk) !=  count($pk))) {
                throw new SkinnyException(\'Invalid primary key.\');
            }
            if (!is_array($pk)) {
                $con = SkinnyDbController::getWriteConnection(self::$databaseKey);

                $condition = "WHERE ".$_pk[0]."=". $con->quote($pk);
            } else {
                $condition = array();
                foreach ($pk As $column=>$value) {
                    if (!in_array($column, $_pk)) {
                        throw new SkinnyException(\'Invalid primary key.\');
                    }
                    array_push($condition, $column."=\'".$value."\'");
                }
                $condition = "WHERE ".implode(" AND ", $condition);
            }
            return self::selectOne(array(\'conditions\'=>$condition));
        }

    //---------------------------------------------------------------------------------------------------------------------//

        // This procedure is deprecated!  Use "fetchByPK" instead.
        public static function getByPK($pk) {
            return self::fetchByPK($pk);
        }
';


                if (  isset($table['_UNIQUES']) && is_array($table['_UNIQUES']) && !empty($table['_UNIQUES'])  ) {
                    foreach (  $table['_UNIQUES'] AS $theUnique  ) {

                        if (  is_string($theUnique)  ) {
                            $theUnique = array( $theUnique );
                        }

                        $theCamelCasePart = '';
                        $paramPart = '';
                        $sqlWherePart = '';


                        $paramPartSeparator = '';
                        $theCamelCasePartSeparator = ' ';
                        $sqlWherePartSeparator = '';
                        foreach (  $theUnique AS $aField) {

                            $theCamelCasePart .= $theCamelCasePartSeparator . $aField;
                            $paramPart .= $paramPartSeparator .'$'. $aField;
                            $sqlWherePart .= $sqlWherePartSeparator . '\'. self::$tableName .\'.' . $aField . ' = \'. SkinnyDbController::getReadConnection(self::$databaseKey)->quote($'. $aField .') .\'';

                            $paramPartSeparator = ', ';
                            $theCamelCasePartSeparator = ' and ';
                            $sqlWherePartSeparator = ' AND ';
                        } // foreach

                        $theCamelCasePart = str_replace(' ','',ucwords(str_replace('_',' ',$theCamelCasePart)));

                        $baseClass .= '
    //---------------------------------------------------------------------------------------------------------------------//

        public static function fetchBy'. $theCamelCasePart .'('. $paramPart .')
        {
            //
            // Fetch it.
            //
                $sql = \'

                    SELECT \'. self::$tableName .\'.*

                    FROM \'. self::$tableName .\'

                    WHERE '. $sqlWherePart .'

                \';

                $criteria = array();

                $criteria[\'sql\'] = $sql;

                $obj = self::selectOne($criteria);


            //
            // Return.
            //
                return $obj;
        }
';

                    } // foreach
                }


                $baseClass .= '
    ////////////////////////////////////////////////////////////////////////////////////////////////// P R O C E D U R E S //




    // G L O B A L S ////////////////////////////////////////////////////////////////////////////////////////////////////////
        protected static $fields = '.$tableFields.';
        protected static $tableName = \''.$tableName.'\';
        protected static $databaseKey = '.(isset($databaseKey)?'"'.$databaseKey.'"':"null").';
        protected static $className = \'Base'.$tableNameCamelized.'\';
        protected static $primaryKey = '.$pk.';
    //////////////////////////////////////////////////////////////////////////////////////////////////////// G L O B A L S //




    // C O N S T R U C T O R ////////////////////////////////////////////////////////////////////////////////////////////////
        public function __construct($fieldValues=null)
        {
            if(!empty($fieldValues)) {
                foreach($fieldValues As $field=>$value) {
                    if (is_numeric($field)) continue;
                    if (!in_array($field, self::$fields)) {
                        throw new SkinnyException(\'Invalid field name used in constructor.\');
                    }
                    $this->fieldValues[$field] = $value;
                }
                $this->originalFieldValues = $this->fieldValues;
            }
        }
    //////////////////////////////////////////////////////////////////////////////////////////////// C O N S T R U C T O R //




    // F I E L D S //////////////////////////////////////////////////////////////////////////////////////////////////////////
        protected $new = true;
        protected $originalFieldValues = '.$tableFieldsValues.';
        protected $fieldValues = '.$tableFieldsValues.';
        protected $modifiedFields = array();
        protected $magicTransactionNumber = null;

        //query error info
        protected $errorInfo = null;

        protected $cachedForeignKeyObjects;
    ////////////////////////////////////////////////////////////////////////////////////////////////////////// F I E L D S //




    // M E T H O D S ////////////////////////////////////////////////////////////////////////////////////////////////////////
        public function isNew()
        {
            return $this->new;
        }
    //---------------------------------------------------------------------------------------------------------------------//
        public function makeNew($new)
        {
            $this->new = $new;
        }

    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

        public function isInAnyTransaction()
        {
            if (!empty($this->magicTransactionNumber)) {
                $tn = SkinnyDbTransaction::transactionMagicNumber(self::$databaseKey);
                if ($tn == $this->magicTransactionNumber) {
                    return true;
                }
            }
            return false;
        }

    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

        public function canModify()
        {
            if ($this->isInAnyTransaction()) {
                return true;
            } else if (!SkinnyDbTransaction::transactionExists(self::$databaseKey)) {
                return true;
            }

            return false;
        }

    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

        public function updateMagicTransactionNumber($mn)
        {
            $this->magicTransactionNumber = $mn;
        }

    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

        public function fetchDbTransaction()
        {
            if ($this->canModify()) {
                return SkinnyDbTransaction::fetchDbTransaction(self::$databaseKey, $this->magicTransactionNumber);
            }
            return null;
        }

    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

        public function get()
        {
            return $this->fieldValues;
        }

    //---------------------------------------------------------------------------------------------------------------------//

        public function set($fieldValues=null)
        {
            if(!empty($fieldValues)) {
                foreach($fieldValues AS $field=>$value) {

                    if (!in_array($field, self::$fields)) {
                        throw new SkinnyException(\'Invalid field name used in set().\');
                    }

                    $this->fieldValues[$field] = $value;

                    if (  !in_array($field, $this->modifiedFields)  ) {
                        $this->modifiedFields[] = $field;
                    }
                }
            }
        }

    //---------------------------------------------------------------------------------------------------------------------//

        public function reset()
        {
            $this->modifiedFields = array();

            $this->fieldValues   = $this->originalFieldValues;
        }

    //---------------------------------------------------------------------------------------------------------------------//

        public function isValid()
        {
            $is_valid = parent::isValid()
';

foreach ($tableFieldsArray as $column) {
    $columnCamelized = camelize($column);

    $baseClass .= '                      && $this->isValid'. $columnCamelized .'()'."\n";
} // foreach
$baseClass .= '                      ;'."\n";

$baseClass .=
'
            return $is_valid;
        }

';

                foreach ($tableFieldsArray as $column) {
                    $columnCamelized = camelize($column);

                    $baseClass .=     ''
                               .      '    '
                               .      '//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//'
                               .      "\n"
                               .      '    '
                               .      '// field: '. $column
                               .      "\n"
                               .      '    '
                               .      '//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//'
                               .      "\n"
                               .      "\n"
                               ;

                    $baseClass .=     ''
                               .      "        public function get".$columnCamelized."()\n"
                               .      "        {\n"
                               .      "            return \$this->fieldValues['$column'];\n"
                               .      "        }"
                               .      "\n"
                               .      "\n"
                               ;

                    $baseClass .=     ''
                               .      '    '
                               .      '//---------------------------------------------------------------------------------------------------------------------//'
                               .      "\n"
                               .      "\n"
                               ;

                    $baseClass .=     ''
                               .      "        public function set".$columnCamelized."(\$value)\n"
                               .      "        {"
                               .      "            \$this->fieldValues['$column'] = \$value;\n"
                               .      '            if (  !in_array('. var_export($column,TRUE) .', $this->modifiedFields)  ) {' ."\n"
                               .      '                $this->modifiedFields[] = '. var_export($column,TRUE) .';'               ."\n"
                               .      '            }'                                                                           ."\n"
                               .      '        }'                                                                               ."\n"
                               .      "\n"
                               ;

                    $baseClass .=     ''
                               .      '    '
                               .      '//---------------------------------------------------------------------------------------------------------------------//'
                               .      "\n"
                               .      "\n"
                               ;

                    $baseClass .=     ''
                               .      '        public function reset'. $columnCamelized .'()'                                                                            ."\n"
                               .      '        {'                                                                                                                        ."\n"
                               .      '            $i = array_search('. var_export($column,TRUE) .', $this->modifiedFields);'                                            ."\n"
                               .      '            if (  FALSE !== $i  ) {'                                                                                              ."\n"
                               .      '                unset($this->modifiedFields[$i]);'                                                                                ."\n"
                               .      '                $this->fieldValues['. var_export($column,TRUE) .'] = $this->originalFieldValues['. var_export($column,TRUE) .'];' ."\n"
                               .      '            }'                                                                                                                    ."\n"
                               .      '        }'                                                                                                                        ."\n"
                               .      "\n"
                               ;

                    $baseClass .=     ''
                               .      '    '
                               .      '//---------------------------------------------------------------------------------------------------------------------//'
                               .      "\n"
                               .      "\n"
                               ;

                    $baseClass .=     '        public function isValid'. $columnCamelized .'()' ."\n"
                               .      '        {'                                               ."\n"
                               .      '            return TRUE;'                                ."\n"
                               .      '        }'                                               ."\n"
                               .      "\n"
                               ;

                    if (  isset($table['_FOREIGN_KEYS']) && is_array($table['_FOREIGN_KEYS']) && !empty($table['_FOREIGN_KEYS'])  ) {
                        if (  in_array($column, $tableForeignKeys) && isset($table['_FOREIGN_KEYS'][$column]['table'])  ) {
                            $foreignCamelizedClassName = camelize($table['_FOREIGN_KEYS'][$column]['table']);

                            $retrieverMethodName = $columnCamelized;
                            if (  2 < strlen($retrieverMethodName) && 'Id' == substr($retrieverMethodName,-2)  ) {
                                $retrieverMethodName = substr($retrieverMethodName, 0, -2);
                            }

                            $baseClass .=     ''
                                       .      '    '
                                       .      '//---------------------------------------------------------------------------------------------------------------------//'
                                       .      "\n"
                                       .      "\n"
                                       ;

                            $baseClass .=     '        public function retrieve'. $retrieverMethodName .'()'                                                    ."\n"
                                       .      '        {'                                                                                                       ."\n"
                                       .      '            return '.$foreignCamelizedClassName.'::fetchByPK($this->fieldValues['.var_export($column,TRUE).']);' ."\n"
                                       .      '        }'                                                                                                       ."\n"
                                       .      ''                                                                                                                ."\n"
                                       ;


                            $baseClass .=     ''
                                       .      '    '
                                       .      '//---------------------------------------------------------------------------------------------------------------------//'
                                       .      "\n"
                                       .      "\n"
                                       ;

                            $baseClass .=     '        public function recache'. $retrieverMethodName .'()'                      ."\n"
                                       .      '        {'                                                                        ."\n"
                                       .      '            $this->cachedForeignKeyObjects['.var_export($column,TRUE).'] = $this->retrieve'. $retrieverMethodName .'();' ."\n"
                                       .      ''                                                                                 ."\n"
                                       .      '            return $this->cachedForeignKeyObjects['.var_export($column,TRUE).'];' ."\n"
                                       .      '        }'                                                                        ."\n"
                                       .      ''                                                                                 ."\n"
                                       ;


                            $baseClass .=     ''
                                       .      '    '
                                       .      '//---------------------------------------------------------------------------------------------------------------------//'
                                       .      "\n"
                                       .      "\n"
                                       ;

                            $baseClass .=     '        public function clearCached'. $retrieverMethodName .'()'                      ."\n"
                                       .      '        {'                                                                        ."\n"
                                       .      '            unset($this->cachedForeignKeyObjects['.var_export($column,TRUE).']);' ."\n"
                                       .      ''                                                                                 ."\n"
                                       .      '            return TRUE;' ."\n"
                                       .      '        }'                                                                        ."\n"
                                       .      ''                                                                                 ."\n"
                                       ;



                            $baseClass .=     ''
                                       .      '    '
                                       .      '//---------------------------------------------------------------------------------------------------------------------//'
                                       .      "\n"
                                       .      "\n"
                                       ;

                            $baseClass .=     '        public function cached'. $retrieverMethodName .'()'                       ."\n"
                                       .      '        {'                                                                        ."\n"
                                       .      '            if (  !isset($this->cachedForeignKeyObjects['.var_export($column,TRUE).']) || !is_object($this->cachedForeignKeyObjects['.var_export($column,TRUE).']) || ! $this->cachedForeignKeyObjects['.var_export($column,TRUE).'] instanceof '. $foreignCamelizedClassName .'  ) {' ."\n"
                                       .      '                $this->cachedForeignKeyObjects['.var_export($column,TRUE).'] = $this->retrieve'. $retrieverMethodName .'();' ."\n"
                                       .      '            }'                                                                    ."\n"
                                       .      ''                                                                                 ."\n"
                                       .      '            return $this->cachedForeignKeyObjects['.var_export($column,TRUE).'];' ."\n"
                                       .      '        }'                                                                        ."\n"
                                       .      ''                                                                                 ."\n"
                                       ;
                        }
                    }

                    $baseClass .= "\n\n";

                } // foreach
                $baseClass .= '

        public function fetchFields()
        {
            return self::$fields;
        }

        public function fetchTableName()
        {
            return self::$tableName;
        }

   public function reload() {
     if (!isset(self::$primaryKey)) {
       throw new SkinnyException(\'This class does not have a primary key defined.\');
     }
     $_pk = self::$primaryKey;
     $pk = array();
     $condition = array();

     foreach($_pk As $field) {
        array_push($condition, $field.\'="\'.$this->fieldValues[$field].\'"\');
     }
     $condition = "WHERE ".implode(" AND ", $condition);

     $criteria = array();
     $criteria[\'conditions\'] = $condition;

     $criteria[\'limit\'] = \'LIMIT 1\';
     $criteria[\'offset\'] = \'OFFSET 0\';


     $result = self::selectArray(self::$tableName, $criteria, self::$databaseKey);

     if (empty($result)) {
        //Something is wrong
        throw new SkinnyException("Could not reload object from database. Criteria:".var_export($criteria, true));
     }

     $row = $result[0];

     foreach($row As $field=>$value) {
       if (is_numeric($field)) continue;
       $this->fieldValues[$field] = $value;
     }

     $this->originalFieldValues = $this->fieldValues;

     $this->new = false;
   }

   public function __clone() {
      $this->new = true;
      $_pk = self::$primaryKey;
      //TODO: Create a new object instead
      foreach($_pk As $field) {
         $this->fieldValues[$field]         = 0;
         $this->originalFieldValues[$field] = 0;
      }
   }
  
   public function delete() {
     if ($this->new) return false;
     if (!$this->canModify()) {
        throw new SkinnyDbException(\'Not in transaction.\');
     }
     $sql = "DELETE FROM '.$tableName.' WHERE ";

     $_pk = self::$primaryKey;
     $npk = count($_pk);
     for ($loop=0;$loop<$npk;$loop++) {
       $sql .= $_pk[$loop]."=".$this->fieldValues[$_pk[$loop]];
       if($loop<$npk-1) {
         $sql .= " AND ";
       }
     }

     $con = SkinnyDbController::getWriteConnection(self::$databaseKey);
     $result = $con->exec($sql);
     if (!empty($result)) {
        return true;
     }
     return true;
   }
 

        protected function assertMaySave()
        {
            //
            // Check various things.
            //
';

                foreach($tableFieldsArray As $column) {
                    if ((isset($table[$column]['type']) && in_array($table[$column]['type'], $integerType)) || in_array($table[$column], $integerType)) {
                        $baseClass .= '                if (!is_numeric($this->fieldValues["'.$column.'"]) && !is_null($this->fieldValues["'.$column.'"])) {'."\n"
                                   .  '                    throw new SkinnyException(\''.$column.' must be numeric.\');'."\n"
                                   .  '                }'."\n";
                    } else {
                        if (isset($table[$column]['null']) && false === $table[$column]['null']) {
                            $baseClass .= '                if (is_null($this->fieldValues["'.$column.'"])) {'."\n"
                                       .  '                    throw new SkinnyException(\''.$column.' must not be null.\');'."\n"
                                       .  '                }'."\n";
                        }
                    }
                }



$baseClass .= '
            //
            // Check the parent version of this method too.
            //
                parent::assertMaySave();
        }


        public function save()
        {
            //
            // Run pre-Save.
            //
                $this->preSave();


            //
            //
            //
                if (!$this->canModify()) {
                    throw new SkinnyDbException(\'Not in transaction.\');
                }

                $this->assertMaySave();


            //
            //
            //
                $con = SkinnyDbController::getWriteConnection(self::$databaseKey);

                $numfv = count($this->fieldValues);
                $inserted = false;
                if($this->new) {
                    $sql = "INSERT INTO '.$tableName.' VALUES (";
                    $loop = 1;
                    foreach($this->fieldValues As $field=>$value) {
                        if (  !in_array($field, $this->modifiedFields)  ) {
                            $sql .= "DEFAULT";
                        } elseif(is_null($value)) {
                            $sql .= \'NULL\';
                        } else {
                            $sql .= $con->quote($value);
                        }
                        if ($loop<$numfv) {
                            $sql .= ", ";
                        }
                        $loop++;
                    } // foreach;
                    $sql .= ")";

                    $this->new = false;
                    $inserted = true;
                }else{
                    //UPDATE
                    $numfv = count($this->modifiedFields);
                    if ($numfv == 0) {
                        return true;
                    }
                    $sql = "UPDATE '.$tableName.' SET ";
                    $loop = 1;
                    foreach($this->fieldValues As $field=>$value) {
                        if (  in_array($field, $this->modifiedFields)  ) {
                            if (is_null($value)) {
                                $sql .= $field."=null";
                            } else if (is_bool($value)) {
                                if (  TRUE === $value  ) {
                                    $sql .= $field."=TRUE";
                                } else if (  FALSE === $value  ) {
                                    $sql .= $field."=FALSE";
                                } else {
                                    // Should never go in here!
                                }
                            } else {
                                $sql .= $field."=". $con->quote($value);
                            }
                            if ($loop<$numfv) {
                                $sql .= ", ";
                            }
                            $loop++;
                        }
                    } // foreach
                    $sql .= " WHERE ";
                    $_pk = self::$primaryKey;
                    $npk = count($_pk);
                    for ($loop=0;$loop<$npk;$loop++) {
                        $sql .= $_pk[$loop]."=".$this->fieldValues[$_pk[$loop]];
                        if($loop<$npk-1) {
                            $sql .= " AND ";
                        }
                    }
                }


      ';

   if ($db_driver == "pgsql") {
    // use query for inserts instead of exec for postgres
    //TODO: Make work with complex primary key! 
    $baseClass .= '
        $result = false;
        if ($inserted) {
           //Works with simple primary key only
           $stmt = $con->query($sql. " RETURNING ".self::$primaryKey[0]);

           if (!empty($stmt)) {
             $result = $stmt->fetchColumn();
             $this->fieldValues[self::$primaryKey[0]] = $result;
           } else {
             //result is false
           }
        } else {
           $result = $con->exec($sql);
        }
    ';

   } else {
    $baseClass .= '
        $result = $con->exec($sql);

        if ($inserted) {
            //TODO: Make work with multiple fields
            $this->fieldValues[self::$primaryKey[0]] = $con->lastInsertId();
        }
    ';
   } //end if

   // $result, if successful, contains the number of affected rows OR the insert_id (only in postgres).
   // In postgres, the insert_id should always be >0
   $baseClass .= '

            //
            // Since we just saved it, we need to update $this->originalFieldValues with the new saved values.
            //
                if (  $result  ) {
                    $this->originalFieldValues = $this->fieldValues;
                }

            //
            // Figure out result to return.
            //
                if (is_numeric($result) && $result>0) {
                   $result = true;
                } else {
                   $result = false;
                }

            //
            // Set error info.
            //
                $this->errorInfo = $con->errorInfo();


            //
            // Run post-Save.
            //
                $this->postSave();


            //
            // Return.
            //
                return $result;
        }

    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

        public function errorInfo()
        {
            return $this->errorInfo;
        }
    //////////////////////////////////////////////////////////////////////////////////////////////////////// M E T H O D S //


    //selects and returns ONE instance of the class
    public static function selectOne($criteria = array()) {
        if (  !isset($criteria["sql"])  ) {
            $criteria[\'limit\'] = \'LIMIT 1\';
            $criteria[\'offset\'] = \'OFFSET 0\';
        }
        $result = self::select($criteria);

        if(!empty($result)) {
            return $result[0];
        } else {
            return null;
        }
    }

  /**
   * Selects and creates multiple instances of the class
   * @param mixed $criteria
   * @return array of class instances
   */
    public static function select($criteria = array()) {

        if (  !isset($criteria["sql"])  ) {

            //columns can be SQL or an array.
            if (empty($criteria[\'columns\'])) {
                $criteria[\'columns\'] = \'*\';
            } else {
                if (is_array($criteria[\'columns\'])) {
                    foreach($criteria[\'columns\'] As $column) {
                        if (!in_array($column, self::$fields)) {
                            throw new SkinnyException(\'Invalid column: "\'.$column.\'".\');
                        }
                    }
                    $criteria[\'columns\'] = implode (\',\', $criteria[\'columns\']);
                }
            }

            // group can be SQL or array
            if (empty($criteria[\'group\'])) {
                $criteria[\'group\'] = \'\';
            } else {
                if(is_array($criteria[\'group\'])) {
                    foreach($criteria[\'group\'] As $column) {
                        if (!in_array($column, self::$fields)) {
                            throw new SkinnyException(\'Invalid column: "\'.$column.\'".\');
                        }
                    }
                    $criteria[\'group\'] = \'GROUP BY \'.implode(\',\',$criteria[\'group\']);
                }
            }
        }


        $result = self::selectArray(self::$tableName, $criteria, self::$databaseKey);

        if (is_array($result)) {
            $objects = array();

            foreach($result as $row) {
                $obj = new '.$tableNameCamelized.'($row);
                $obj->makeNew(false); 
                array_push($objects, $obj);
            }
            return $objects;
        }
        return null;
    }

}
       ';

            if (!file_exists($model_dir_path.'/'.$tableNameCamelized.'.php')) {
                @file_put_contents($model_dir_path.'/'.$tableNameCamelized.'.php', $class);
                echo "Created $tableNameCamelized.php\n";
            }
            @file_put_contents($model_dir_path.'/base/Base'.$tableNameCamelized.'.php', $baseClass);
            echo "Created Base$tableNameCamelized.php\n";
        }
    }

