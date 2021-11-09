<?php
/**
**/
require_once("dataApi.php");
require_once( dirname( plugin_dir_path( __FILE__ ) ) . "/config.php" );
require_once("utilities.php");

class StudentData {
    const UNSPECIFIED_DOMAIN = "UNSPECIFIED";

    private $has_student_record;
    private $first_name = "";
    private $last_name = "";
    private $student_id = "";
    private $email = "";
    private $phone_day = "";
    private $phone_evening = "";
    private $username = "";
    private $domain = self::UNSPECIFIED_DOMAIN;

    function __construct ( $login = null ) {
        if ( is_null($login) ) {
            // parameterless constructor exists to be able to declare a global variable of $self that can be referenced later
            return null;
        }

        //$this->username = $this->extract_username( $login );
        $this->username = $login;
        $this->has_student_record = false;

        // retrieve student info from database
        try {
            debug_log( "Query Data API for user '" . $this->username . "'..." );

            $stu_info = DataApi::get_student($this->username);
            if ( isset($stu_info) ) {
                $this->has_student_record = true;

                $this->student_id = $stu_info['EMPLID'];
                $this->first_name = $stu_info['firstName'];
                $this->last_name = $stu_info['lastName'];
                $this->email = $stu_info['email'];
                $this->phone_daytime = $stu_info['phoneDaytime'];
                $this->phone_evening = $stu_info['phoneEvening'];

                debug_log( "Successfully retrieved student info: $this->student_id, $this->first_name $this->last_name, $this->email" );
            }
            else {
                debug_log( sprintf("Username %s does not have a student record.", $login) );
            }
        } catch ( Exception $ex ) {
            debug_log( "GF External Data Fields plugin :: An exception occurred while retrieving student data! See error log." );
            error_log( "GF External Data Fields plugin :: Failed to retrieve student data: " . $ex->getMessage() );
        }
    }

    /**
    * @return bool
    */
    public function has_student_record() {
        return $this->has_student_record;
    }

    /**
    * @param $login
    * @return bool
    */
    public static function is_student( $login ) {
        $sd = new StudentData( $login );
        return $sd->has_student_record();
    }

    /**
    * @return string
    */
    public function get_first_name() {
        return $this->first_name;
    }

    /**
    * @return string
    */
    public function get_last_name() {
        return $this->last_name;
    }

    /**
    * @return string
    */
    public function get_student_id() {
        return $this->student_id;
    }

    /**
    * @return string
    */
    public function get_email() {
        return $this->email;
    }

    /**
    * @return string
    */
    public function get_daytime_phone() {
        return $this->phone_daytime;
    }

    /**
    * @return string
    */
    public function get_evening_phone() {
        return $this->phone_evening;
    }

    /**
    * @return string
    */
    public function get_username() {
        return $this->username;
    }

    /**
    * @return string
    */
    public function get_login_domain() {
        return $this->domain;
    }

    /**
    * @param $login
    * @return mixed
    */
    private function extract_username ( $login ) {
        if ( strpos ( $login, '\\' ) ) {
            $credential = explode ( '\\', $login );
            // we're not currently using the domain, but save it in case we want it later
            $this->domain = $credential[0];

            return $credential[1];
        }
        else {
            return $login;
        }
    }
}