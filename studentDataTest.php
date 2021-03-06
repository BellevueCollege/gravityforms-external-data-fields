<?php
/**
 * Created by PhpStorm.
 * User: ssouth
 * Date: 3/17/14
 * Time: 4:49 PM
 */

require_once("gravityforms-external-data-fields/studentData.php");

// This line stops PHP timezone warnings/exceptions that interfere with PHPUnit reporting
date_default_timezone_set('America/Los_Angeles');

class studentDataTest extends PHPUnit_Framework_TestCase
{

  // TODO: write generic unit tests that are not dependent on the source

  /*
   * The data expected by the following tests is specific to Bellevue College.
   */
  public function testGetStudentWithNoEmail()
  {
    $username = "_teststu01";
    $data = new studentData($username);

    $this->assertNotNull($data, "studentData object is null");

    $this->assertStudentData($data, $username, "954999991", "Student", "Test");

    $this->assertEmpty($data->getEmailAddress());
    $this->assertNotEmpty($data->getDaytimePhone());
    $this->assertEmpty($data->getEveningPhone());
  }

  public function testGetRealStudentWithEmail()
  {
    $username = "ssouth";
    $data = new studentData($username);

    $this->assertNotNull($data, "studentData object is null");

    $this->assertStudentData($data, $username, "950394601", "Shawn", "South");
    $this->assertEquals(studentData::UNSPECIFIED_DOMAIN, $data->getLoginDomain());

    $this->assertEquals("shawn.south@bellevuecollege.edu", $data->getEmailAddress());
    $this->assertNotEmpty($data->getDaytimePhone());
    $this->assertNotEmpty($data->getEveningPhone());
  }

  public function testGetTestStudent_DomainAndUsername()
  {
    $username = "_teststu01";
    $domain = "DOMAIN";
    $data = new studentData($domain."\\".$username);

    $this->assertNotNull($data, "studentData object is null");

    $this->assertStudentData($data, $username, "954999991", "Student", "Test");
    $this->assertEquals($domain, $data->getLoginDomain());

    $this->assertEmpty($data->getEmailAddress());
    $this->assertNotEmpty($data->getDaytimePhone());
    $this->assertEmpty($data->getEveningPhone());
  }

  public function testIsStudent_Static_True()
  {
    $isStudent = studentData::IsStudent("_teststu01");
    $this->assertTrue($isStudent);
  }

  public function testIsStudent_Instance_True()
  {
    $data = new studentData("_teststu01");
    $this->assertTrue($data->isAStudent());
  }

  public function testIsStudent_Static_False()
  {
    $isStudent = studentData::IsStudent("tripti.sharma");
    $this->assertFalse($isStudent);
  }

  public function testIsStudent_Instance_False()
  {
    $data = new studentData("tripti.sharma");
    $this->assertFalse($data->isAStudent());
  }

  /**
   * @param \studentData $data
   * @param              $username
   * @param              $sid
   * @param              $firstName
   * @param              $lastName
   */
  private function assertStudentData(studentData $data, $username, $sid, $firstName, $lastName)
  {
    $this->assertEquals($username, $data->getUsername());
    $this->assertEquals($sid, $data->getStudentID());
    $this->assertEquals($firstName, $data->getFirstName());
    $this->assertEquals($lastName, $data->getLastName());
  }
}
