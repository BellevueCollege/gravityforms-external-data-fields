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
require_once("gravityforms-external-data-fields-utilities.php");
// automatically include class files when encountered
spl_autoload_register('class_autoloader');  // needed by phpCAS

// The following include is CAS-specific. Replace if you move from CAS.
//region CAS client code
if(file_exists($gfedf_phpcas_path))
{
  /** @noinspection PhpIncludeInspection */
  require_once($gfedf_phpcas_path);
}
// The CAS client needs to be initialized before any content is sent to the browser
try
{
  phpCAS::client(CAS_VERSION_2_0,
                 gf_external_data_fields_config::$ssoServer,
                 gf_external_data_fields_config::$ssoPort,
                 gf_external_data_fields_config::$ssoPath);
}
catch (Exception $e)
{
  debug_log("CAS client initialization failed.");
  error_log("gfedf: Failed to initialize CAS! [".$e->getCode()."] ".$e->getMessage()."\n".$e->getTraceAsString());
}
//endregion

add_filter("gform_upload_path", "change_upload_path", 10, 2);
function change_upload_path($path_info, $form_id){

    $path_info["path"] = $GLOBALS["file_upload_path"];
    $path_info["url"] = $GLOBALS["file_upload_url"];
    return $path_info;
}


add_filter("gform_pre_render", "gfedf_enforce_login");
/**
 * @param $form
 *
 * This method is currently CAS-specific. Modify this method if you
 * use another SSO provider. The code is based on the gateway example
 * provided by the project:
 * https://github.com/Jasig/phpCAS/blob/master/docs/examples/example_gateway.php
 */
function gfedf_enforce_login($form)
{
  debug_log("gfedf_enforce_login() invoked.");

  try
  {
    // TODO: only authenticate if form requires it
    if(true)
    {
      debug_log("Logging user in...");
      phpCAS::forceAuthentication();
    }

    debug_log("Checking if authenticated...");
    if(phpCAS::checkAuthentication())
    {
      debug_log("Authentication successful (".phpCAS::getUser().")...");
      $_SESSION[gf_external_data_fields_config::SESSION_USERNAME] = phpCAS::getUser();
      debug_log("...username '".$_SESSION[gf_external_data_fields_config::SESSION_USERNAME]."' saved.");
    }
    else
    {
      // TODO: allow form if authentication is not required
      error_log("User was not authenticated! (phpCAS version ".phpCAS::getVersion().")");
      print('<h1 style="color:red;">You must log in to view this form</h1>');
    }
  }
  catch (Exception $e)
  {
    debug_log("Authentication caused an Exception!");
    error_log("gfedf_enforce_login: Failed to authenticate user: [".$e->getCode()."] ".$e->getMessage()."\n".$e->getTraceAsString());
  }

  return $form;
}