
<?php
require_once("gravityforms-external-data-fields-config.php");
spl_autoload_register('class_autoloader');  // needed by phpCAS

if(file_exists($gfedf_phpcas_path))
{
/** @noinspection PhpIncludeInspection */
require_once($gfedf_phpcas_path);

    phpCAS::client(CAS_VERSION_2_0,
        gf_external_data_fields_config::$ssoServer,
        gf_external_data_fields_config::$ssoPort,
        gf_external_data_fields_config::$ssoPath);
    if(defined("gf_external_data_fields_config::AFTER_LOGOUT_URL"))
        phpCAS::logoutWithRedirectService(gf_external_data_fields_config::AFTER_LOGOUT_URL) ;
    else
        phpCAS::logout();

}
//Added this function again instead of including from Utilities file because this file is currently called outside of the plugin. Some things in the utilities fail if included in this file.
function class_autoloader($class)
{
    $classFile = dirname(__FILE__) . "/" . $class . ".php";

    if (file_exists($classFile))
    {
        include_once($classFile);
    }
    else
    {
        $error = "Failed to load '".$classFile."'";
        //debug_log($error);
        error_log($error);
    }
}
?>