    function _create_module($module_name)
    {
            if (!file_exists('modules')) {
                echo "Invalid SkinnyMVC installation.\n";
                exit;
            }

            if(empty($module_name)) {
                echo "Usage: ".$argv[0]." createMod module\n";
                return;
            }

            if(file_exists('modules/'.$module_name)) {
                echo "Module '$module_name' already exists!\n";
                return;
            }

            mkdir('modules/'.$module_name);
            mkdir('modules/'.$module_name.'/actions');
            mkdir('modules/'.$module_name.'/templates');

            _create_module_actions_file($module_name);
            _create_module_template_files($module_name);
    }

//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

    function _create_module_actions_file($module)
    {
        $moduleClassName = $module;
        $moduleClassName = str_replace('_', ' ', $moduleClassName);
        $moduleClassName = ucwords($moduleClassName);
        $moduleClassName = str_replace(' ', '', $moduleClassName);
        $moduleClassName .= 'Actions';

        $s = '<?php
/******************************
 * filename:    modules/'.$module.'/actions/actions.php
 * description:
 */

class '.$moduleClassName.' extends SkinnyActions {

    public function __construct()
    {
    }

  /**
   * The actions index method
   * @param array $request
   * @return array
   */
    public function executeIndex($request)
    {
        // return an array of name value pairs to send data to the template
        $data = array();
        return $data;
    }

}';

        @file_put_contents('modules/'.$module.'/actions/actions.php', $s);
    } 

//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=//

    function _create_module_template_files($module)
    {

        if ($module=='default') {
            $output = ' echo "   You have successfuly installed SkinnyMVC."; ';
        } else {
            $output = " /* Put your code here */ ";
        }

        $s = '    <h1>Under Construction</h1>'."\n"
           . '<?php'."\n"
           . $output."\n"
           ;

        @file_put_contents('modules/'.$module.'/templates/index.php', $s);

        //README for plugins
        $s = 'This directory contains your module-action templates.'."\n"
           . 'A template must be named after the associated action.'."\n"
           . 'For example, if the associated action is "list", then the file name of the template must be "list.php"'."\n"
           ;

        @file_put_contents('modules/'.$module.'/templates/README', $s);

    }
