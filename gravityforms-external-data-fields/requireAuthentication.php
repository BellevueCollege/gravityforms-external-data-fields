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
      debug_log("Checking if authenticated...");
      if ($this->ssoAuthenticated())
      {
        $_SESSION[gf_external_data_fields_config::SESSION_USERNAME] = $this->currentUser;
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
    // get a reference to the WordPress post object
    global $post;
    // get standard regex pattern for shortcodes
    $pattern = get_shortcode_regex();

    // if we don't already have an array of shortcodes create one
    $codes = is_array($this->shortcode) ? $this->shortcode : array($this->shortcode);

    foreach($codes as $code)
    {
      $hasShortcode = preg_match_all('/' . $pattern . '/s', $post->post_content, $matches)
                      && array_key_exists(2, $matches)
                      && in_array($code, $matches[2]);
      if($hasShortcode)
      {
        debug_log("A Gravity Form was detected!");
        // as soon as we find one, exit the loop and notify caller
        return true;
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
    if ($this->enableDebug)
    {
      // TODO: make log file configurable
      phpCAS::setDebug("/var/tmp/gravityforms-external-data-fields-debug.log");
    }

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
        $this->currentUser = phpCAS::getUser();
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