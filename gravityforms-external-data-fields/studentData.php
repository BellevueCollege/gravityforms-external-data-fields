<?php
/**
 * Created by PhpStorm.
 * User: shawn.south@bellevuecollege.edu
 * Date: 3/14/14
 * Time: 7:20 PM
 */
require_once("gravityforms-external-data-fields-config.php");

class studentData
{
  const UNSPECIFIED_DOMAIN = "UNSPECIFIED";

  private $firstName = "";
  private $lastName = "";
  private $studentID = "";
  private $emailAddress = "";
  private $phoneDaytime = "";
  private $phoneEvening = "";
  private $username = "";
  private $domain = studentData::UNSPECIFIED_DOMAIN;

  function __construct($login)
  {
    $this->username = $this->extractUsername($login);
    $dbh = null;

    // retrieve student info from database
    try
    {
      $dbh = new PDO(gf_external_data_fields_config::$dsn,
                     gf_external_data_fields_config::$studentDataLogin,
                     gf_external_data_fields_config::$studentDataPassword);

      $query = $dbh->prepare(gf_external_data_fields_config::$studentQuery);
      $query->execute(array($this->username));

      if($query)
      {
        $rs = $query->fetch(PDO::FETCH_ASSOC);

        if($rs)
        {
          $this->studentID = $rs[gf_external_data_fields_config::$sqlColumnStudentID];
          $this->firstName = $rs[gf_external_data_fields_config::$sqlColumnFirstName];
          $this->lastName = $rs[gf_external_data_fields_config::$sqlColumnLastName];
          $this->emailAddress = $rs[gf_external_data_fields_config::$sqlColumnEmailAddress];
          $this->phoneDaytime = $rs[gf_external_data_fields_config::$sqlColumnDaytimePhone];
          $this->phoneEvening = $rs[gf_external_data_fields_config::$sqlColumnEveningPhone];
        }
        else
        {
          $this->log_error("Student record is null!", $query->errorInfo());
        }
      }
      else
      {
        $this->log_error("Student data query results are null!", $query->errorInfo());
      }
    }
    catch(PDOException $ex)
    {
      $err = error_get_last();
      $this->log_error("Failed to retrieve student data: ". $ex->getCode() .": '".$ex->getMessage()."' Trace: ".$ex->getTraceAsString());
      $this->log_error("Connection: ". (($dbh != null) ? $dbh->errorInfo() : "null"));
      $this->log_error("Last PHP error: ". $err);
    }

    // close database connection
    $dbh = null;
  }

  //region Properties
  /**
   * @return string
   */
  public function getFirstName()
  {
    return $this->firstName;
  }

  /**
   * @return string
   */
  public function getLastName()
  {
    return $this->lastName;
  }

  /**
   * @return string
   */
  public function getStudentID()
  {
    return $this->studentID;
  }

  /**
   * @return string
   */
  public function getEmailAddress()
  {
    return $this->emailAddress;
  }

  /**
   * @return string
   */
  public function getDaytimePhone()
  {
    return $this->phoneDaytime;
  }

  /**
   * @return string
   */
  public function getEveningPhone()
  {
    return $this->phoneEvening;
  }

  /**
   * @return string
   */
  public function getUsername()
  {
    return $this->username;
  }

  /**
   * @return string
   */
  public function getLoginDomain()
  {
    return $this->domain;
  }
  //endregion

  //region Private methods
  /**
   * @param            $msg
   * @param null $errorInfo
   */
  private function log_error($msg, $errorInfo = null)
  {
    error_log("studentData: ".$msg);
    if($errorInfo)
    {
      error_log(print_r($errorInfo, true));
    }
  }

  /**
   * @param string
   */
  private function extractUsername($login)
  {
    // TODO: parse domain name out if it exists
    if(strpos($login, '\\'))
    {
      $credential = explode('\\', $login);
      $this->domain = $credential[0];
      return $credential[1];
    }
    else
    {
      return $login;
    }
  }
  //endregion
}