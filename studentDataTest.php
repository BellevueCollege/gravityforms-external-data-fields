<?php
/**
 * Created by PhpStorm.
 * User: ssouth
 * Date: 3/17/14
 * Time: 4:49 PM
 */

require_once("gravityforms-external-data-fields/studentData.php");

class studentDataTest extends PHPUnit_Framework_TestCase
{

  // TODO: write generic unit tests that are not dependent on the source

  /*
   * The data expected by the following tests is specific to Bellevue College.
   */
  public function testGetStudentWithNoEmail()
  {
    $sid = "954999999";
    $data = new studentData($sid);

    $this->assertNotNull($data, "studentData object is null");
    $this->assertNotNull($data->getStudentID(), "StudentID is null");

    $this->assertEquals($sid, $data->getStudentID());
    $this->assertEquals("Student", $data->getFirstName());
    $this->assertEquals("Test", $data->getLastName());
    $this->assertEmpty($data->getEmailAddress());
    $this->assertNotEmpty($data->getDaytimePhone());
    $this->assertEmpty($data->getEveningPhone());
  }

  public function testGetTestStudentWithEmail()
  {
    $sid = "950394601";
    $data = new studentData($sid);

    $this->assertNotNull($data, "studentData object is null");
    $this->assertNotNull($data->getStudentID(), "StudentID is null");

    $this->assertEquals($sid, $data->getStudentID());
    $this->assertEquals("Shawn", $data->getFirstName());
    $this->assertEquals("South", $data->getLastName());
    $this->assertEquals("shawn.south@bellevuecollege.edu", $data->getEmailAddress());
    $this->assertNotEmpty($data->getDaytimePhone());
    $this->assertNotEmpty($data->getEveningPhone());
  }
}
