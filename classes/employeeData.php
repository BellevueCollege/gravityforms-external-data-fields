<?php
require_once( "dataApi.php" );
require_once( dirname( plugin_dir_path( __FILE__ ) ) . "/config.php" );
require_once( "utilities.php" );

class EmployeeData {
	//private $employeeSSN   = "";
	private $sid = "";
	private $first_name = "";
	private $last_name = "";
	private $alias_name = "";
	private $email = "";
	private $phone = "";
	private $username = "";

	function __construct ( $login ) {
		$this->username = $login;
	}

	public function employee_record() {
		
        //$this->username = $this->extract_username( $login );
		//$this->username = $login;
        $this->has_student_record = false;

        // retrieve employee info
        try {
			if ( isset( $this->username ) ) {
				debug_log( "GF External Data Fields plugin :: Query Data API for user '" . $this->username . "'..." );

				$emp_info = DataApi::get_employee($this->username);
				//echo $emp_info;
				if ( isset($emp_info) ) {

					$this->sid = $emp_info['SID'];
					$this->first_name = $emp_info['firstName'];
					$this->last_name = $emp_info['lastName'];
					$this->alias_name = $emp_info['aliasName'];
					$this->email = $emp_info['email'];
					$this->phone = $emp_info['phone'];

					debug_log( "GF External Data Fields plugin :: Successfully retrieved employee info: $this->sid, $this->first_name $this->last_name, $this->email" );

					return true;
				}
				else {
					debug_log( sprintf("Username %s does not have an employee record.", $this->username) );
				}
			}
        } catch ( Exception $ex ) {
            error_log( "GF External Data Fields plugin :: Failed to retrieve employee data: " . $ex->getMessage() );
        }
		return false;
	}

	public function get_first_name() {
		return $this->first_name;
	}

	public function get_last_name() {
		return $this->last_name;
	}

	public function get_email() {
		return $this->email;
	}

	public function get_phone() {
		return $this->phone;
	}

	public function get_sid() {
		return $this->sid;
	}
}