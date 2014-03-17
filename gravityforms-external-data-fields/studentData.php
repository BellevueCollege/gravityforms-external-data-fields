<?php
/**
 * Created by PhpStorm.
 * User: ssouth
 * Date: 3/14/14
 * Time: 7:20 PM
 */

class studentData
{
  private $firstName = "";
  private $lastName = "";
  private $studentID = "";
  private $emailAddress = "";

  function __construct($sid)
  {
    $this->studentID = $sid;

    // TODO: retrieve student info from database

    // temporary test data
    $this->firstName = "Shawn";
    $this->lastName = "South";
    $this->emailAddress = "shawn.south@bellevuecollege.edu";
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