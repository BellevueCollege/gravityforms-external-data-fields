<?php
/**
 * Created by PhpStorm.
 * User: ssouth
 * Date: 3/24/14
 * Time: 4:08 PM
 */

require_once("gravityforms-external-data-fields-config.php");
require_once("gravityforms-external-data-fields-utilities.php");

/**
 * Class requireAuthentication
 *
 * This class currently performs SSO authentication via Jasig CAS, but it is not
 * required to. To use another authentication system replace the CAS code with
 * appropriate code for your system.
 */
//global $gfedf_phpcas_path;
if(isset($gfedf_phpcas_path))
    $GLOBALS["cas_path"] = $gfedf_phpcas_path;
else
    echo("Error : CAS library path is not set.");
class requireAuthentication
{
  const SESSION_USERNAME = "authenticated_username";

  protected $authenticationForced = false;
  protected $shortcode;
  protected $currentUser;
    protected  $cas_path;

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
    /*
     * Check to see if the phpCAS class exsists in our environment.
     * If it doesn't then load the phpCAS library using the $gfedf_phpcas_path
     * configuration variable and configure the phpCAS client settings.
     *
     * NOTE: This assumes that if the phpCAS class does exist in the
     *       environment that the method phpCAS::client() has been already
     *       called by another piece of code elsewhere. If the client method
     *       has not been invoked but the phpCAS class has been imported into
     *       the environment anyway then this logic would cause the
     *       requireAuthentication::ssoAuthenticated() method to fail.
     */
    if (!class_exists('phpCAS')) {
        global $cas_path;
        if(!empty($cas_path))
        {
            require_once $cas_path;
            if (($this->enableDebug) && (ENABLE_DEBUG_LOG)) {
                phpCAS::setDebug(DEBUG_LOG_PATH);
            }
            try
            {
                phpCAS::client(
                    CAS_VERSION_2_0,
                    gf_external_data_fields_config::$ssoServer,
                    gf_external_data_fields_config::$ssoPort,
                    gf_external_data_fields_config::$ssoPath
                );
                phpCAS::setNoCasServerValidation();
            }
            catch (Exception $ex)
            {
                debug_log("CAS client initialization failed. See error log.");
                $this->logError("Failed to initialize CAS!", $ex);
            }
        }
        else
        {
            echo "Error : CAS library path empty";
        }
    }
  }

  /**
   * @return bool
   */
  private function ssoAuthenticated()
  {
    try
    {
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
