<?php

// C L A S S E S ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    class SkinnyMvcTest
    {
        // P R O C E D U R E S //////////////////////////////////////////////////////////////////////////////////////////////
            private static function generateTempDirName()
            {
                global $argv;

                //
                // Construct Temp Dir Name.
                //
                    $dirname = 'TEMP-'.$argv[0].'-'.date('YmdHis').'-'.md5(uniqid('',TRUE));

                //
                // Return.
                //
                    return $dirname;
            }
        ////////////////////////////////////////////////////////////////////////////////////////////// P R O C E D U R E S //



        // F I E L D S //////////////////////////////////////////////////////////////////////////////////////////////////////
            private $dirname;
            private $have_begun;
        ////////////////////////////////////////////////////////////////////////////////////////////////////// F I E L D S //



        // C O N S T R U C T O R S //////////////////////////////////////////////////////////////////////////////////////////
            function __construct()
            {
                //
                // Set fields.
                //
                    $this->dirname    = self::generateTempDirName();
                    $this->have_begun = FALSE;
            }
        ////////////////////////////////////////////////////////////////////////////////////////// C O N S T R U C T O R S //



        // D E S T R U C T O R //////////////////////////////////////////////////////////////////////////////////////////////
            function __destruct()
            {
                //
                //
                //
                    if (  $this->have_begun  ) {
                        $this->end();
                    }
            }
        ////////////////////////////////////////////////////////////////////////////////////////////// D E S T R U C T O R //



        // M E T H O D S ////////////////////////////////////////////////////////////////////////////////////////////////////
            public function getPath()
            {
                //
                //
                //
                    if (  ! $this->have_begun  ) {
            /////////// RETURN
                        return FALSE;
                    }

                //
                //
                //
                    return realpath($this->dirname);
            }

        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

            public function haveBegun()
            {
                return $this->have_begun;
            }

        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

            function begin()
            {
                //
                //
                //
                    if ( ! $this->have_begun  ) {

                        //
                        // Create TEMP directory to do test in.
                        //
                            $cmd = 'mkdir '. $this->dirname;

                            `$cmd`;

                        //
                        // Create a new copy of skinnymvc.php
                        //
                            $cmd = 'cd .. ; php build.php > tests/'. $this->dirname .'/skinnymvc.php';

                            `$cmd`;

                        //
                        //
                        //
                            $this->have_begun = TRUE;
                    }

                //
                // Return.
                //
                    return TRUE;
            }
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//
            function end()
            {
                //
                //
                //
                    if ( $this->have_begun  ) {

                        //
                        // Remove TEMP directory.
                        //
                            $cmd = 'rm -fR '. $this->dirname;

                            `$cmd`;

                    }

                //
                // Return.
                //
                    return TRUE;
            }

        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

            public function execSkinnyMvc()
            {
                //
                // Deal with parameters.
                //
                    $args = func_get_args();

                //
                // Construct Shell command.
                //
                    $cmd = 'cd '. $this->dirname .' ; php skinnymvc.php';
                    foreach (  $args AS $arg  ) {

                        if (  is_string($arg)  ) {
                            $cmd .= ' '. escapeshellcmd($arg);
                        }

                    } // foreach

                //
                // Execute Shell-basedSkinnyMVC command.
                //
                    $output = `$cmd`;

                //
                // Return.
                //
                    return $output;
            }

        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

            function skinnyMvcInstall()
            {
                //
                // Execute command.
                //
                    $output = $this->execSkinnyMvc('install');

                //
                // Return.
                //
                    return $output;
            }
        //-----------------------------------------------------------------------------------------------------------------//
            function skinnyMvcGenerateSql()
            {
                //
                // Execute command.
                //
                    $output = $this->execSkinnyMvc('generateSQL');

                //
                // Return.
                //
                    return $output;
            }
        //-----------------------------------------------------------------------------------------------------------------//
            function skinnyMvcGenerateModel()
            {
                //
                // Execute command.
                //
                    $output = $this->execSkinnyMvc('generateModel');

                //
                // Return.
                //
                    return $output;
            }
        //-----------------------------------------------------------------------------------------------------------------//
            function skinnyMvcCreateMod($mod)
            {
                //
                // Execute command.
                //
                    $output = $this->execSkinnyMvc('createMod', $mod);

                //
                // Return.
                //
                    return $output;
            }

        //////////////////////////////////////////////////////////////////////////////////////////////////// M E T H O D S //

    }
//////////////////////////////////////////////////////////////////////////////////////////////////////////// C L A S S E S //

