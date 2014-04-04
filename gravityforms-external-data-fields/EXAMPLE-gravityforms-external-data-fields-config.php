<?php
// TODO: make a copy of this file w/ the 'EXAMPLE-' prefix removed, then uncomment the following lines & add your settings

/* NOTE: commenting just this line will expose the code
// TODO: the following setting can be removed when CAS has been
$gfedf_phpcas_path = dirname(__FILE__). "/cas/CAS.php";

class gf_external_data_fields_config
{
  const FILE_UPLOAD_PATH = "/uploads/"; // Set this to physical path of the directory
  const FILE_UPLOAD_URL = "http://example.com/uploads/"; // Set this to the url of the directory
  const IS_AUTH = "is_auth"; // Set the parameter name of the authenticated text field from the form. This field is case-sensitive.
  const IS_VERIFIED_MESSAGE = "VERIFIED SUBMISSION - This individual has confirmed their identity by logging in with their NetID. "; // Set this to a message stating the user is authenticated
  const IS_NOT_VERIFIED_MESSAGE = "NOT VERIFIED";// Set this to a message stating the user is not authenticated

 const AUTHENTICATE_ATTRIBUTE = "authenticate"; // This parameter should match the attribute name in the shortcode of gravity form. This is case sensitive
  // See http://docs.php.net/manual/en/ref.pdo-dblib.connection.php
  public static $dsn = "";
  // Database credentials
  // NOTE: Only SQL accounts if connecting to MSSQL (AD accounts do not work)
  public $studentDataLogin = "";
  public $studentDataPassword = "";

  public static $studentQuery = <<<EOS
SELECT statement
EOS;

  // SQL column names
  public static $sqlColumnStudentID = "SID";
  public static $sqlColumnFirstName = "FirstName";
  public static $sqlColumnLastName = "LastName";
  public static $sqlColumnEmailAddress = "Email";
  public static $sqlColumnDaytimePhone = "DaytimePhone";
  public static $sqlColumnEveningPhone = "EveningPhone";

  // SSO settings
  public static $ssoServer = "examplecasserver.com";
  public static $ssoPort = 443;
  public static $ssoPath = "/cas";
  public static $authenticationRequiredMessage = <<<EOH
<p class='warning'>
  You must be logged in to use this form.
</p>
EOH;
}

//*/
