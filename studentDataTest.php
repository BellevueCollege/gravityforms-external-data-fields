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
  public function testInitializeWithSid()
  {
    $sid = "954999999";
    $data = new studentData($sid);

    $this->assertNotNull($data, "studentData object is null");
    $this->assertNotNull($data->getStudentID(), "StudentID is null");
    $this->assertEquals($sid, $data->getStudentID());
  }
}
