<?php

//
// Description:
//
//     This unit tests that the SkinnyMvcTest class works. (This class is used by all the other unit tests, so it's pretty
//     important that it works!!!)
//

// R E Q U I R E S //////////////////////////////////////////////////////////////////////////////////////////////////////////
    require_once( dirname(__FILE__).'/simpletest/autorun.php' );
    require_once( dirname(__FILE__).'/SkinnyMvcTest.php' );
////////////////////////////////////////////////////////////////////////////////////////////////////////// R E Q U I R E S //




// C L A S S E S ////////////////////////////////////////////////////////////////////////////////////////////////////////////
class TestSkinnyMvcTest extends UnitTestCase
{
    // P R O C E D U R E S //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////// P R O C E D U R E S //




    // M E T H O D S ////////////////////////////////////////////////////////////////////////////////////////////////////////
        public function testThatTestDirectoryGetsCreatedAfterBeginIsCalled()
        {
            //
            // Begin.
            //
                $skt = new SkinnyMvcTest();
                $this->assertTrue(  isset($skt)                    , 'Problem creating new SkinnyMvcTest object.  isset() test failed.');
                $this->assertTrue(  is_object($skt)                , 'Problem creating new SkinnyMvcTest object.  is_object() test failed.');
                $this->assertTrue(  $skt instanceof SkinnyMvcTest  , 'Problem creating new SkinnyMvcTest object.  instanceof test failed.');

                $haveBegun = $skt->begin();
                $this->assertTrue(  TRUE === $haveBegun  , 'Problem invoking begin() method on SkinnyMvcTest object.');

                $haveBegun = $skt->haveBegun();
                $this->assertTrue(  TRUE === $haveBegun  , 'haveBegun() method on SkinnyMvcTest object said we haven\'t begun.');

            //
            // Check Directory Exists.
            //
                $path = $skt->getPath();
                $this->assertTrue(  is_string($path)    , 'Problem getting path to Test SkinnyMVC directory.  is_string() test failed.');
                $this->assertTrue(  file_exists($path)  , 'Problem getting path to Test SkinnyMVC directory.  file_exists() test failed.');
                $this->assertTrue(  is_dir($path)       , 'Problem getting path to Test SkinnyMVC directory.  is_dir() test failed.');

            //
            // End.
            //
                $haveEnded = $skt->end();
                $this->assertTrue(  TRUE === $haveEnded  , 'Problem invoking end() method on SkinnyMvcTest object.');
        }

    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

        public function testThatTestDirectoryGetsDeleteAfterEndIsCalled()
        {
            //
            // Begin.
            //
                $skt = new SkinnyMvcTest();
                $this->assertTrue(  isset($skt)                    , 'Problem creating new SkinnyMvcTest object.  isset() test failed.');
                $this->assertTrue(  is_object($skt)                , 'Problem creating new SkinnyMvcTest object.  is_object() test failed.');
                $this->assertTrue(  $skt instanceof SkinnyMvcTest  , 'Problem creating new SkinnyMvcTest object.  instanceof test failed.');

                $haveBegun = $skt->begin();
                $this->assertTrue(  TRUE === $haveBegun  , 'Problem invoking begin() method on SkinnyMvcTest object.');

                $haveBegun = $skt->haveBegun();
                $this->assertTrue(  TRUE === $haveBegun  , 'haveBegun() method on SkinnyMvcTest object said we haven\'t begun.');

            //
            // Check Directory Exists.
            //
                $path = $skt->getPath();
                $this->assertTrue(  is_string($path)    , 'Problem getting path to Test SkinnyMVC directory.  is_string() test failed.');
                $this->assertTrue(  file_exists($path)  , 'Problem getting path to Test SkinnyMVC directory.  file_exists() test failed.');
                $this->assertTrue(  is_dir($path)       , 'Problem getting path to Test SkinnyMVC directory.  is_dir() test failed.');

            //
            // End.
            //
                $haveEnded = $skt->end();
                $this->assertTrue(  TRUE === $haveEnded  , 'Problem invoking end() method on SkinnyMvcTest object.');

            //
            // Check Directory Is Gone.
            //
                $this->assertTrue(  is_string($path)     , 'Test SkinnyMVC directory was not deleted.  is_string() test failed.');
                $this->assertTrue(  !file_exists($path)  , 'Test SkinnyMVC directory was not deleted.  !file_exists() test failed.');
        }

    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

        public function testThatSkinnyMvcPhpFileIsInTestDirectory()
        {
            //
            // Begin.
            //
                $skt = new SkinnyMvcTest();
                $this->assertTrue(  isset($skt)                    , 'Problem creating new SkinnyMvcTest object.  isset() test failed.');
                $this->assertTrue(  is_object($skt)                , 'Problem creating new SkinnyMvcTest object.  is_object() test failed.');
                $this->assertTrue(  $skt instanceof SkinnyMvcTest  , 'Problem creating new SkinnyMvcTest object.  instanceof test failed.');

                $haveBegun = $skt->begin();
                $this->assertTrue(  TRUE === $haveBegun  , 'Problem invoking begin() method on SkinnyMvcTest object.');

                $haveBegun = $skt->haveBegun();
                $this->assertTrue(  TRUE === $haveBegun  , 'haveBegun() method on SkinnyMvcTest object said we haven\'t begun.');

            //
            // Check Directory Exists.
            //
                $path = $skt->getPath();
                $this->assertTrue(  is_string($path)    , 'Problem getting path to Test SkinnyMVC directory.  is_string() test failed.');
                $this->assertTrue(  file_exists($path)  , 'Problem getting path to Test SkinnyMVC directory.  file_exists() test failed.');
                $this->assertTrue(  is_dir($path)       , 'Problem getting path to Test SkinnyMVC directory.  is_dir() test failed.');

            //
            // Check skinnymvc.php Exists.
            //
                $skinnymvc_php_path = $skt->getPath() . '/skinnymvc.php';
                $this->assertTrue(  is_string($skinnymvc_php_path)    , 'Problem with Test SkinnyMVC directory\'s skinnymvc.php file.  is_string() test failed.');
                $this->assertTrue(  file_exists($skinnymvc_php_path)  , 'Problem with Test SkinnyMVC directory\'s skinnymvc.php file.  file_exists() test failed.');
                $this->assertTrue(  is_file($skinnymvc_php_path)      , 'Problem with Test SkinnyMVC directory\'s skinnymvc.php file.  is_file() test failed.');

            //
            // End.
            //
                $haveEnded = $skt->end();
                $this->assertTrue(  TRUE === $haveEnded  , 'Problem invoking end() method on SkinnyMvcTest object.');
        }

    //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

        public function testThatSkinnyMvcInstallCreatesSomeStuff()
        {
            //
            // Begin.
            //
                $skt = new SkinnyMvcTest();
                $this->assertTrue(  isset($skt)                    , 'Problem creating new SkinnyMvcTest object.  isset() test failed.');
                $this->assertTrue(  is_object($skt)                , 'Problem creating new SkinnyMvcTest object.  is_object() test failed.');
                $this->assertTrue(  $skt instanceof SkinnyMvcTest  , 'Problem creating new SkinnyMvcTest object.  instanceof test failed.');

                $haveBegun = $skt->begin();
                $this->assertTrue(  TRUE === $haveBegun  , 'Problem invoking begin() method on SkinnyMvcTest object.');

                $haveBegun = $skt->haveBegun();
                $this->assertTrue(  TRUE === $haveBegun  , 'haveBegun() method on SkinnyMvcTest object said we haven\'t begun.');

            //
            // Check Directory Exists.
            //
                $path = $skt->getPath();
                $this->assertTrue(  is_string($path)    , 'Problem getting path to Test SkinnyMVC directory.  is_string() test failed.');
                $this->assertTrue(  file_exists($path)  , 'Problem getting path to Test SkinnyMVC directory.  file_exists() test failed.');
                $this->assertTrue(  is_dir($path)       , 'Problem getting path to Test SkinnyMVC directory.  is_dir() test failed.');

            //
            // Check skinnymvc.php Exists.
            //
                $skinnymvc_php_path = $skt->getPath() . '/skinnymvc.php';
                $this->assertTrue(  is_string($skinnymvc_php_path)    , 'Problem with Test SkinnyMVC directory\'s skinnymvc.php file.  is_string() test failed.');
                $this->assertTrue(  file_exists($skinnymvc_php_path)  , 'Problem with Test SkinnyMVC directory\'s skinnymvc.php file.  file_exists() test failed.');
                $this->assertTrue(  is_file($skinnymvc_php_path)      , 'Problem with Test SkinnyMVC directory\'s skinnymvc.php file.  is_file() test failed.');

            //
            //
            //
                $skt->skinnyMvcInstall();

                $path = $skt->getPath() . '/config';
                $this->assertTrue(  is_string($path)    , 'Problem seeing if "config" directory exists in Test SkinnyMVC directory.  is_string() test failed.');
                $this->assertTrue(  file_exists($path)  , 'Problem seeing if "config" directory exists in Test SkinnyMVC directory.  file_exists() test failed.');
                $this->assertTrue(  is_dir($path)       , 'Problem seeing if "config" directory exists in Test SkinnyMVC directory.  is_dir() test failed.');

                $path = $skt->getPath() . '/lib';
                $this->assertTrue(  is_string($path)    , 'Problem seeing if "lib" directory exists in Test SkinnyMVC directory.  is_string() test failed.');
                $this->assertTrue(  file_exists($path)  , 'Problem seeing if "lib" directory exists in Test SkinnyMVC directory.  file_exists() test failed.');
                $this->assertTrue(  is_dir($path)       , 'Problem seeing if "lib" directory exists in Test SkinnyMVC directory.  is_dir() test failed.');

                $path = $skt->getPath() . '/modules';
                $this->assertTrue(  is_string($path)    , 'Problem seeing if "modules" directory exists in Test SkinnyMVC directory.  is_string() test failed.');
                $this->assertTrue(  file_exists($path)  , 'Problem seeing if "modules" directory exists in Test SkinnyMVC directory.  file_exists() test failed.');
                $this->assertTrue(  is_dir($path)       , 'Problem seeing if "modules" directory exists in Test SkinnyMVC directory.  is_dir() test failed.');

                $path = $skt->getPath() . '/modules/default';
                $this->assertTrue(  is_string($path)    , 'Problem seeing if "modules/default" directory exists in Test SkinnyMVC directory.  is_string() test failed.');
                $this->assertTrue(  file_exists($path)  , 'Problem seeing if "modules/default" directory exists in Test SkinnyMVC directory.  file_exists() test failed.');
                $this->assertTrue(  is_dir($path)       , 'Problem seeing if "modules/default" directory exists in Test SkinnyMVC directory.  is_dir() test failed.');

                $path = $skt->getPath() . '/modules/default/actions';
                $this->assertTrue(  is_string($path)    , 'Problem seeing if "modules/default/actions" directory exists in Test SkinnyMVC directory.  is_string() test failed.');
                $this->assertTrue(  file_exists($path)  , 'Problem seeing if "modules/default/actions" directory exists in Test SkinnyMVC directory.  file_exists() test failed.');
                $this->assertTrue(  is_dir($path)       , 'Problem seeing if "modules/default/actions" directory exists in Test SkinnyMVC directory.  is_dir() test failed.');

                $path = $skt->getPath() . '/modules/default/actions/actions.php';
                $this->assertTrue(  is_string($path)    , 'Problem seeing if "modules/default/actions/actions.php" directory exists in Test SkinnyMVC directory.  is_string() test failed.');
                $this->assertTrue(  file_exists($path)  , 'Problem seeing if "modules/default/actions/actions.php" directory exists in Test SkinnyMVC directory.  file_exists() test failed.');
                $this->assertTrue(  is_file($path)       , 'Problem seeing if "modules/default/actions/actions.php" directory exists in Test SkinnyMVC directory.  is_file() test failed.');

                $path = $skt->getPath() . '/plugins';
                $this->assertTrue(  is_string($path)    , 'Problem seeing if "plugins" directory exists in Test SkinnyMVC directory.  is_string() test failed.');
                $this->assertTrue(  file_exists($path)  , 'Problem seeing if "plugins" directory exists in Test SkinnyMVC directory.  file_exists() test failed.');
                $this->assertTrue(  is_dir($path)       , 'Problem seeing if "plugins" directory exists in Test SkinnyMVC directory.  is_dir() test failed.');

                $path = $skt->getPath() . '/templates';
                $this->assertTrue(  is_string($path)    , 'Problem seeing if "templates" directory exists in Test SkinnyMVC directory.  is_string() test failed.');
                $this->assertTrue(  file_exists($path)  , 'Problem seeing if "templates" directory exists in Test SkinnyMVC directory.  file_exists() test failed.');
                $this->assertTrue(  is_dir($path)       , 'Problem seeing if "templates" directory exists in Test SkinnyMVC directory.  is_dir() test failed.');

                $path = $skt->getPath() . '/tmp';
                $this->assertTrue(  is_string($path)    , 'Problem seeing if "tmp" directory exists in Test SkinnyMVC directory.  is_string() test failed.');
                $this->assertTrue(  file_exists($path)  , 'Problem seeing if "tmp" directory exists in Test SkinnyMVC directory.  file_exists() test failed.');
                $this->assertTrue(  is_dir($path)       , 'Problem seeing if "tmp" directory exists in Test SkinnyMVC directory.  is_dir() test failed.');

                $path = $skt->getPath() . '/web';
                $this->assertTrue(  is_string($path)    , 'Problem seeing if "web" directory exists in Test SkinnyMVC directory.  is_string() test failed.');
                $this->assertTrue(  file_exists($path)  , 'Problem seeing if "web" directory exists in Test SkinnyMVC directory.  file_exists() test failed.');
                $this->assertTrue(  is_dir($path)       , 'Problem seeing if "web" directory exists in Test SkinnyMVC directory.  is_dir() test failed.');



            //
            // End.
            //
                $haveEnded = $skt->end();
                $this->assertTrue(  TRUE === $haveEnded  , 'Problem invoking end() method on SkinnyMvcTest object.');
        }
    //////////////////////////////////////////////////////////////////////////////////////////////////////// M E T H O D S //

}
//////////////////////////////////////////////////////////////////////////////////////////////////////////// C L A S S E S //

