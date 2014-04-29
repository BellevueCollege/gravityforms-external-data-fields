<?php
/**
 * Created by PhpStorm.
 * User: ssouth
 * Date: 3/24/14
 * Time: 4:08 PM
 */
require_once("gravityforms-external-data-fields-config.php");
require_once("gravityforms-external-data-fields-utilities.php");
//region CAS client code
// The following include is CAS-specific. Replace if you move from CAS.
// automatically include class files when encountered
/*
 * In order to just include one CAS library, we first look for CAS lib set for Wordpress-CAS-Client plugin.
 * If that lib is not set, than it will fetch the path from the config file.
*/

$wpcasldap_include_path = get_site_option('wpcasldap_include_path');
if(!empty($wpcasldap_include_path))
    include_once $wpcasldap_include_path;
else
{
    if(file_exists($gfedf_phpcas_path))
    {
        // @noinspection PhpIncludeInspection
        require_once($gfedf_phpcas_path);
    }
}
//spl_autoload_register('class_autoloader');  // needed by phpCAS

/*
if(file_exists($gfedf_phpcas_path))
{
  // @noinspection PhpIncludeInspection
  require_once($gfedf_phpcas_path);
}
*/
//endregion

/**
 * Class requireAuthentication
 *
 * This class currently performs SSO authentication via Jasig CAS, but it is not
 * required to. To use another authentication system replace the CAS code with
 * appropriate code for your system.
 */
class requireAuthentication
{
  const SESSION_USERNAME = "authenticated_username";

  protected $authenticationForced = false;
  protected $shortcode;
  protected $currentUser;

  public $enableDebug = false;

  /**
   * @param      $post_shortcode
   * @param bool $enableDebug
   */
  function __construct($post_shortcode, $enableDebug = false)
  {
    $this->shortcode = $post_shortcode;
    $this->enableDebug = $enableDebug;

    // This check needs to run early enough in the WordPress page flow that a redirect (to the login page)
    // can occur before any content is written to the HTTP Response.
    if(function_exists("add_action"))
    {
        add_action( 'wp', array($this, "forceAuthentication"), 1 );
                $this->ssoInitialize();
    }
  }

  /**
   *
   */
  function forceAuthentication()
  {
    // reset flag
    $this->authenticationForced = false;

    if ($this->hasShortcode())
    {
      debug_log("Checking if authenticated...");
      if ($this->ssoAuthenticated())
      {
        $_SESSION[requireAuthentication::SESSION_USERNAME] = $this->currentUser;
        debug_log("...username '" . requireAuthentication::getCurrentUser() . "' saved.");
      }
      else
      {
        add_filter( 'the_content', array($this, 'displayAuthenticationRequired' ));
      }
    }
    else
    {

    }

  }

  /**
   * @param $content
   *
   * @return string
   */
  function displayAuthenticationRequired($content)
  {
    if(isset(gf_external_data_fields_config::$authenticationRequiredMessage))
    {
      debug_log("authenticationRequiredMessage is set...");
      $content = gf_external_data_fields_config::$authenticationRequiredMessage;
    }
    else
    {
      debug_log("authenticationRequiredMessage is NOT set. Using default message...");
      $content = "<strong>You must be logged in to use this form.</strong>";
    }
    debug_log("content = '$content'");
    return $content;
  }

  /**
   * @param $post_shortcode
   *
   * @return \requireAuthentication
   */
  static function setup($post_shortcode)
  {
    return new requireAuthentication($post_shortcode);
  }

  /**
   * @return mixed
   */
  static function getCurrentUser()
  {
    return $_SESSION[requireAuthentication::SESSION_USERNAME];
  }

  /**
   * @return bool
   */
  static function isAuthenticated()
  {

    return (!(is_null($_SESSION[requireAuthentication::SESSION_USERNAME])) &&
            ($_SESSION[requireAuthentication::SESSION_USERNAME] != ""));
  }


  /**
   * @internal param $pattern
   * @internal param $post
   *
   * @return bool
   */
  private function hasShortcode()
  {
    // get a reference to the WordPress post object
    global $post;
    // get standard regex pattern for shortcodes
    $pattern = get_shortcode_regex();
    $matches = array();
    // if we don't already have an array of shortcodes create one
    $codes = is_array($this->shortcode) ? $this->shortcode : array($this->shortcode);
    foreach($codes as $code)
    {
      $hasShortcode = preg_match_all('/' . $pattern . '/s', $post->post_content, $matches)
                      && array_key_exists(2, $matches)
                      && in_array($code, $matches[2]);
      if($hasShortcode)
      {

          if(!empty($matches))
          {
              $attributes = $matches[3];

              if(defined('gf_external_data_fields_config::AUTHENTICATE_ATTRIBUTE') && !empty($attributes[0]))
              {
                  $auth_attr = gf_external_data_fields_config::AUTHENTICATE_ATTRIBUTE;
                  $regex = "/$auth_attr\s*=\s*\"\s*(\S*)\s*\"/i";

                  $hasAuthParam = preg_match($regex, $attributes[0], $matches);

                  if( isset($matches[1])  &&  strtolower($matches[1]) == 'true')
                  {
                      //Force authentication
                      debug_log("Force Authentication is true");
                      return true;
                  }
                  else
                  {
                      unset($_SESSION[requireAuthentication::SESSION_USERNAME]);
                  }

              }
          }
      }
    }
    // no shortcode was found
    return false;
  }

  /**
   *
   */
  private function ssoInitialize()
  {
    if (($this->enableDebug) && (ENABLE_DEBUG_LOG))
    {
      phpCAS::setDebug(DEBUG_LOG_PATH);
    }

    try
    {
      //if(!class_exists('phpCAS') && !isset(phpCAS::$_PHPCAS_CLIENT))
        if(!isset($_SESSION["CAS_INI"]))
        {
            debug_log("CAS client initialization success. See error log.");
            /*
             *  Trying to get the CAS settings from Wordpress CAS CLient settings page.
             * If they are not available it will get the settings from config file.
             */
            $wp_hostname = get_site_option('wpcasldap_server_hostname');
            $wp_port = get_site_option('wpcasldap_server_port');
            $wp_path = get_site_option('wpcasldap_server_path');
            $sso_server = !empty($wp_hostname) ? $wp_hostname : gf_external_data_fields_config::$ssoServer;
            $sso_port = !empty($wp_port) ? $wp_port  : gf_external_data_fields_config::$ssoPort;
            $sso_path = !empty($wp_path) ? $wp_path  : gf_external_data_fields_config::$ssoPath;
            phpCAS::client(CAS_VERSION_2_0,
                $sso_server,
                intval($sso_port),
                $sso_path);
            $_SESSION["CAS_INI"] = true;
            phpCAS::setNoCasServerValidation();

           /* phpCAS::client(CAS_VERSION_2_0,
                         gf_external_data_fields_config::$ssoServer,
                         gf_external_data_fields_config::$ssoPort,
                         gf_external_data_fields_config::$ssoPath); */
        }

    }
    catch (Exception $ex)
    {
      debug_log("CAS client initialization failed. See error log.");
      $this->logError("Failed to initialize CAS!", $ex);
    }
  }

  /**
   * @return bool
   */
  private function ssoAuthenticated()
  {
    try
    {
      // TODO: call phpCAS::setCasServerCACert()
      //phpCAS::setNoCasServerValidation();

      debug_log("phpCAS::checkAuthentication()...");
      if (phpCAS::checkAuthentication())
      {
        debug_log("Authentication successful (" . phpCAS::getUser() . ")...");
        $this->currentUser = phpCAS::getUser();
        return true;
      }
      else
      {
        debug_log("... failed...");
        if (!($this->authenticationForced))
        {
          debug_log("Redirecting to login server...");
          // redirect user to login page
          phpCAS::forceAuthentication();
          // set flag so we don't get caught in an endless loop
          $this->authenticationForced = true;
          // ...and check again.
          debug_log("Recursively calling to verify user has authenticated...");
          return $this->ssoAuthenticated();
        }
        else
        {
          debug_log("Second attempt at authentication failed. See error log.");
          error_log("Forcing authentication failed. Unable to log user in.");
        }
      }
    }
    catch (Exception $ex)
    {
      debug_log("Unknown problem authenticating user. See error log.");
      $this->logError("Encountered a problem trying to authenticate the user!", $ex);
    }

    debug_log("ssoAuthenticated() returning FALSE");
    return false;
  }

  public function logout()
  {
      $this->ssoInitialize();
      if(defined("gf_external_data_fields_config::AFTER_LOGOUT_URL"))
          phpCAS::logoutWithRedirectService(gf_external_data_fields_config::AFTER_LOGOUT_URL) ;
      else
          phpCAS::logout();
  }

  /**
   * @param           $message
   * @param Exception $ex
   */
  private function logError($message, Exception $ex)
  {
    error_log("gfedf: ".$message." [" . $ex->getCode() . "] " . $ex->getMessage() . "\n" . $ex->getTraceAsString());
  }


}