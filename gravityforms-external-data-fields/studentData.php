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

    // temporary test data
//    $this->firstName = "Shawn";
//    $this->lastName = "South";
//    $this->emailAddress = "shawn.south@bellevuecollege.edu";

    // retrieve student info from database
    try
    {
      $dbh = new PDO(gf_external_data_fields_config::$dsn);//,
//                     gf_external_data_fields_config::$studentDataLogin,
//                     gf_external_data_fields_config::$studentDataPassword);

//      $q = $dbh->query(gf_external_data_fields_config::$studentQuery);

      $query = $dbh->prepare(gf_external_data_fields_config::$studentQuery);
      $query->execute();

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
          print("Student record is null!");
          print_r($query->errorInfo());
//          die("Student record is null!");
        }
      }
      else
      {
        print("Query results are null!");
        print_r($query->errorInfo());
//        die("Student query returned no records.");
      }
    }
    catch(PDOException $ex)
    {
      // TODO: log error message
      print_r($ex->getMessage());
//      die($ex->getMessage());
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

}