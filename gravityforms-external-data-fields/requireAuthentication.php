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
spl_autoload_register('class_autoloader');  // needed by phpCAS

if(file_exists($gfedf_phpcas_path))
{
  /** @noinspection PhpIncludeInspection */
  require_once($gfedf_phpcas_path);
}
//endregion

/**
 * Class requireAuthentication
 */
class requireAuthentication
{
  protected $authenticationForced = false;
  protected $shortcode;
  protected $current_user;

  /**
   * @param $post_shortcode
   */
  function __construct($post_shortcode)
  {
    $this->shortcode = $post_shortcode;
    add_action( 'wp', array($this, "forceAuthentication") );

    $this->ssoInitialize();
  }

  /**
   * @param $wp
   */
  function forceAuthentication($wp)
  {
    // reset flag
    $this->authenticationForced = false;

    if ($this->hasShortcode())
    {
      debug_log("A Gravity Form was detected!");

      debug_log("Checking if authenticated...");
      if ($this->ssoAuthenticated())
      {
        $_SESSION[gf_external_data_fields_config::SESSION_USERNAME] = $this->current_user;
        debug_log("...username '" . requireAuthentication::getCurrentUser() . "' saved.");
      }
    }
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
    return $_SESSION[gf_external_data_fields_config::SESSION_USERNAME];
  }

  /**
   * @return bool
   */
  static function isAuthenticated()
  {

    return (!(is_null($_SESSION[gf_external_data_fields_config::SESSION_USERNAME])) &&
            ($_SESSION[gf_external_data_fields_config::SESSION_USERNAME] != ""));
  }

  /**
   * @internal param $pattern
   * @internal param $post
   *
   * @return bool
   */
  private function hasShortcode()
  {
    global $post;
    $pattern = get_shortcode_regex();

    // TODO: support more than one shortcode
    $hasShortcode = preg_match_all('/' . $pattern . '/s', $post->post_content, $matches)
                    && array_key_exists(2, $matches)
                    && in_array($this->shortcode, $matches[2]);
    return $hasShortcode;
  }

  /**
   *
   */
  private function ssoInitialize()
  {
    // TODO: make log file configurable
    phpCAS::setDebug("/var/tmp/gravityforms-external-data-fields-debug.log");

    try
    {
      phpCAS::client(CAS_VERSION_2_0,
                     gf_external_data_fields_config::$ssoServer,
                     gf_external_data_fields_config::$ssoPort,
                     gf_external_data_fields_config::$ssoPath);
    }
    catch (Exception $e)
    {
      debug_log("CAS client initialization failed. See error log.");
      error_log("gfedf: Failed to initialize CAS! [" . $e->getCode() . "] " . $e->getMessage() . "\n" . $e->getTraceAsString());
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
      phpCAS::setNoCasServerValidation();

      debug_log("phpCAS::checkAuthentication()...");
      if (phpCAS::checkAuthentication())
      {
        debug_log("Authentication successful (" . phpCAS::getUser() . ")...");
        $this->current_user = phpCAS::getUser();
        return true;
      }
      else
      {
        if (!($this->authenticationForced))
        {
          debug_log("Not already authenticated. Forcing login...");
          // redirect user to login page
          phpCAS::forceAuthentication();
          // set flag so we don't get caught in an endless loop
          $this->authenticationForced = true;
          // ...and check again.
          return $this->ssoAuthenticated();
        }
        else
        {
          debug_log("Second attempt at authentication failed. See error log.");
          error_log("Forcing authentication failed. Unable to log user in.");

          // TODO: what is the user experience if they can't/don't log in?
        }
      }
    }
    catch (Exception $e)
    {
      debug_log("Unknown problem authenticating user. See error log.");
      error_log("gfedf: Encountered a problem trying to authenticate the user! [".$e->getCode()."] ".$e->getMessage()."\n".$e->getTraceAsString());
    }

    debug_log("ssoAuthenticated() returning FALSE");
    return false;
  }
}