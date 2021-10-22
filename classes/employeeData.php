<?php
require_once( "dataApi.php" );
require_once( dirname( plugin_dir_path( __FILE__ ) ) . "/config.php" );
require_once( "utilities.php" );

class EmployeeData {
	//private $employeeSSN   = "";
	private $sid;
	private $first_name;
	private $last_name;
	private $alias_name;
	private $email;
	private $phone;
	private $username;
	private $has_student_record;

	function __construct( $login ) {
		$this->username = $login;
		$this->has_student_record = false;
		$this->sid = null;
		$this->first_name = null;
		$this->last_name = null;
		$this->alias_name = null;
		$this->email = null;
		$this->phone = null;

		try {
			if ( isset( $this->username ) ) {
				debug_log( "GF External Data Fields plugin :: Query Data API for user '" . $this->username . "'..." );

				$emp_info = DataApi::get_employee( $this->username );
				//echo $emp_info;
				if ( isset( $emp_info ) ) {

					$this->sid = $emp_info['EMPLID'];
					$this->first_name = $emp_info['firstName'];
					$this->last_name = $emp_info['lastName'];
					$this->alias_name = $emp_info['aliasName'];
					$this->email = $emp_info['email'];
					$this->phone = $emp_info['phone'];

					debug_log( "GF External Data Fields plugin :: Successfully retrieved employee info: $this->sid, $this->first_name $this->last_name, $this->email" );
				}
				else {
					debug_log( sprintf( "Username %s does not have an employee record.", $this->username ) );
				}
			}
        } catch ( Exception $ex ) {
            error_log( "GF External Data Fields plugin :: Failed to retrieve employee data: " . $ex->getMessage() );
        }
	}

	// if SID is set, we determine that the username has an employee record
	public function is_employee() {
		if ( isset( $this->sid ) ) {
			return true;
		} else {
			return false;
		}
	}

	// get functions for class members
	public function get_first_name() {
		return $this->first_name;
	}

	public function get_last_name() {
		return $this->last_name;
	}

	public function get_alias_name() {
		return $this->alias_name;
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