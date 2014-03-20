<?php
/**
 * Created by PhpStorm.
 * User: ssouth
 * Date: 3/14/14
 * Time: 7:20 PM
 */
require_once("gravityforms-external-data-fields-config.php");

class studentData
{
  private $firstName = "";
  private $lastName = "";
  private $studentID = "";
  private $emailAddress = "";

  function __construct($sid)
  {
    $this->studentID = $sid;
    $dbh = null;

    // retrieve student info from database
    try
    {
      $dbh = new PDO(gf_external_data_fields_config::$dsn,
                     gf_external_data_fields_config::$studentDataLogin,
                     gf_external_data_fields_config::$studentDataPassword);

      $query = $dbh->prepare(gf_external_data_fields_config::$studentQuery);
      $query->execute(array($this->studentID));

      if($query)
      {
        $rs = $query->fetch(PDO::FETCH_ASSOC);

        if($rs)
        {
          $this->firstName = $rs[gf_external_data_fields_config::$sqlColumnFirstName];
          $this->lastName = $rs[gf_external_data_fields_config::$sqlColumnLastName];
          $this->emailAddress = $rs[gf_external_data_fields_config::$sqlColumnEmailAddress];
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
      $this->log_error("Last error: ". $err);
    }

    // close database connection
    $dbh = null;
  }

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
   * @param      $msg
   * @param null $errorInfo
   */
  private function log_error($msg, $errorInfo = null)
  {
    error_log("studentData: ".$msg);
    if($errorInfo)
    {
      error_log($errorInfo);
    }
  }
}