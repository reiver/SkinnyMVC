//
// M A I N
//

    //
    // Make sure the proper number of CLI parameters were given.
    //
        if ($argc < 2) {
            _help();
/////////// EXIT
            exit(0);
        }

    //
    //
    //
        $mvc = new MVC();

        if (is_callable(array($mvc, $argv[1])) && $argv[1]!='main') {
            call_user_func_array(array($mvc, $argv[1]), array($argv)); //hack
        } else {
            echo "Invalid argument!\n\n";
            _help();
/////////// EXIT
            exit(0);
        }


    //
    // Exit
    //
/////// EXIT
        exit(0);
