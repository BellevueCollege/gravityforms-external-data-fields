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

    // retrieve student info from database
    try
    {
      $dbh = new PDO(gf_external_data_fields_config::$dsn, gf_external_data_fields_config::$studentDataLogin, gf_external_data_fields_config::$studentDataPassword);

      $rs = $dbh->query(gf_external_data_fields_config::$studentQuery);
      // TODO: use fetch() - http://docs.php.net/manual/en/pdostatement.fetch.php

      if(($rs) && ($rs->rowCount() > 0))
      {
        if($rs->rowCount() > 1)
        {
          // TODO: warn that query returned too many records
        }
      }
      else
      {
        die("Student query returned no records.");
      }
    }
    catch(PDOException $ex)
    {
      // TODO: log error message
      die($ex->getMessage());
    }

    // temporary test data
//    $this->firstName = "Shawn";
//    $this->lastName = "South";
//    $this->emailAddress = "shawn.south@bellevuecollege.edu";
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

}