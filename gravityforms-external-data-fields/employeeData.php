<?php
require_once("gravityforms-external-data-fields-config.php");
require_once("gravityforms-external-data-fields-utilities.php");

class employeeData
{
    private $employeeSSN = "";
    private $employeeSID = "";
    private $firstName = "";
    private $lastName = "";
    private $emailAddress = "";
    private $phone = "";
    private $username = "";

    function __construct($login)
    {
        $this->username = $login;
    }

    public function employeeRecord()
    {
        debug_log("Connecting to '".gf_external_data_fields_config::$dsn."'...");

        try
         {

             if($this->username)
             {
                $dbh = new PDO(gf_external_data_fields_config::$dsn,
                    gf_external_data_fields_config::$studentDataLogin,
                    gf_external_data_fields_config::$studentDataPassword);

                $query = $dbh->prepare(gf_external_data_fields_config::$employeeQuery);

                debug_log("Executing SQL query for username '$this->username'...'\n".gf_external_data_fields_config::$employeeQuery);

                $query->execute(array($this->username));

                if($query)
                {
                    $rs = $query->fetch(PDO::FETCH_ASSOC);

                    if($rs)
                    {
                       //means the user is an employee
                        $this->employeeSSN =  $rs[gf_external_data_fields_config::$sqlColumnEmployeeSSN];
                        $this->employeeSID = $rs[gf_external_data_fields_config::$sqlColumnEmployeeSID];
                        $this->firstName = $rs[gf_external_data_fields_config::$sqlColumnEmployeeFirstName];
                        $this->lastName = $rs[gf_external_data_fields_config::$sqlColumnEmployeeLastName];
                        $this->emailAddress = $rs[gf_external_data_fields_config::$sqlColumnEmployeeEmailAddress];
                        $this->phone = $rs[gf_external_data_fields_config::$sqlColumnEmployeeDaytimePhone];

                        return true;
                    }
                }
             }
             return false;

         }
        catch(PDOException $ex)
        {
            //$err = error_get_last();
            debug_log("An exception occurred while retrieving employee data! See error log.");
            //$this->logError("Failed to retrieve student data: ". $ex->getCode() .": '".$ex->getMessage()."' Trace: ".$ex->getTraceAsString());
            //$this->logError("Connection: ". (($dbh != null) ? $dbh->errorInfo() : "null"));
           // $this->logError("Last PHP error: ". $err);
        }

        // close database connection
        $dbh = null;
        return false;
    }
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
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @return string
     */
    public function getDaytimePhone()
    {
        return $this->phone;
    }
    public function getEmployeeID()
    {
        return $this->employeeSID;
    }
}
?>