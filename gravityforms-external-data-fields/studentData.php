<?php
    /**
     * Created by PhpStorm.
     * User: shawn.south@bellevuecollege.edu
     * Date: 3/14/14
     * Time: 7:20 PM
     */
    require_once ( "gravityforms-external-data-fields-config.php" );
    require_once ( "gravityforms-external-data-fields-utilities.php" );

    class studentData {
        const UNSPECIFIED_DOMAIN = "UNSPECIFIED";

        private $hasStudentRecord;
        private $firstName    = "";
        private $lastName     = "";
        private $studentID    = "";
        private $emailAddress = "";
        private $phoneDaytime = "";
        private $phoneEvening = "";
        private $username     = "";
        private $domain = studentData::UNSPECIFIED_DOMAIN;

        function __construct ( $login = null ) {
            if ( is_null ( $login ) ) {
                // parameterless constructor exists to be able to declare a global variable of $self that can be referenced later
                return null;
            }

            $this->username = $this->extractUsername ( $login );
            $dbh = null;
            $this->hasStudentRecord = false;

            // retrieve student info from database
            try {
                debug_log ( "Connecting to '" . gf_external_data_fields_config::$dsn . "'..." );
                $dbh = new PDO( gf_external_data_fields_config::$dsn, gf_external_data_fields_config::$studentDataLogin, gf_external_data_fields_config::$studentDataPassword );

                $query = $dbh->prepare ( gf_external_data_fields_config::$studentQuery );
                debug_log ( "Executing SQL query for username '$this->username'...'\n" . gf_external_data_fields_config::$studentQuery );
                $query->execute ( array( $this->username ) );

                if ( $query ) {
                    $rs = $query->fetch ( PDO::FETCH_ASSOC );

                    if ( $rs ) {
                        $this->hasStudentRecord = true;

                        $this->studentID = $rs[ gf_external_data_fields_config::$sqlColumnStudentID ];
                        $this->firstName = $rs[ gf_external_data_fields_config::$sqlColumnFirstName ];
                        $this->lastName = $rs[ gf_external_data_fields_config::$sqlColumnLastName ];
                        $this->emailAddress = $rs[ gf_external_data_fields_config::$sqlColumnEmailAddress ];
                        $this->phoneDaytime = $rs[ gf_external_data_fields_config::$sqlColumnDaytimePhone ];
                        $this->phoneEvening = $rs[ gf_external_data_fields_config::$sqlColumnEveningPhone ];

                        debug_log ( "Successfully retrieved student info: $this->studentID, $this->firstName $this->lastName, $this->emailAddress" );
                    }
                    else {
                        $this->logError ( "Student record is null!", $query->errorInfo () );
                    }
                }
                else {
                    $this->logError ( "Student data query results are null!", $query->errorInfo () );
                }
            } catch ( PDOException $ex ) {
                $err = error_get_last ();
                debug_log ( "An exception occurred while retrieving student data! See error log." );
                $this->logError ( "Failed to retrieve student data: " . $ex->getCode () . ": '" . $ex->getMessage () . "' Trace: " . $ex->getTraceAsString () );
                $this->logError ( "Connection: " . ( ( $dbh != null ) ? $dbh->errorInfo () : "null" ) );
                $this->logError ( "Last PHP error: " . $err );
            }

            // close database connection
            $dbh = null;
        }

        /**
         * @return bool
         */
        public function isAStudent () {
            return $this->hasStudentRecord;
        }

        /**
         * @param $login
         *
         * @return bool
         */
        public static function IsStudent ( $login ) {
            $sd = new studentData( $login );
            return $sd->isAStudent ();
        }

        //region Properties
        /**
         * @return string
         */
        public function getFirstName () {
            return $this->firstName;
        }

        /**
         * @return string
         */
        public function getLastName () {
            return $this->lastName;
        }

        /**
         * @return string
         */
        public function getStudentID () {
            return $this->studentID;
        }

        /**
         * @return string
         */
        public function getEmailAddress () {
            return $this->emailAddress;
        }

        /**
         * @return string
         */
        public function getDaytimePhone () {
            return $this->phoneDaytime;
        }

        /**
         * @return string
         */
        public function getEveningPhone () {
            return $this->phoneEvening;
        }

        /**
         * @return string
         */
        public function getUsername () {
            return $this->username;
        }

        /**
         * @return string
         */
        public function getLoginDomain () {
            return $this->domain;
        }
        //endregion

        //region Private methods
        /**
         * @param            $msg
         * @param null $errorInfo
         */
        private static function logError ( $msg, $errorInfo = null ) {
            $message = "studentData: " . $msg;
            error_log ( $message, 0 );
            if ( $errorInfo ) {
                error_log ( print_r ( $errorInfo, true ), 0 );
            }
        }

        /**
         * @param      $login
         *
         * @internal param null|\studentData $sd
         *
         * @return mixed
        @internal param $string
         */
        private function extractUsername ( $login ) {
            if ( strpos ( $login, '\\' ) ) {
                $credential = explode ( '\\', $login );
                // we're not currently using the domain, but save it in case we want it later
                $this->domain = $credential[ 0 ];

                return $credential[ 1 ];
            }
            else {
                return $login;
            }
        }
        //endregion
    }