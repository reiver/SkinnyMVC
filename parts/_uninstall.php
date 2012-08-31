    function _uninstall()
    {
        echo "This will delete ALL files in the project. Are you sure? [y/N]";
        flush();
        @ob_flush();
        $confirmation  =  trim( fgets( STDIN ) );
        if ( $confirmation !== 'y' ) {
            exit;
        }
        _delete_directory('modules');
        _delete_directory('templates');
        _delete_directory('config');
        _delete_directory('lib');
        _delete_directory('plugins');
        _delete_directory('web');
        _delete_directory('tmp');

        unlink('README');
    }

//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

    function _delete_directory($dir)
    {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir) || is_link($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!_delete_directory($dir . "/" . $item)) {
                chmod($dir . "/" . $item, 0777);
                if (!_delete_directory($dir . "/" . $item)) return false;
            }
        }
        return rmdir($dir);
    }

