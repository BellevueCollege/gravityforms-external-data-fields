<?php
require_once("gravityforms-external-data-fields-config.php");
require_once("gravityforms-external-data-fields-utilities.php");

class employeeData
{
    function __construct($username)
    {
        $this->username = $username;
    }

    public function employeeRecord()
    {
        debug_log("Connecting to '".gf_external_data_fields_config::$dsn."'...");
        try
         {
             //error_log("resultset $this->username :");
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
                    //error_log("resultset $this->username :".print_r($rs,true));
                    if($rs)
                    {
                       //means the user is an employee
                        $employeeSSN =  $rs[gf_external_data_fields_config::$sqlColumnEmployeeSSN];

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
}
?>