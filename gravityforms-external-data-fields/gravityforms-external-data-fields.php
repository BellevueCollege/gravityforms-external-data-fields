<?php
/*
Plugin Name: Gravity Forms External Data Fields
Plugin URI: http://www.bellevuecollege.edu
Description: Extend Gravity Forms with Bellevue College form field data
Author: Bellevue College Technology Development and Communications
Version: 0.1
Author URI: http://www.bellevuecollege.edu
*/
require_once("gravityforms-external-data-fields-config.php");

add_filter("gform_upload_path", "change_upload_path", 10, 2);
function change_upload_path($path_info, $form_id){

    $path_info["path"] = $GLOBALS["file_upload_path"];
    $path_info["url"] = $GLOBALS["file_upload_url"];
    return $path_info;
}

require_once("requireAuthentication.php");
requireAuthentication::setup(array("gravityform","gravityforms"));
