<?php 
/* Data API class for authentication and accessing endpoints */

class DataApi {

    const STATUS_FOUND = 'found';
    const STATUS_NOT_FOUND = 'not_found';
    const STATUS_UNAVAILABLE = 'unavailable';

    /**
     * @param string     $status One of STATUS_FOUND, STATUS_NOT_FOUND, STATUS_UNAVAILABLE.
     * @param array|null $data   Payload when status is found.
     * @return array
     */
    private static function build_result( $status, $data = null ) {
        return array(
            'status' => $status,
            'data'   => $data,
        );
    }

    //Sends authentication request to DataAPI using app's client key and client id
    public static function authenticate(){

        $body = array('clientid' => GFEDF_Config::get_data_api_clientid(), 'clientkey' => GFEDF_Config::get_data_api_clientkey());
        $auth_url = GFEDF_Config::get_data_api_url() . GFEDF_Config::get_data_api_stub_auth();
        $resp = wp_remote_post($auth_url, array(
                        'method' => 'POST', 'sslverify' => GFEDF_Config::get_data_api_sslverify(),
                        'body' => $body));

        if ( is_wp_error( $resp ) ) {
            $error_message = $resp->get_error_message();
            error_log( print_r("GF External Data Fields plugin :: error authenticating to Data API - " . $error_message, true) );
            return null;
        } else {
            //var_dump($resp['body']);
            $json = json_decode($resp['body'], true);
            //var_dump($json);
            if ( !empty($json['access_token'])) {
                //get token from response and save
                $token = $json['access_token'];
                update_option(GFEDF_Config::get_data_api_token_option_name(), $token);
                return $token;
            } else {
                error_log( print_r("GF External Data Fields plugin :: error authenticating to Data API.", true) );
                return null;
            }
        } 
    }

    // Get student information from the student data DataAPI endpoint
    public static function get_student($_username) {
        $stu_url = GFEDF_Config::get_data_api_url() . GFEDF_Config::get_data_api_stub_stu();
        $stu_url = sprintf($stu_url, rawurlencode($_username));
        return self::get_url($stu_url);
    }

    // Get employee information from the employee data DataAPI endpoint
    public static function get_employee( $_username ){
        $emp_url = GFEDF_Config::get_data_api_url() . GFEDF_Config::get_data_api_stub_emp();
        $emp_url = sprintf( $emp_url, rawurlencode($_username) );
        return self::get_url( $emp_url );
    }

    // Make call to DataApi endpoint
    public static function get_url( $_url, $isRetry = false ) {

        // get valid authorization header
        $headers = self::create_authorization_header();
        if ( is_null( $headers ) ) {
            error_log( print_r( "GF External Data Fields plugin :: Data API token unavailable; cannot call endpoint.", true ) );
            debug_log( print_r( "GF External Data Fields plugin :: Data API token unavailable; cannot call endpoint.", true ) );
            return self::build_result( self::STATUS_UNAVAILABLE );
        }

        try {
            //make url call
            $resp = wp_remote_get( $_url, array( 'headers' => $headers, 'sslverify' => GFEDF_Config::get_data_api_sslverify() ) );

            if ( is_wp_error( $resp ) ) {

                //if error, log it
                $error_message = $resp->get_error_message();
                error_log( print_r( "GF External Data Fields plugin :: error calling Data API - " . $error_message, true ) );
                debug_log( print_r( "GF External Data Fields plugin :: error calling Data API - " . $error_message, true ) );
                return self::build_result( self::STATUS_UNAVAILABLE );
            }

            $response_code = (int) wp_remote_retrieve_response_code( $resp );

            if ( $response_code == 401 ) {

                if ( $isRetry ) {
                    error_log( print_r( "GF External Data Fields plugin :: Data API still unauthorized after re-authentication.", true ) );
                    debug_log( print_r( "GF External Data Fields plugin :: Data API still unauthorized after re-authentication.", true ) );
                    return self::build_result( self::STATUS_UNAVAILABLE );
                }

                // token is likely expired so reauthenticate and retry data call
                debug_log( print_r("GFEDF :: Authentication token is likely expired. Attempting to reauthenticate.", true) );
                $token = self::authenticate();
                if ( empty( $token ) ) {
                    error_log( print_r( "GF External Data Fields plugin :: re-authentication failed; Data API unavailable.", true ) );
                    debug_log( print_r( "GF External Data Fields plugin :: re-authentication failed; Data API unavailable.", true ) );
                    return self::build_result( self::STATUS_UNAVAILABLE );
                }

                return self::get_url( $_url, true );

            }

            if ( $response_code == 404 ) {
                return self::build_result( self::STATUS_NOT_FOUND );
            }

            // Anything outside 2xx (after 401/404 handling) is treated as unavailable.
            if ( $response_code < 200 || $response_code >= 300 ) {
                error_log( print_r( "GF External Data Fields plugin :: unexpected Data API response code " . $response_code, true ) );
                debug_log( print_r( "GF External Data Fields plugin :: unexpected Data API response code " . $response_code, true ) );
                return self::build_result( self::STATUS_UNAVAILABLE );
            }

            // it's a valid response, so use it
            $body = json_decode( wp_remote_retrieve_body( $resp ), true );
            //var_dump($body);
            if ( ! empty( $body['data'] ) ) {
                return self::build_result( self::STATUS_FOUND, $body['data'] );
            } else {
                return self::build_result( self::STATUS_NOT_FOUND );
            }
        } catch ( Exception $e ){
            error_log( print_r("GF External Data Fields plugin :: error building and saving model - " . $e->getMessage(), true) );
            debug_log( print_r("GF External Data Fields plugin :: error building and saving model - " . $e->getMessage(), true) );
            return self::build_result( self::STATUS_UNAVAILABLE );
        }
    }

    // get token from saved plugin option or authenticate to get valid token
    public static function get_token(){
        $token_val = get_option(GFEDF_Config::get_data_api_token_option_name());
        if ( empty($token_val) ){
            //token not set so authenticate to generate
            $token_val = self::authenticate();
        }
        return $token_val;
    }

    // create authorization bearer/token header to use in request
    public static function create_authorization_header(){
        $token = self::get_token();
        if ( empty( $token ) ) {
            return null;
        }

        $headers = array('Authorization' => 'Bearer ' . $token );

        return $headers;
    }
}
